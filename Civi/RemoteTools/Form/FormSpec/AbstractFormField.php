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
 * @template T of scalar
 *
 * @codeCoverageIgnore
 *
 * @api
 */
abstract class AbstractFormField extends AbstractFormInput {

  private bool $required = FALSE;

  private bool $readOnly = FALSE;

  private ?bool $nullable = NULL;

  private bool $hasDefaultValue = FALSE;

  /**
   * @var mixed
   * @phpstan-var T|null
   */
  private $defaultValue = NULL;

  public function getType(): string {
    return 'field';
  }

  public function isRequired(): bool {
    return $this->required;
  }

  /**
   * @return $this
   */
  public function setRequired(bool $required): self {
    $this->required = $required;

    return $this;
  }

  public function isReadOnly(): bool {
    return $this->readOnly;
  }

  /**
   * @return $this
   */
  public function setReadOnly(bool $readOnly): self {
    $this->readOnly = $readOnly;

    return $this;
  }

  public function isNullable(): bool {
    return NULL === $this->nullable ? !$this->isRequired() : $this->nullable;
  }

  /**
   * @param bool|null $nullable
   *   If NULL, isNullable() will return TRUE, if the field is not required.
   *
   * @return $this
   *
   * @see isRequired()
   */
  public function setNullable(?bool $nullable): self {
    $this->nullable = $nullable;

    return $this;
  }

  /**
   * @return bool
   *   TRUE if a default value is set which might be NULL.
   */
  public function hasDefaultValue(): bool {
    return $this->hasDefaultValue;
  }

  /**
   * @return $this
   */
  public function setHasDefaultValue(bool $hasDefaultValue): self {
    $this->hasDefaultValue = $hasDefaultValue;

    return $this;
  }

  /**
   * @return mixed
   * @phpstan-return T|null
   */
  public function getDefaultValue() {
    return $this->defaultValue;
  }

  /**
   * Additionally sets "has default value" to TRUE.
   *
   * @param mixed $defaultValue
   * @phpstan-param T|null $defaultValue
   *
   * @return $this
   *
   * @see hasDefaultValue()
   */
  public function setDefaultValue($defaultValue): self {
    $this->hasDefaultValue = TRUE;
    $this->defaultValue = $defaultValue;

    return $this;
  }

}
