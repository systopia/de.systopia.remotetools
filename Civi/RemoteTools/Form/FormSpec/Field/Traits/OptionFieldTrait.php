<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
 *
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types = 1);

namespace Civi\RemoteTools\Form\FormSpec\Field\Traits;

/**
 * @template T of int|string
 */
trait OptionFieldTrait {

  /**
   * @phpstan-var array<T, string>
   *   Maps values to labels.
   */
  protected array $options;

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
   * Reduces the options to those which values are in the given list.
   *
   * @param list<T> $optionValues
   */
  public function filterOptions(array $optionValues): static {
    $this->options = array_filter(
      $this->options,
      fn ($key) => in_array($key, $optionValues, TRUE),
      ARRAY_FILTER_USE_KEY
    );

    return $this;
  }

  /**
   * @phpstan-param T $value
   */
  public function removeOption(int|string $value): static {
    unset($this->options[$value]);

    return $this;
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
