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

abstract class AbstractFormInput implements FormElementInterface {

  private string $name;

  private string $label;

  private string $description = '';

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

  abstract public function getDataType(): string;

  abstract public function getInputType(): string;

}
