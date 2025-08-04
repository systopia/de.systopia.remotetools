<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
 *
 *  This program is free software: you can redistribute it and/or modify it under
 *  the terms of the GNU Affero General Public License as published by the Free
 *  Software Foundation, either version 3 of the License, or (at your option) any
 *  later version.
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

namespace Civi\RemoteTools\Form\FormSpec\Field;

use Civi\RemoteTools\Form\FormSpec\AbstractFormField;

/**
 * This field type can be used for fields whose values are calculated using the
 * values of other fields.
 *
 * @phpstan-type dataTypeT 'boolean'|'integer'|'number'|'string'
 *
 * @extends AbstractFormField<scalar>
 *
 * @codeCoverageIgnore
 *
 * @api
 */
final class CalculateField extends AbstractFormField {

  /**
   * @phpstan-var dataTypeT
   */
  private string $dataType;

  private string $expression;

  /**
   * Expressions have to be compliant with the syntax of the Symfony
   * ExpressionLanguage component and might use functionality from
   * systopia/expression-language-ext. They may reference field values with the
   * field name in curly brackets, e.g. {my_field}. If used in an FieldListField
   * only fields of the current field item can be referenced.
   *
   * If the result of the expression isn't a number the data type should be set
   * accordingly (e.g. "string" or "boolean").
   *
   * @phpstan-param dataTypeT $dataType
   *   The data type of the expression result.
   *
   * @see https://github.com/systopia/expression-language-ext
   */
  public function __construct(string $name, string $label, string $expression, string $dataType = 'number') {
    parent::__construct($name, $label);
    $this->expression = $expression;
    $this->dataType = $dataType;
  }

  public function getInputType(): string {
    return 'calculate';
  }

  /**
   * @phpstan-return dataTypeT
   */
  public function getDataType(): string {
    return $this->dataType;
  }

  /**
   * @phpstan-param dataTypeT $dataType
   */
  public function setDataType(string $dataType): void {
    $this->dataType = $dataType;
  }

  public function getExpression(): string {
    return $this->expression;
  }

  public function setExpression(string $expression): void {
    $this->expression = $expression;
  }

}
