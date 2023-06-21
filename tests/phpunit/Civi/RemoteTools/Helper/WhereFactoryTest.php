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

use Civi\RemoteTools\Api4\Query\Comparison;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\RemoteTools\Helper\WhereFactory
 */
final class WhereFactoryTest extends TestCase {

  private WhereFactory $whereFactory;

  protected function setUp(): void {
    parent::setUp();
    $this->whereFactory = new WhereFactory();
  }

  public function testComparison(): void {
    $entityFields = [
      'fieldA' => ['name' => 'fieldA'],
      'fieldB' => ['name' => 'fieldB'],
    ];
    $remoteFields = [
      'fieldA' => ['name' => 'fieldA'],
      'fieldC' => ['name' => 'fieldC'],
    ];

    $where = [
      ['fieldA', '=', 'a'],
      ['fieldB', '=', 'b'],
      ['fieldC', '=', 'c'],
    ];

    $expected = [['fieldA', '=', 'a']];

    static::assertSame($expected, $this->whereFactory->getWhere(
      $where,
      $entityFields,
      $remoteFields,
      fn() => FALSE,
      fn() => NULL,
    ));
  }

  public function testComposite(): void {
    $entityFields = [
      'fieldA' => ['name' => 'fieldA'],
      'fieldB' => ['name' => 'fieldB'],
    ];
    $remoteFields = [
      'fieldA' => ['name' => 'fieldA'],
      'fieldC' => ['name' => 'fieldC'],
    ];

    $where = [
      [
        'OR',
        [
          ['fieldA', '=', 'a'],
          ['fieldB', '=', 'b'],
          ['fieldC', '=', 'c'],
        ],
      ],
    ];

    $expected = [
      ['OR', [['fieldA', '=', 'a']]],
    ];

    static::assertSame($expected, $this->whereFactory->getWhere(
      $where,
      $entityFields,
      $remoteFields,
      fn() => FALSE,
      fn() => NULL,
    ));
  }

  public function testCompositeEmpty(): void {
    $entityFields = [
      'fieldA' => ['name' => 'fieldA'],
      'fieldB' => ['name' => 'fieldB'],
    ];
    $remoteFields = [
      'fieldA' => ['name' => 'fieldA'],
      'fieldC' => ['name' => 'fieldC'],
    ];

    $where = [
      [
        'OR',
        [
          ['fieldB', '=', 'b'],
          ['fieldC', '=', 'c'],
        ],
      ],
    ];

    $expected = [];

    static::assertSame($expected, $this->whereFactory->getWhere(
      $where,
      $entityFields,
      $remoteFields,
      fn() => FALSE,
      fn() => NULL,
    ));
  }

  public function testImplicitJoinInRemote(): void {
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

    $where = [
      ['fieldA', '=', 1],
      ['fieldA.x', '=', 2],
      ['fieldA.x.y', '=', 'y'],
      ['fieldA.x.z', '=', 'z'],
    ];

    $expected = [
      ['fieldA', '=', 1],
      ['fieldA.x', '=', 2],
      ['fieldA.x.y', '=', 'y'],
    ];

    static::assertSame($expected, $this->whereFactory->getWhere(
      $where,
      $entityFields,
      $remoteFields,
      fn() => FALSE,
      fn() => NULL,
    ));
  }

  public function testImplicitJoinNotInRemote(): void {
    $entityFields = [
      'fieldA' => [
        'name' => 'fieldA',
        'fk_entity' => 'Test',
      ],
    ];
    $remoteFields = [
      'fieldA' => ['name' => 'fieldA'],
    ];

    $where = [
      ['fieldA', '=', 1],
      ['fieldA.x', '=', 2],
      ['fieldA.x.y', '=', 'y'],
      ['fieldA.x.z', '=', 'z'],
    ];

    $expected = [
      ['fieldA', '=', 1],
      ['fieldA.x', '=', 2],
      ['fieldA.x.z', '=', 'z'],
    ];

    static::assertSame($expected, $this->whereFactory->getWhere(
      $where,
      $entityFields,
      $remoteFields,
      function(string $fieldName, string $joinedFieldName) {
        static::assertSame('fieldA', $joinedFieldName);

        return in_array($fieldName, ['fieldA.x', 'fieldA.x.z'], TRUE);
      },
      fn() => NULL,
    ));
  }

  public function testWhereRemoteOnlyField(): void {
    $entityFields = [
      'fieldA' => ['name' => 'fieldA'],
    ];
    $remoteFields = [
      'fieldB' => ['name' => 'fieldB'],
    ];

    $where = [
      ['fieldB', '=', 1],
    ];

    $expected = [
      ['fieldC', '!=', 'c'],
    ];

    static::assertSame($expected, $this->whereFactory->getWhere(
      $where,
      $entityFields,
      $remoteFields,
      fn() => FALSE,
      function (Comparison $comparison) {
        static::assertEquals(Comparison::new('fieldB', '=', 1), $comparison);

        return Comparison::new('fieldC', '!=', 'c');
      },
    ));
  }

}
