<?php
/*
 * Copyright (C) 2024 SYSTOPIA GmbH
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

namespace Civi\RemoteTools\Form\FormSpec;

/**
 * @extends AbstractFormElementContainer<FormElementInterface>
 *
 * @phpstan-type fieldNameT string
 * @phpstan-type fieldValueT scalar|null
 * @phpstan-type operatorT '='|'!='|'>'|'>='|'<'|'<='|'=~'|'IN'|'NOT IN'
 *   "=~" can be used for regex comparison. (Patterns must not be enclosed by a
 *   character.)
 * @phpstan-type expressionT string
 *   Expression in Symfony Expression Language syntax. Form fields can be
 *   referenced in this form: @{field}
 * @phpstan-type fieldNameValuePairsT non-empty-array<fieldNameT, fieldValueT|list<fieldValueT>>
 *   Field must equal the given values or one of the given values.
 * @phpstan-type conditionListT non-empty-list<array{fieldNameT, operatorT, fieldValueT|list<fieldValueT>}>
 *   List of conditions with field name, operator, and value. For the operators
 *   "IN" and "NOT IN" a list of values has to be given.
 * @phpstan-type limitValidationT null|bool|fieldNameValuePairsT|conditionListT|expressionT
 *   - null (default): Limited validation is disabled when used in FormSpec.
 *     Configuration of FormSpec is applied when used in form input.
 *   - false: Limited validation is disabled. Can be used in form input when
 *     limited validation is configured in FormSpec to enforce normal validation
 *     for this input.
 *   - true: Limited validation is enabled. (Probably not necessary.)
 *   - fieldNameValuePairsT: Limited validation is used if all given fields
 *     match.
 *   - conditionListT: Limited validation is used if all conditions are matched.
 *   - expressionT: Limited validation is used if expression is matched.
 *
 * @api
 */
final class FormSpec extends AbstractFormElementContainer {

  /**
   * @phpstan-var list<DataTransformerInterface>
   */
  private array $dataTransformers = [];

  /**
   * @phpstan-var limitValidationT
   */
  private null|bool|string|array $limitValidation = NULL;

  /**
   * @phpstan-var list<ValidatorInterface>
   */
  private array $validators = [];

  public function __construct(string $title, array $elements = []) {
    parent::__construct($title, $elements);
    $this->dataTransformers[] = new FormSpecDataTransformer($this);
    $this->validators[] = new FormSpecValidator($this);
  }

  public function appendDataTransformer(DataTransformerInterface $dataTransformer): self {
    $this->dataTransformers[] = $dataTransformer;

    return $this;
  }

  public function getDataTransformer(): DataTransformerInterface {
    return new DataTransformerChain($this->dataTransformers);
  }

  public function setDataTransformer(DataTransformerInterface $dataTransformer): self {
    $this->dataTransformers = [
      new FormSpecDataTransformer($this),
      $dataTransformer,
    ];

    return $this;
  }

  /**
   * @phpstan-return limitValidationT
   *   Condition for usage of limited validation. Limited validation can be used
   *   to persist forms in an incomplete state. See definition of
   *   "limitValidationT" for possible values.
   */
  public function getLimitValidation(): null|bool|string|array {
    return $this->limitValidation;
  }

  /**
   * @phpstan-param limitValidationT $limitValidation
   *   Condition for usage of limited validation. Limited validation can be used
   *   to persist forms in an incomplete state. With limited validation some
   *   validations are not performed, but it is for example ensured that the
   *   data type matches if a value is given, and that strings don't exceed a
   *   possible maximum length. See definition of "limitValidationT" for
   *   possible values.
   *
   *   Example: ['_action' => 'save']
   *   If "_action" is the name of the submit buttons and the submit button
   *   with the value "save" is pressed then limited validation is performed.
   */
  public function setLimitValidation(null|bool|string|array $limitValidation): self {
    $this->limitValidation = $limitValidation;

    return $this;
  }

  public function appendValidator(ValidatorInterface $validator): self {
    $this->validators[] = $validator;

    return $this;
  }

  /**
   * @phpstan-return list<ValidatorInterface>
   */
  public function getValidators(): array {
    return $this->validators;
  }

  public function prependValidator(ValidatorInterface $validator): self {
    array_unshift($this->validators, $validator);

    return $this;
  }

}
