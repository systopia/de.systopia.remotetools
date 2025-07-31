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

namespace Civi\RemoteTools\Api4\Util;

/**
 * @phpstan-type whereT list<array{string, string|list<mixed>, 2?: mixed}>
 *   "list<mixed>" is actually a condition of a composite condition so we have
 *   a recursion that cannot be expressed in a phpstan type. The third entry is
 *   not given for composite conditions.
 *
 * @codeCoverageIgnore
 */
final class WhereUtil {

  /**
   * @phpstan-param whereT $where
   *
   * @phpstan-return list<string>
   *   Field names used in where.
   */
  public static function getFields(array $where): array {
    $fields = [];

    foreach ($where as $clause) {
      if (is_array($clause[1])) {
        // Composite condition.
        // @phpstan-ignore argument.type
        $fields = array_merge($fields, self::getFields($clause[1]));
      }
      else {
        $fields[] = $clause[0];
      }
    }

    return array_values(array_unique($fields));
  }

}
