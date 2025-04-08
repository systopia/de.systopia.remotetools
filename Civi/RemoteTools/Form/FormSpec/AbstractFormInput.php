<?php
/*
 * Copyright (C) 2023 SYSTOPIA GmbH
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
 * @phpstan-import-type limitValidationT from FormSpec
 *
 * @api
 */
abstract class AbstractFormInput implements FormElementInterface {

  private string $name;

  private string $label;

  private string $description = '';

  /**
   * @phpstan-var limitValidationT
   */
  private $limitValidation = NULL;

  public function __construct(string $name, string $label) {
    $this->name = $name;
    $this->label = $label;
  }

  public function getType(): string {
    return 'input';
  }

  public function getName(): string {
    return $this->name;
  }

  /**
   * @return $this
   */
  public function setName(string $name): self {
    $this->name = $name;

    return $this;
  }

  public function getLabel(): string {
    return $this->label;
  }

  /**
   * @return $this
   */
  public function setLabel(string $label): self {
    $this->label = $label;

    return $this;
  }

  public function getDescription(): string {
    return $this->description;
  }

  /**
   * @return $this
   */
  public function setDescription(string $description): self {
    $this->description = $description;

    return $this;
  }

  /**
   * @phpstan-return limitValidationT
   *   Condition for usage of limited validation. Limited validation can be used
   *   to persist forms in an incomplete state. See definition of
   *   "limitValidationT" for possible values.
   */
  public function getLimitValidation() {
    return $this->limitValidation;
  }

  /**
   * @phpstan-param limitValidationT $limitValidation
   *   Condition for usage of limited validation. Limited validation can be used
   *   to persist forms in an incomplete state. With limited validation some
   *   validations are not performed, but it is for example ensured that the
   *   data type matches if a value is given, and that strings don't exceed a
   *   possible maximum length. See definition of "limitValidationT" for
   *   possible values. Might be set to false to enforce normal validation for
   *   this input if limited validation is configured in FormSpec.
   */
  public function setLimitValidation($limitValidation): self {
    $this->limitValidation = $limitValidation;

    return $this;
  }

  abstract public function getDataType(): string;

  abstract public function getInputType(): string;

}
