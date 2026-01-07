<?php
/*
 * Copyright (C) 2026 SYSTOPIA GmbH
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

namespace Civi\RemoteTools\Form\FormSpec\Traits;

trait CssClassesTrait {

  /**
   * @var list<string>
   *   CSS classes that will be set when rendered to HTML.
   */
  private array $cssClasses = [];

  public function addCssClass(string $cssClass): static {
    $this->cssClasses[] = $cssClass;

    return $this;
  }

  public function hasCssClass(string $cssClass): bool {
    return in_array($cssClass, $this->cssClasses, TRUE);
  }

  public function removeCssClass(string $cssClass): static {
    $this->cssClasses = array_values(array_filter($this->cssClasses, fn ($class) => $class !== $cssClass));

    return $this;
  }

  /**
   * @return list<string>
   */
  public function getCssClasses(): array {
    return $this->cssClasses;
  }

  public function hasCssClasses(): bool {
    return [] !== $this->cssClasses;
  }

  /**
   * @param list<string> $cssClasses
   */
  public function setCssClasses(array $cssClasses): static {
    $this->cssClasses = $cssClasses;

    return $this;
  }

}
