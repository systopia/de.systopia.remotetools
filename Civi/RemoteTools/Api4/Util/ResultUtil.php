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

final class ResultUtil {

  public static function copy(Result $from, Result $to): void {
    $to->exchangeArray($from->getArrayCopy());
    try {
      $to->setCountMatched($from->countMatched());
    }
    // @phpstan-ignore-next-line
    catch (\CRM_Core_Exception $e) {
      // @ignoreException: happens if no matchedCount was set.
    }
    $to->debug = $from->debug;
  }

}
