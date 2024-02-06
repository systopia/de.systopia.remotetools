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
   * The 'oneOf' keyword must not be empty.
   *
   * @phpstan-param non-empty-array<int|string, string> $titles
   *   Allowed values mapped to titles.
   *
   * @phpstan-return non-empty-array<JsonSchema> To be used as value of "oneOf" keyword.
   */
  public static function buildTitledOneOf(array $titles): array {
    $oneOf = [];
    foreach ($titles as $value => $title) {
      $oneOf[] = JsonSchema::fromArray(['const' => $value, 'title' => $title]);
    }

    return $oneOf;
  }

  /**
   * @phpstan-param array<int|string> $path
   */
  public static function getPropertySchemaAt(JsonSchema $jsonSchema, array $path): ?JsonSchema {
    foreach ($path as $pathElement) {
      if (is_int($pathElement)
        || ('array' === $jsonSchema->getKeywordValueOrDefault('type', NULL)
          && 1 === preg_match('/^[1-9]*[0-9]$/', $pathElement))
      ) {
        $jsonSchema = $jsonSchema->getKeywordValueOrDefault('items', NULL);
      }
      else {
        $properties = $jsonSchema->getKeywordValueOrDefault('properties', NULL);
        if (!$properties instanceof JsonSchema) {
          return NULL;
        }

        $jsonSchema = $properties->getKeywordValueOrDefault($pathElement, NULL);
      }

      if (!$jsonSchema instanceof JsonSchema) {
        return NULL;
      }
    }

    return $jsonSchema;
  }

}
