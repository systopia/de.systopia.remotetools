<?php
/*
 * Copyright (C) 2023 SYSTOPIA GmbH
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as published by
 *  the Free Software Foundation in version 3.
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

namespace Civi\RemoteTools\EntityProfile;

use Civi\RemoteTools\EntityProfile\Authorization\GrantResult;
use Civi\RemoteTools\PHPUnit\Traits\ArrayAssertTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\RemoteTools\EntityProfile\EntityProfilePermissionDecorator
 */
final class EntityProfilePermissionDecoratorTest extends TestCase {
  use ArrayAssertTrait;

  /**
   * @var \Civi\RemoteTools\EntityProfile\EntityProfilePermissionDecorator
   */
  private EntityProfilePermissionDecorator $profileDecorator;

  /**
   * @var \Civi\RemoteTools\EntityProfile\RemoteEntityProfileInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $profileMock;

  protected function setUp(): void {
    parent::setUp();
    $this->profileMock = $this->createMock(RemoteEntityProfileInterface::class);
    $this->profileDecorator = new EntityProfilePermissionDecorator($this->profileMock);
  }

  public function testConvertToRemoteValues(): void {
    $entityValues = ['name' => 'test'];
    $select = [];
    $contactId = 12;

    $this->profileMock->method('convertToRemoteValues')
      ->with($entityValues, $select, 12)
      ->willReturn(['remoteName' => 'x']);

    $this->profileMock->method('isDeleteGranted')
      ->with($entityValues, $contactId)
      ->willReturn(GrantResult::newDenied());
    $this->profileMock->method('isUpdateGranted')
      ->with($entityValues, $contactId)
      ->willReturn(GrantResult::newPermitted());

    $expectedValues = [
      'remoteName' => 'x',
      'CAN_delete' => FALSE,
      'CAN_update' => TRUE,
    ];
    static::assertEquals(
      $expectedValues,
      $this->profileDecorator->convertToRemoteValues(['name' => 'test'], $select, 12),
    );
  }

  public function testConvertToRemoteValuesNotSelected(): void {
    $entityValues = ['name' => 'test'];
    $select = ['remoteName'];
    $contactId = 12;

    $this->profileMock->method('convertToRemoteValues')
      ->with($entityValues, $select, 12)
      ->willReturn(['remoteName' => 'x']);

    $expectedValues = ['remoteName' => 'x'];
    static::assertEquals(
      $expectedValues,
      $this->profileDecorator->convertToRemoteValues(['name' => 'test'], $select, 12),
    );
  }

  public function testGetRemoteFields(): void {
    $entityFields = ['field' => ['name' => 'field']];
    $contactId = 12;

    $this->profileMock->method('getRemoteFields')
      ->with($entityFields, $contactId)
      ->willReturn(['remoteField' => ['name' => 'remoteField']]);

    $expectedFieldNames = ['remoteField', 'CAN_delete', 'CAN_update'];
    static::assertArrayHasSameKeys(
      $expectedFieldNames,
      $this->profileDecorator->getRemoteFields($entityFields, $contactId),
    );
  }

}
