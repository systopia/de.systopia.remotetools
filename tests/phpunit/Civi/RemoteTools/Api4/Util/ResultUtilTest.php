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
    static::assertCount(1, $to);
    static::assertSame(1, $to->countFetched());
    $this->expectException(\CRM_Core_Exception::class);
    $to->countMatched();
  }

  public function testCopyWithCountMatched(): void {
    $from = new Result(['foo' => 'bar']);
    $from->setCountMatched(2);
    $to = new Result();
    ResultUtil::copy($from, $to);
    static::assertSame(['foo' => 'bar'], $to->getArrayCopy());
    // Before CiviCRM 6 count was the same as countMatched(). Now it's the same as countFetched().
    // https://github.com/civicrm/civicrm-core/commit/c5ead539ad5271db8a7a583efd3eea03ac04204b#diff-9facf00b48a5316db8c1e7e67a6a68053248d342d4e74099cf971f1ba899e7feL186
    static::assertCount(count($from), $to);
    static::assertSame(1, $to->countFetched());
    static::assertSame(2, $to->countMatched());
  }

}
