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

use Civi\RemoteTools\Form\FormSpec\Rule\FormRule;

/**
 * @api
 */
interface FormElementInterface {

  public function getType(): string;

  public function addCssClass(string $cssClass): static;

  public function hasCssClass(string $cssClass): bool;

  public function removeCssClass(string $cssClass): static;

  /**
   * @return list<string>
   *   CSS classes that will be set when rendered to HTML.
   */
  public function getCssClasses(): array;

  /**
   * @param list<string> $cssClasses
   *   CSS classes that will be set when rendered to HTML.
   */
  public function setCssClasses(array $cssClasses): static;

  public function getRule(): ?FormRule;

  /**
   * @return $this
   */
  public function setRule(?FormRule $rule): static;

}
