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
 * @extends AbstractFormField<string>
 *
 * @codeCoverageIgnore
 *
 * @api
 */
abstract class AbstractTextField extends AbstractFormField {

  private ?int $maxLength = NULL;

  private ?int $minLength = NULL;

  private ?string $pattern = NULL;

  public function getDataType(): string {
    return 'string';
  }

  public function getMaxLength(): ?int {
    return $this->maxLength;
  }

  /**
   * @return $this
   */
  public function setMaxLength(?int $maxLength): self {
    $this->maxLength = $maxLength;

    return $this;
  }

  public function getMinLength(): ?int {
    if ($this->isRequired()) {
      return $this->minLength ?? 1;
    }

    return $this->minLength;
  }

  /**
   * @return $this
   */
  public function setMinLength(?int $minLength): self {
    $this->minLength = $minLength;

    return $this;
  }

  public function getPattern(): ?string {
    return $this->pattern;
  }

  /**
   * @return $this
   */
  public function setPattern(?string $pattern): self {
    $this->pattern = $pattern;

    return $this;
  }

}
