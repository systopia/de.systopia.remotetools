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

namespace Civi\RemoteTools\JsonSchema\Util;

use Civi\RemoteTools\JsonSchema\JsonSchemaArray;
use Civi\RemoteTools\JsonSchema\JsonSchemaNumber;
use Civi\RemoteTools\JsonSchema\JsonSchemaObject;
use Civi\RemoteTools\JsonSchema\JsonSchemaString;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\RemoteTools\JsonSchema\Util\JsonSchemaUtil
 */
final class JsonSchemaUtilTest extends TestCase {

  public function testGetPropertySchemaAt(): void {
    $barSchema = new JsonSchemaNumber();
    $bazItemsSchema = new JsonSchemaString();

    $jsonSchema = new JsonSchemaObject([
      'foo' => new JsonSchemaObject(['bar' => $barSchema]),
      'baz' => new JsonSchemaArray($bazItemsSchema),
    ]);

    static::assertSame($barSchema, JsonSchemaUtil::getPropertySchemaAt($jsonSchema, ['foo', 'bar']));

    static::assertSame($bazItemsSchema, JsonSchemaUtil::getPropertySchemaAt($jsonSchema, ['baz', 2]));
    static::assertSame($bazItemsSchema, JsonSchemaUtil::getPropertySchemaAt($jsonSchema, ['baz', '3']));

    static::assertSame($jsonSchema, JsonSchemaUtil::getPropertySchemaAt($jsonSchema, []));

    static::assertNull(JsonSchemaUtil::getPropertySchemaAt($jsonSchema, ['foo', 'baz']));
  }

}
