<?php
/*
 * Copyright (C) 2026 SYSTOPIA GmbH
 *
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types = 1);

namespace Civi\RemoteTools\Helper;

use Civi\Api4\File;
use Civi\RemoteTools\AbstractRemoteToolsHeadlessTestCase;
use Civi\RemoteTools\Fixture\ContactFixture;

/**
 * @covers \Civi\RemoteTools\Helper\FilePersister
 *
 * @group headless
 */
final class FilePersisterTest extends AbstractRemoteToolsHeadlessTestCase {

  private FilePersister $filePersister;

  protected function setUp(): void {
    parent::setUp();
    // @phpstan-ignore assign.propertyType
    $this->filePersister = \Civi::service(FilePersister::class . '.test');
  }

  public function testPersistFile(): void {
    $contact = ContactFixture::addIndividual();
    $fileId = $this->filePersister->persistFile('test.txt', 'content', 'description', $contact['id']);

    $file = File::get(FALSE)
      ->addSelect('*', 'content')
      ->addWhere('id', '=', $fileId)
      ->execute()
      ->single();

    static::assertMatchesRegularExpression('/^test_.+\.txt$/', $file['uri']);
    static::assertSame('content', $file['content']);
    static::assertSame('description', $file['description']);
    static::assertSame('text/plain', $file['mime_type']);
    static::assertSame($contact['id'], $file['created_id']);
  }

  public function testPersistFileFromForm(): void {
    $contact = ContactFixture::addIndividual();
    $fileId = $this->filePersister->persistFileFromForm(
      ['filename' => 'test.txt', 'content' => base64_encode('content')],
      'description',
      $contact['id']
    );

    $file = File::get(FALSE)
      ->addSelect('*', 'content')
      ->addWhere('id', '=', $fileId)
      ->execute()
      ->single();

    static::assertMatchesRegularExpression('/^test_.+\.txt$/', $file['uri']);
    static::assertSame('content', $file['content']);
    static::assertSame('description', $file['description']);
    static::assertSame('text/plain', $file['mime_type']);
    static::assertSame($contact['id'], $file['created_id']);
  }

}
