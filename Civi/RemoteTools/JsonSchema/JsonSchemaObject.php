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

namespace Civi\RemoteTools\JsonSchema;

class JsonSchemaObject extends JsonSchema {

  /**
   * @phpstan-param array<int|string, JsonSchema> $properties
   *   Integers are allowed as keys because PHP automatically converts
   *   integerish string to integers when used as key. The keys must not be
   *   strictly increasing starting at 0.
   */
  public function __construct(array $properties, array $keywords = [], bool $nullable = FALSE) {
    $type = $nullable ? ['object', 'null'] : 'object';

    $keywords['type'] = $type;
    if ([] !== $properties) {
      $keywords['properties'] = new JsonSchema($properties);
    }

    parent::__construct($keywords);
  }

}
