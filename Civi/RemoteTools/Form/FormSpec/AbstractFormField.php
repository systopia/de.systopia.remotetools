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
 * @template T of scalar|array<int|string, mixed> JSON serializable.
 *
 * @codeCoverageIgnore
 *
 * @api
 */
abstract class AbstractFormField extends AbstractFormInput {

  private bool $hidden = FALSE;

  private bool $required = FALSE;

  private bool $readOnly = FALSE;

  private ?bool $nullable = NULL;

  private bool $hasDefaultValue = FALSE;

  /**
   * @phpstan-var T|null
   */
  private mixed $defaultValue = NULL;

  public function getType(): string {
    return 'field';
  }

  public function isHidden(): bool {
    return $this->hidden;
  }

  public function setHidden(bool $hidden): static {
    $this->hidden = $hidden;

    return $this;
  }

  public function isRequired(): bool {
    return $this->required;
  }

  /**
   * @return $this
   */
  public function setRequired(bool $required): static {
    $this->required = $required;

    return $this;
  }

  public function isReadOnly(): bool {
    return $this->readOnly;
  }

  /**
   * @return $this
   */
  public function setReadOnly(bool $readOnly): static {
    $this->readOnly = $readOnly;

    return $this;
  }

  public function isNullable(): bool {
    if ($this->isReadOnly() && $this->hasDefaultValue()) {
      return $this->getDefaultValue() === NULL && FALSE !== $this->nullable;
    }

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
  public function setNullable(?bool $nullable): static {
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
  public function setHasDefaultValue(bool $hasDefaultValue): static {
    $this->hasDefaultValue = $hasDefaultValue;

    return $this;
  }

  /**
   * @phpstan-return T|null
   */
  public function getDefaultValue(): mixed {
    return $this->defaultValue;
  }

  /**
   * Additionally sets "has default value" to TRUE.
   *
   * @phpstan-param T|null $defaultValue
   *
   * @return $this
   *
   * @see hasDefaultValue()
   */
  public function setDefaultValue(mixed $defaultValue): static {
    $this->hasDefaultValue = TRUE;
    $this->defaultValue = $defaultValue;

    return $this;
  }

  public function getDataTransformer(): FieldDataTransformerInterface {
    return IdentityFieldDataTransformer::getInstance();
  }

  public function getValidator(): FieldValidatorInterface {
    return NullFieldValidator::getInstance();
  }

}
