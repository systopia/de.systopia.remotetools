<?php
/*
 * Copyright (C) 2022 SYSTOPIA GmbH
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

use Civi\RemoteTools\JsonSchema\JsonSchema;

final class JsonSchemaUtil {

  /**
   * @phpstan-param array<int|string, scalar> $titles
   *   Allowed values mapped to titles.
   *
   * @phpstan-return array<JsonSchema> To be used as value of "oneOf" keyword.
   */
  public static function buildTitledOneOf(array $titles): array {
    $oneOf = [];
    foreach ($titles as $value => $title) {
      $oneOf[] = JsonSchema::fromArray(['const' => $value, 'title' => $title]);
    }

    return $oneOf;
  }

}
