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
 * This field combines the input of multiple fields to a single property.
 *
 * @extends AbstractFormField<array<string, scalar>>
 *
 * @codeCoverageIgnore
 *
 * @api
 */
final class FieldCollectionField extends AbstractFormField {

  /**
   * @var list<\Civi\RemoteTools\Form\FormSpec\AbstractFormField>
   */
  private array $fields;

  /**
   * @param list<\Civi\RemoteTools\Form\FormSpec\AbstractFormField> $fields
   */
  public function __construct(string $name, string $label, array $fields = []) {
    parent::__construct($name, $label);
    $this->fields = $fields;
  }

  public function getDataType(): string {
    return 'object';
  }

  public function getInputType(): string {
    return 'fieldCollection';
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

}
