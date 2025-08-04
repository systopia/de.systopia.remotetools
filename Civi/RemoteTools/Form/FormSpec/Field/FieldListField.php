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
 * This field type provides the possibility to enter multiple values of the same
 * type defined by the item field. The name of the item field has no influence.
 *
 * @extends AbstractFormField<list<scalar|array<int|string, mixed>>>
 *
 * @codeCoverageIgnore
 *
 * @api
 */
final class FieldListField extends AbstractFormField {

  public const LAYOUT_VERTICAL = 'VerticalLayout';

  public const LAYOUT_TABLE_ROW = 'TableRow';

  private AbstractFormField $itemField;

  private string $itemLayout = self::LAYOUT_TABLE_ROW;

  /**
   * @phpstan-var non-negative-int|null
   */
  private ?int $maxItems = NULL;

  /**
   * @phpstan-var non-negative-int|null
   */
  private ?int $minItems = NULL;

  private bool $uniqueItems = FALSE;

  private ?string $addButtonLabel = NULL;

  private ?string $removeButtonLabel = NULL;

  public function __construct(string $name, string $label, AbstractFormField $itemField) {
    parent::__construct($name, $label);
    $this->itemField = $itemField;
  }

  public function getDataType(): string {
    return 'array';
  }

  public function getInputType(): string {
    return 'fieldList';
  }

  public function getItemField(): AbstractFormField {
    return $this->itemField;
  }

  public function setItemField(AbstractFormField $itemField): self {
    $this->itemField = $itemField;

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

  /**
   * @return bool
   *   If TRUE each item must be unique.
   */
  public function isUniqueItems(): bool {
    return $this->uniqueItems;
  }

  /**
   * @param bool $uniqueItems
   *   If TRUE each item must be unique.
   */
  public function setUniqueItems(bool $uniqueItems): self {
    $this->uniqueItems = $uniqueItems;

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
