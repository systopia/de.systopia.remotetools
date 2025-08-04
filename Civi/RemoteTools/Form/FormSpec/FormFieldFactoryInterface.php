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

namespace Civi\RemoteTools\Form\FormSpec;

/**
 * @phpstan-type dataTypeT 'Array'|'Boolean'|'Date'|'Float'|'Integer'|'Money'|'String'|'Text'|'Timestamp'
 * @phpstan-type inputTypeT 'ChainSelect'|'CheckBox'|'Date'|'DisplayOnly'|'Email'|'EntityRef'|'File'|'Hidden'|'Location'|'Number'|'Radio'|'RichTextEditor'|'Select'|'Text'|'TextArea'|'Url'
 * @phpstan-type optionsT array<int|string, string>|list<array{id: int|string, label: string}>
 *
 * @phpstan-type fieldT array{
 *   name: string,
 *   data_type: dataTypeT,
 *   input_type: inputTypeT,
 *   fk_entity?: string,
 *   fk_column?: string,
 *   serialize?: int,
 *   options?: bool|optionsT,
 *   label?: string,
 *   title?: string,
 *   description?: string,
 *   help_pre?: string,
 *   help_post?: string,
 *   required?: bool,
 *   readonly?: bool,
 *   default_value?: scalar,
 *   input_attrs?: array{maxlength?: int},
 * }
 *
 * @apiService
 */
interface FormFieldFactoryInterface {

  /**
   * Creates a form field from a APIv4 field specification. If there's no
   * appropriate form field a text field will be created.
   *
   * Fields with data_type "Array" cannot be supported because there's no
   * information about the data structure.
   *
   * @phpstan-param fieldT $field
   *   APIv4 field specification.
   * @phpstan-param array<string, mixed> $entityValues
   *   The default value will be fetched from this array. If not set, the
   *   default value from the field specification will be used (if available).
   *   For fields of type "Money" the currency will be fetched from attribute
   *   "currency". The CiviCRM settings are used as fallback.
   *   For file fields with existing value URL and file name are set
   *   appropriately. On CiviCRM <6.1 this requires the entity ID to be set at
   *   key 'id'.
   * @param string $formFieldNamePrefix
   *   This string will be used as prefix for the name of the form field.
   *
   * @throws \CRM_Core_Exception
   * @throws \InvalidArgumentException
   */
  public function createFormField(
    array $field,
    ?array $entityValues,
    string $formFieldNamePrefix = ''
  ): AbstractFormField;

}
