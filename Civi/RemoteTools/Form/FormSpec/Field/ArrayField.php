<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
 *
 *  This program is free software: you can redistribute it and/or modify it under
 *  the terms of the GNU Affero General Public License as published by the Free
 *  Software Foundation, either version 3 of the License, or (at your option) any
 *  later version.
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
 * @extends AbstractFormField<list<array<string, scalar>>>
 *
 * @codeCoverageIgnore
 *
 * @api
 */
final class ArrayField extends AbstractFormField {

  public const LAYOUT_VERTICAL = 'VerticalLayout';

  public const LAYOUT_TABLE_ROW = 'TableRow';

  /**
   * @var list<\Civi\RemoteTools\Form\FormSpec\AbstractFormField>
   */
  private array $fields;

  private string $itemLayout = self::LAYOUT_TABLE_ROW;

  /**
   * @phpstan-var non-negative-int|null
   */
  private ?int $maxItems = NULL;

  /**
   * @phpstan-var non-negative-int|null
   */
  private ?int $minItems = NULL;

  private ?string $addButtonLabel = NULL;

  private ?string $removeButtonLabel = NULL;

  /**
   * @param list<\Civi\RemoteTools\Form\FormSpec\AbstractFormField> $fields
   */
  public function __construct(string $name, string $label, array $fields) {
    parent::__construct($name, $label);
    $this->fields = $fields;
  }

  public function getDataType(): string {
    return 'array';
  }

  public function getInputType(): string {
    return 'array';
  }

  public function addField(AbstractFormField $field): static {
    $this->fields[] = $field;

    return $this;
  }

  /**
   * @return list<\Civi\RemoteTools\Form\FormSpec\AbstractFormField>
   */
  public function getFields(): array {
    return $this->fields;
  }

  public function insertField(AbstractFormField $field, int $index): static {
    array_splice($this->fields, $index, 0, [$field]);

    return $this;
  }

  /**
   * @param list<\Civi\RemoteTools\Form\FormSpec\AbstractFormField> $fields
   */
  public function setFields(array $fields): static {
    $this->fields = $fields;

    return $this;
  }

  public function getItemLayout(): string {
    return $this->itemLayout;
  }

  public function setItemLayout(string $itemLayout): static {
    $this->itemLayout = $itemLayout;

    return $this;
  }

  /**
   * @phpstan-return non-negative-int|null
   */
  public function getMaxItems(): ?int {
    return $this->maxItems;
  }

  /**
   * @phpstan-param non-negative-int|null $maxItems
   */
  public function setMaxItems(?int $maxItems): static {
    $this->maxItems = $maxItems;

    return $this;
  }

  public function getMinItems(): ?int {
    return $this->minItems;
  }

  /**
   * @phpstan-param non-negative-int|null $minItems
   */
  public function setMinItems(?int $minItems): static {
    $this->minItems = $minItems;

    return $this;
  }

  public function getAddButtonLabel(): ?string {
    return $this->addButtonLabel;
  }

  public function setAddButtonLabel(?string $addButtonLabel): static {
    $this->addButtonLabel = $addButtonLabel;

    return $this;
  }

  public function getRemoveButtonLabel(): ?string {
    return $this->removeButtonLabel;
  }

  public function setRemoveButtonLabel(?string $removeButtonLabel): static {
    $this->removeButtonLabel = $removeButtonLabel;

    return $this;
  }

}
