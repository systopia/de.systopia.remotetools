<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
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

namespace Civi\RemoteTools\Form\FormSpec\Rule;

/**
 * @phpstan-type fieldNameT non-empty-string
 * @phpstan-type operatorT '='|'!='|'IN'|'NOT IN'|'CONTAINS'|'NOT CONTAINS'
 *   The (NOT) CONTAINS operator can be used for multi option fields, e.g.
 *   checkboxes. The value can be a single value or a list of values. The
 *   selected options need only to contain one of the given values (not all of
 *   them).
 * @phpstan-type valueT scalar|list<scalar|null>|null
 * @phpstan-type effectT 'ENABLE'|'DISABLE'|'SHOW'|'HIDE'
 * @phpstan-type conditionT array{operatorT, valueT}
 * @phpstan-type conditionListT array<fieldNameT, conditionT>
 *
 * @api
 */
final class FormRule {

  /**
   * @phpstan-var conditionListT
   */
  public array $conditions = [];

  /**
   * @phpstan-var effectT
   */
  public string $effect;

  /**
   * @phpstan-param effectT $effect
   * @phpstan-param conditionListT $conditions
   */
  public function __construct(string $effect, array $conditions) {
    $this->effect = $effect;
    $this->conditions = $conditions;
  }

}
