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

namespace Civi\RemoteTools\JsonForms\FormSpec;

use Civi\RemoteTools\Form\FormSpec\Rule\FormRule;
use Civi\RemoteTools\JsonForms\JsonFormsRule;
use Civi\RemoteTools\JsonSchema\JsonSchema;

final class RuleFactory {

  public static function createJsonFormsRule(FormRule $rule): JsonFormsRule {
    $propertyConditions = [];
    foreach ($rule->conditions as $fieldName => $condition) {
      [$operator, $value] = $condition;
      switch ($operator) {
        case '=':
          $propertyConditions[$fieldName] = ['const' => $value];
          break;

        case '!=':
          $propertyConditions[$fieldName] = ['not' => ['const' => $value]];
          break;

        case 'IN':
          $propertyConditions[$fieldName] = ['enum' => $value];
          break;

        case 'NOT IN':
          $propertyConditions[$fieldName] = ['not' => ['enum' => $value]];
          break;

        case 'CONTAINS':
          $propertyConditions[$fieldName] = ['contains' => ['enum' => (array) $value]];
          break;

        case 'NOT CONTAINS':
          $propertyConditions[$fieldName] = ['not' => ['contains' => ['enum' => (array) $value]]];
          break;

        default:
          throw new \InvalidArgumentException("Unknown rule operator '$operator'");
      }
    }

    return new JsonFormsRule(
      $rule->effect, '#', JsonSchema::fromArray(['properties' => $propertyConditions])
    );
  }

}
