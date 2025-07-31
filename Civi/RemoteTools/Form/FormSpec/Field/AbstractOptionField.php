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

namespace Civi\RemoteTools\Form\FormSpec\Field;

use Civi\RemoteTools\Form\FormSpec\AbstractFormField;

/**
 * @template T of int|string
 *
 * @extends AbstractFormField<T>
 *
 * @codeCoverageIgnore
 *
 * @api
 */
abstract class AbstractOptionField extends AbstractFormField {

  /**
   * @phpstan-var array<T, string>
   *   Maps values to labels.
   */
  private array $options;

  /**
   * @param array<T, string> $options
   *   Maps values to labels.
   */
  public function __construct(string $name, string $label, array $options) {
    parent::__construct($name, $label);
    $this->options = $options;
  }

  /**
   * @phpstan-param T $value
   */
  public function addOption(int|string $value, string $label): static {
    $this->options[$value] = $label;

    return $this;
  }

  /**
   * @phpstan-return array<T, string>
   *   Maps values to labels.
   */
  public function getOptions(): array {
    return $this->options;
  }

  /**
   * @param array<T, string> $options
   *   Maps values to labels.
   *
   * @return $this
   */
  public function setOptions(array $options): static {
    $this->options = $options;

    return $this;
  }

}
