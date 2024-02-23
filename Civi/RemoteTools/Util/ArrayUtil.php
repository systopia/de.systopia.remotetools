<?php
/*
 * Copyright (C) 2024 SYSTOPIA GmbH
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

namespace Civi\RemoteTools\Util;

final class ArrayUtil {

  /**
   * @phpstan-param array<int|string, mixed> $array
   *
   * @return bool
   *   TRUE if the array keys are strictly increasing starting at 0, i.e. in
   *   JSON it is encoded as array, not as object.
   *
   * @phpstan-assert-if-true list<mixed> $array
   */
  public static function isJsonArray(array $array): bool {
    $expectedKey = 0;
    foreach (array_keys($array) as $key) {
      if ($key !== $expectedKey) {
        return FALSE;
      }

      ++$expectedKey;
    }

    return TRUE;
  }

}
