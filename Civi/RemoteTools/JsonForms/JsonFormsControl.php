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

namespace Civi\RemoteTools\JsonForms;

use Civi\RemoteTools\JsonSchema\JsonSchema;

class JsonFormsControl extends JsonFormsElement {

  /**
   * @phpstan-param array<string, mixed>|null $options
   * @phpstan-param array{
   *  prefix?: string,
   *  suffix?: string,
   *  rule?: JsonFormsRule|JsonSchema,
   * } $keywords
   */
  public function __construct(
    string $scope,
    string $label,
    ?string $description = NULL,
    ?array $options = NULL,
    array $keywords = []
  ) {
    $keywords += [
      'scope' => $scope,
      'label' => $label,
    ];
    if (NULL !== $description) {
      $keywords['description'] = $description;
    }
    if (NULL !== $options) {
      $keywords['options'] = JsonSchema::fromArray($options);
    }

    parent::__construct('Control', $keywords);
  }

  public function isReadonly(): ?bool {
    /** @var bool|null */
    return $this->keywords['options']->keywords['readonly'] ?? NULL;
  }

  public function setReadonly(bool $readonly): self {
    /** @var \Civi\RemoteTools\JsonSchema\JsonSchema $options */
    $options = $this->keywords['options'] ??= new JsonSchema([]);
    $options->addKeyword('readonly', $readonly);

    return $this;
  }

  public function getScope(): string {
    /** @var string $scope */
    $scope = $this->keywords['scope'];

    return $scope;
  }

}
