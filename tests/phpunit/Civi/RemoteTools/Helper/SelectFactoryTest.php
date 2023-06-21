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

namespace Civi\RemoteTools\Helper;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\RemoteTools\Helper\SelectFactory
 */
final class SelectFactoryTest extends TestCase {

  private SelectFactory $selectFactory;

  protected function setUp(): void {
    parent::setUp();
    $this->selectFactory = new SelectFactory();
  }

  public function testGetSelectsComparison(): void {
    $entityFields = [
      'fieldA' => ['name' => 'fieldA'],
      'fieldB' => ['name' => 'fieldB'],
    ];
    $remoteFields = [
      'fieldA' => ['name' => 'fieldA'],
      'fieldC' => ['name' => 'fieldC'],
    ];

    $select = ['fieldA', 'fieldB', 'fieldC'];

    $expected = [
      'entity' => ['fieldA'],
      'remote' => ['fieldA', 'fieldC'],
    ];

    static::assertSame($expected, $this->selectFactory->getSelects(
      $select,
      $entityFields,
      $remoteFields,
      fn() => FALSE,
    ));
  }

  public function testGetSelectsImplicitJoinInRemote(): void {
    $entityFields = [
      'fieldA' => [
        'name' => 'fieldA',
        'fk_entity' => 'Test',
      ],
    ];
    $remoteFields = [
      'fieldA' => ['name' => 'fieldA'],
      'fieldA.x' => ['name' => 'fieldA.x'],
      'fieldA.x.y' => ['name' => 'fieldA.x.y'],
    ];

    $select = ['fieldA', 'fieldA.x', 'fieldA.x.y', 'fieldA.x.z'];

    $expected = [
      'entity' => ['fieldA', 'fieldA.x', 'fieldA.x.y'],
      'remote' => ['fieldA', 'fieldA.x', 'fieldA.x.y'],
    ];

    static::assertSame($expected, $this->selectFactory->getSelects(
      $select,
      $entityFields,
      $remoteFields,
      fn() => FALSE,
    ));
  }

  public function testGetSelectsImplicitJoinNotInRemote(): void {
    $entityFields = [
      'fieldA' => [
        'name' => 'fieldA',
        'fk_entity' => 'Test',
      ],
    ];
    $remoteFields = [
      'fieldA' => ['name' => 'fieldA'],
    ];

    $select = ['fieldA', 'fieldA.x', 'fieldA.x.y', 'fieldA.x.z'];

    $expected = [
      'entity' => ['fieldA', 'fieldA.x', 'fieldA.x.z'],
      'remote' => ['fieldA', 'fieldA.x', 'fieldA.x.z'],
    ];

    static::assertSame($expected, $this->selectFactory->getSelects(
      $select,
      $entityFields,
      $remoteFields,
      function(string $fieldName, string $joinedFieldName) {
        static::assertSame('fieldA', $joinedFieldName);

        return in_array($fieldName, ['fieldA.x', 'fieldA.x.z'], TRUE);
      },
    ));
  }

}
