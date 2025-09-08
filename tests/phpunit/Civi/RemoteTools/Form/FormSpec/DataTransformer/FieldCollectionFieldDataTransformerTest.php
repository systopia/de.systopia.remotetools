<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
 *
 *  This program is free software: you can redistribute it and/or modify it under
 *  the terms of the GNU Affero General Public License as published by the Free
 *  Software Foundation, either version 3 of the License, or (at your option) any
 *  later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types = 1);

namespace Civi\RemoteTools\Form\FormSpec\DataTransformer;

use Civi\RemoteTools\Form\FormSpec\AbstractFormField;
use Civi\RemoteTools\Form\FormSpec\Field\FieldCollectionField;
use Civi\RemoteTools\Form\FormSpec\Field\TextField;
use Civi\RemoteTools\Form\FormSpec\FieldDataTransformerInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\RemoteTools\Form\FormSpec\DataTransformer\FieldCollectionFieldDataTransformer
 */
final class FieldCollectionFieldDataTransformerTest extends TestCase {

  public function test(): void {
    $textField = (new TextField('text', ''))->setDataTransformer(
      new class implements FieldDataTransformerInterface {

        public function toEntityValue(
          mixed $data,
          AbstractFormField $field,
          ?array $defaultValuesInList = NULL
        ): mixed {
          FieldCollectionFieldDataTransformerTest::assertNull($defaultValuesInList);
          /** @var string $data */
          return $data . 'Test';
        }

      }
    );

    $field = new FieldCollectionField('collection', 'Collection', [$textField]);
    static::assertSame(
      ['text' => 'fooTest'],
      $field->getDataTransformer()->toEntityValue(['text' => 'foo'], $field, NULL)
    );

    static::assertSame(['foo' => 'bar'], $field->getDataTransformer()->toEntityValue(['foo' => 'bar'], $field, NULL));
    static::assertNull($field->getDataTransformer()->toEntityValue(NULL, $field, NULL));

  }

  public function testWithDefaultValuesInList(): void {
    $textField = (new TextField('text', ''))->setDataTransformer(
      new class implements FieldDataTransformerInterface {

        public function toEntityValue(
          mixed $data,
          AbstractFormField $field,
          ?array $defaultValuesInList = NULL
        ): mixed {
          FieldCollectionFieldDataTransformerTest::assertSame($defaultValuesInList, ['foo', 'bar']);
          /** @var string $data */
          return $data . 'Test';
        }

      }
    );

    $field = new FieldCollectionField('collection', 'Collection', [$textField]);
    $defaultValuesInList = [
      ['text' => 'foo'],
      ['text' => 'bar'],
    ];
    static::assertSame(
      ['text' => 'bazTest'],
      $field->getDataTransformer()->toEntityValue(['text' => 'baz'], $field, $defaultValuesInList)
    );

  }

}
