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

namespace Civi\RemoteTools\Api4\Util;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\RemoteTools\Api4\Util\FieldUtil
 */
final class FieldUtilTest extends TestCase {

  public function testGetJoinedFieldName(): void {
    static::assertSame('foo', FieldUtil::getJoinedFieldName('foo.bar', ['foo' => ['fk_entity' => 'Foo']]));

    // "foo.bar.baz" is the second implicit join, but "foo.bar" is not part of fields.
    static::assertSame('foo', FieldUtil::getJoinedFieldName('foo.bar.baz', ['foo' => ['fk_entity' => 'Foo']]));

    // Happens if "foo" is the name of a custom group and bar a custom field that references an entity.
    static::assertSame('foo.bar', FieldUtil::getJoinedFieldName('foo.bar.baz', ['foo.bar' => ['fk_entity' => 'Bar']]));

    // "foo" does not reference an entity.
    static::assertNull(FieldUtil::getJoinedFieldName('foo.bar', ['foo' => []]));
  }

  public function testIsValidSuffix(): void {
    static::assertFalse(FieldUtil::isValidSuffix('test', ['suffixes' => ['foo']]));
    static::assertFalse(FieldUtil::isValidSuffix('test', []));
    static::assertTrue(FieldUtil::isValidSuffix('test', ['suffixes' => ['foo', 'test']]));
  }

  public function testRemoveLastImplicitJoin(): void {
    static::assertNull(FieldUtil::removeLastImplicitJoin('foo'));
    static::assertSame('foo', FieldUtil::removeLastImplicitJoin('foo.bar'));
    static::assertSame('foo.bar', FieldUtil::removeLastImplicitJoin('foo.bar.baz'));
  }

  public function testSplitOptionListPropertySuffix(): void {
    static::assertSame(['foo', 'label'], FieldUtil::splitOptionListSuffix('foo:label'));
    static::assertSame(['foo', NULL], FieldUtil::splitOptionListSuffix('foo'));
  }

  public function testStripOptionListProperty(): void {
    static::assertSame('foo', FieldUtil::stripOptionListSuffix('foo:label'));
    static::assertSame('foo', FieldUtil::stripOptionListSuffix('foo'));
  }

}
