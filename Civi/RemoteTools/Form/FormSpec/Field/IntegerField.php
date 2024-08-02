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

/**
 * @extends AbstractNumberField<int>
 *
 * @codeCoverageIgnore
 *
 * @api
 */
final class IntegerField extends AbstractNumberField {

  private ?int $maximum = NULL;

  private ?int $minimum = NULL;

  public function getDataType(): string {
    return 'integer';
  }

  public function getInputType(): string {
    return 'integer';
  }

  public function getMaximum(): ?int {
    return $this->maximum;
  }

  public function setMaximum(?int $maximum): self {
    $this->maximum = $maximum;

    return $this;
  }

  public function getMinimum(): ?int {
    return $this->minimum;
  }

  public function setMinimum(?int $minimum): self {
    $this->minimum = $minimum;

    return $this;
  }

}
