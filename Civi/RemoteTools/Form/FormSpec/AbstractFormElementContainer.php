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

use Civi\RemoteTools\Form\FormSpec\Button\SubmitButton;

/**
 * @template T of FormElementInterface
 *
 * @api
 */
abstract class AbstractFormElementContainer {

  private string $title;

  /**
   * @phpstan-var array<T>
   */
  private array $elements = [];

  /**
   * @phpstan-param array<T> $elements
   */
  public function __construct(string $title, array $elements = []) {
    $this->title = $title;
    $this->elements = $elements;
  }

  public function getTitle(): string {
    return $this->title;
  }

  /**
   * @return $this
   */
  public function setTitle(string $title): self {
    $this->title = $title;

    return $this;
  }

  /**
   * @phpstan-param T $element
   *
   * @return $this
   */
  public function addElement(FormElementInterface $element): self {
    $this->elements[] = $element;

    return $this;
  }

  /**
   * @phpstan-return array<T>
   */
  public function getElements(): array {
    return $this->elements;
  }

  public function hasElements(): bool {
    return [] !== $this->getElements();
  }

  /**
   * @phpstan-param T $element
   *
   * @return $this
   */
  public function insertElement(FormElementInterface $element, int $index): self {
    array_splice($this->elements, $index, 0, [$element]);

    return $this;
  }

  /**
   * @phpstan-param array<T> $elements
   */
  public function setElements(array $elements): self {
    $this->elements = $elements;

    return $this;
  }

  /**
   * @phpstan-return array<string, AbstractFormField>
   *   Field names mapped to fields.
   */
  public function getFields(): array {
    $fields = [];
    foreach ($this->elements as $element) {
      if ($element instanceof AbstractFormField) {
        $fields[$element->getName()] = $element;
      }
      elseif ($element instanceof AbstractFormElementContainer) {
        $containerFields = $element->getFields();
        $nonUniqueFields = array_keys(array_intersect_key($fields, $containerFields));
        if ([] !== $nonUniqueFields) {
          throw new \RuntimeException(sprintf(
            'Form spec contains fields more than once: %s',
            implode(', ', $nonUniqueFields),
          ));
        }

        $fields = array_merge($fields, $containerFields);
      }
    }

    return $fields;
  }

  /**
   * @phpstan-return array<string, array<SubmitButton>>
   *   Mapping of button name to buttons with that name.
   */
  public function getSubmitButtons(): array {
    $buttons = [];

    foreach ($this->elements as $element) {
      if ($element instanceof SubmitButton) {
        if (isset($buttons[$element->getName()])) {
          $buttons[$element->getName()][] = $element;
        }
        else {
          $buttons[$element->getName()] = [$element];
        }
      }
      elseif ($element instanceof FormElementContainer) {
        $containerButtons = $element->getSubmitButtons();
        $buttons = array_merge_recursive($buttons, $containerButtons);
      }
    }

    return $buttons;
  }

}
