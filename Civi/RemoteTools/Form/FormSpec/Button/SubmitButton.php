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

namespace Civi\RemoteTools\Form\FormSpec\Button;

use Civi\RemoteTools\Form\FormSpec\AbstractFormInput;

final class SubmitButton extends AbstractFormInput {

  private string $value;

  private ?string $confirmMessage;

  public function __construct(string $name, string $value, string $label, ?string $confirmMessage = NULL) {
    parent::__construct($name, $label);
    $this->value = $value;
    $this->confirmMessage = $confirmMessage;
  }

  public function getValue(): string {
    return $this->value;
  }

  public function setValue(string $value): self {
    $this->value = $value;

    return $this;
  }

  public function getConfirmMessage(): ?string {
    return $this->confirmMessage;
  }

  public function setConfirmMessage(?string $confirmMessage): self {
    $this->confirmMessage = $confirmMessage;

    return $this;
  }

  public function getDataType(): string {
    return 'string';
  }

  public function getInputType(): string {
    return 'submit';
  }

}
