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

namespace Civi\RemoteTools\JsonForms\Control;

use Civi\RemoteTools\JsonForms\JsonFormsControl;
use Civi\RemoteTools\JsonSchema\JsonSchema;

class JsonFormsArray extends JsonFormsControl {

  /**
   * @param string $scope
   * @param string $label
   * @param string|null $description
   * @param array<int, JsonFormsControl>|null $elements
   */
  public function __construct(string $scope, string $label, ?string $description = NULL, ?array $elements = NULL) {
    if (NULL !== $elements) {
      $options = [
        'detail' => [
          'elements' => $elements,
        ],
      ];
    }
    else {
      $options = NULL;
    }

    parent::__construct($scope, $label, $description, NULL, NULL, $options);
  }

  /**
   * @return array<int, JsonFormsControl>|null
   */
  public function getElements(): ?array {
    $options = $this->keywords['options'] ?? NULL;
    if (!$options instanceof JsonSchema) {
      return NULL;
    }
    $detail = $options->keywords['detail'] ?? NULL;
    if (!$detail instanceof JsonSchema) {
      return NULL;
    }

    /** @var array<int, JsonFormsControl>|null $elements */
    $elements = $detail->keywords['elements'] ?? NULL;

    return $elements;
  }

}
