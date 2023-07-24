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

use Civi\Api4\Generic\Result;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\RemoteTools\Api4\Util\ResultUtil
 */
final class ResultUtilTest extends TestCase {

  public function testCopy(): void {
    $from = new Result(['foo' => 'bar']);
    $to = new Result();
    ResultUtil::copy($from, $to);
    static::assertSame(['foo' => 'bar'], $to->getArrayCopy());
    static::assertSame(1, $to->count());
    static::assertSame(1, $to->countFetched());
    $this->expectException(\CRM_Core_Exception::class);
    $to->countMatched();
  }

  public function testCopyWithCoutMatched(): void {
    $from = new Result(['foo' => 'bar']);
    $from->setCountMatched(2);
    $to = new Result();
    ResultUtil::copy($from, $to);
    static::assertSame(['foo' => 'bar'], $to->getArrayCopy());
    static::assertSame(2, $to->count());
    static::assertSame(1, $to->countFetched());
    static::assertSame(2, $to->countMatched());
  }

}
