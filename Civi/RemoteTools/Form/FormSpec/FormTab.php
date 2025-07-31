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

use Civi\RemoteTools\Form\FormSpec\Rule\FormRuleTrait;

/**
 * @codeCoverageIgnore
 *
 * @api
 */
class FormTab extends AbstractFormElementContainer implements FormElementInterface {

  use FormRuleTrait;

  public ?string $description;

  public function __construct(string $title, array $elements = [], ?string $description = NULL) {
    parent::__construct($title, $elements);
    $this->description = $description;
  }

  public function getType(): string {
    return 'tab';
  }

  public function getDescription(): ?string {
    return $this->description;
  }

  public function setDescription(?string $description): static {
    $this->description = $description;

    return $this;
  }

}
