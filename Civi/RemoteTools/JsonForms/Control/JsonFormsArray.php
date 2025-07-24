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

class JsonFormsArray extends JsonFormsControl {

  /**
   * @phpstan-param array<int, JsonFormsControl>|null $elements
   *   The elements of each array entry to display. If NULL, all elements are
   *   shown based on the JSON schema.
   * @phpstan-param array{
   *   addButtonLabel?: string,
   *   removeButtonLabel?: string,
   *   itemLayout?: string,
   * }|null $options
   *   "itemLayout" is the layout type to display the controls for the item
   *   properties, e.g. HorizontalLayout.
   *
   * Usage of $elements and the option "itemLayout" is supported by the Drupal
   * module JSON Forms since version 0.6.
   */
  public function __construct(
    string $scope,
    ?string $label,
    ?string $description = NULL,
    ?array $elements = NULL,
    ?array $options = NULL,
    array $keywords = []
  ) {
    if (NULL !== $elements) {
      $options ??= [];
      $options['elements'] = $elements;
    }

    parent::__construct($scope, $label, $description, $options, $keywords);
  }

  /**
   * @return list<JsonFormsControl>|null
   */
  public function getElements(): ?array {
    /** @var list<JsonFormsControl>|null */
    return $this->keywords['options']->keywords['elements'] ?? NULL;
  }

}
