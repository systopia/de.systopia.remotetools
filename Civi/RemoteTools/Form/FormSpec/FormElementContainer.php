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
 * @extends AbstractFormElementContainer<FormElementInterface>
 *
 * @api
 */
class FormElementContainer extends AbstractFormElementContainer implements FormElementInterface {

  use FormRuleTrait;

  private bool $collapsible = FALSE;

  private ?string $description;

  public function __construct(string $title, array $elements = [], ?string $description = NULL) {
    parent::__construct($title, $elements);
    $this->description = $description;
  }

  public function getType(): string {
    return 'container';
  }

  public function isCollapsible(): bool {
    return $this->collapsible;
  }

  public function setCollapsible(bool $collapsible): self {
    $this->collapsible = $collapsible;

    return $this;
  }

  public function getDescription(): ?string {
    return $this->description;
  }

  public function setDescription(?string $description): self {
    $this->description = $description;

    return $this;
  }

}
