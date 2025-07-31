<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
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

use Civi\RemoteTools\Form\FormSpec\DataTransformer\HtmlDataTransformer;
use Civi\RemoteTools\Form\FormSpec\FieldDataTransformerInterface;

/**
 * Input is sanitized with all non-body elements and unsafe elements being
 * removed (including their children). The list of the saved elements can be
 * found here:
 * https://wicg.github.io/sanitizer-api/#built-in-safe-default-configuration
 * Elements that additionally shall be allowed or removed, can be added via the
 * available methods.
 *
 * The default maximum length is 20000 to prevent DoS attacks. (This is the same
 * length used as default maximum length in the Symfony HTML sanitizer, see
 * https://symfony.com/doc/current/html_sanitizer.html#max-input-length.)
 *
 * @codeCoverageIgnore
 *
 * @api
 */
final class HtmlField extends AbstractTextField {

  /**
   * @phpstan-var array<string, list<string>|null>
   *   Elements that are allowed with a list of the allowed attributes. If the
   *   attribute list is NULL, all standard attributes defined by W3C are
   *   allowed.
   */
  private array $allowedElements = [];

  /**
   * @var array<string, true>
   *   Elements that are removed from the input, but their children are
   *   retained.
   */
  private array $blockedElements = [];

  /**
   * @var array<string, true>
   *    Elements that are removed from the input, including their children.
   */
  private array $droppedElements = [];

  public function __construct(string $name, string $label) {
    parent::__construct($name, $label);
    $this->setMaxLength(20000);
  }

  public function getInputType(): string {
    return 'html';
  }

  public function getDataTransformer(): FieldDataTransformerInterface {
    return new HtmlDataTransformer();
  }

  /**
   * @param list<string>|null $allowedAttributes
   *   If NULL, all standard attributes defined by W3C are allowed.
   */
  public function addAllowedElement(string $element, ?array $allowedAttributes): static {
    $this->allowedElements[$element] = $allowedAttributes;
    unset($this->blockedElements[$element]);
    unset($this->droppedElements[$element]);

    return $this;
  }

  /**
   * @phpstan-return array<string, list<string>|null>
   *   Elements that are allowed with a list of the allowed attributes. If the
   *   attribute list is NULL, all standard attributes defined by W3C are
   *   allowed.
   */
  public function getAllowedElements(): array {
    return $this->allowedElements;
  }

  /**
   * Element that shall be removed from the input, but with its children
   * retained.
   */
  public function addBlockedElement(string $element): static {
    $this->blockedElements[$element] = TRUE;
    unset($this->allowedElements[$element]);
    unset($this->droppedElements[$element]);

    return $this;
  }

  /**
   * @phpstan-return list<string>
   */
  public function getBlockedElements(): array {
    return array_keys($this->blockedElements);
  }

  /**
   * Element that shall be removed from the input including its children.
   */
  public function addDroppedElement(string $element): static {
    $this->droppedElements[$element] = TRUE;
    unset($this->allowedElements[$element]);
    unset($this->blockedElements[$element]);

    return $this;
  }

  /**
   * @phpstan-return list<string>
   */
  public function getDroppedElements(): array {
    return array_keys($this->droppedElements);
  }

}
