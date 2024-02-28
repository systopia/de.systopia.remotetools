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

use Webmozart\Assert\Assert;

final class FieldUtil {

  /**
   * @phpstan-param array<string, array<string, mixed>> $fields
   *   Entity fields indexed by name.
   *
   * @return string|null
   *   The joined field, or NULL if $fieldName contains no joined field.
   *   Examples:
   *     - foo_id.bar_id.name => foo_id, or NULL if foo_id is not a foreign key.
   *     - custom_group.foo_id.name => custom_group.foo_id, or NULL if foo_id is
   *       not a foreign key.
   */
  public static function getJoinedFieldName(string $fieldName, array $fields): ?string {
    $newFieldName = self::removeLastImplicitJoin($fieldName);
    while ($newFieldName !== NULL) {
      if (isset($fields[$newFieldName]['fk_entity'])) {
        return $newFieldName;
      }
      $newFieldName = self::removeLastImplicitJoin($newFieldName);
    }

    return NULL;
  }

  /**
   * @return bool
   *   TRUE if the given string is the name of a joined field, i.e. contains a
   *   "." or ":".
   */
  public static function isJoinedField(string $fieldName): bool {
    return str_contains($fieldName, '.') || str_contains($fieldName, ':');
  }

  /**
   * @phpstan-param array<string, mixed> $field
   *
   * @return bool TRUE if $suffix is a valid option list suffix.
   */
  public static function isValidSuffix(string $suffix, array $field): bool {
    $suffixes = $field['suffixes'] ?? [];
    Assert::isArray($suffixes);

    return in_array($suffix, $suffixes, TRUE);
  }

  /**
   * @return string|null
   *   The field name without the last implicit join, or NULL if there's no
   *   implicit join.
   */
  public static function removeLastImplicitJoin(string $fieldName): ?string {
    $pos = strrpos($fieldName, '.');
    /** @var string|null $result */
    $result = FALSE === $pos ? NULL : substr($fieldName, 0, $pos);

    return $result;
  }

  /**
   * @return array{string, string|null}
   *   The field name without option list suffix at index 0, the option list
   *   suffix (NULL, if given field name contains no suffix) at index 1.
   */
  public static function splitOptionListSuffix(string $fieldName): array {
    // @phpstan-ignore-next-line
    return explode(':', $fieldName, 2) + [NULL, NULL];
  }

  /**
   * @return string
   *   The field name without option list suffix, i.e. everything from the
   *   first ":" is stripped. If there's no ":" the field name is returned
   *   unchanged.
   */
  public static function stripOptionListSuffix(string $fieldName): string {
    return self::splitOptionListSuffix($fieldName)[0];
  }

}
