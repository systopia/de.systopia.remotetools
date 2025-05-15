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

namespace Civi\RemoteTools\Form\FormSpec;

final class FormSpecDataTransformer implements DataTransformerInterface {

  private FormSpec $formSpec;

  public function __construct(FormSpec $formSpec) {
    $this->formSpec = $formSpec;
  }

  /**
   * @inheritDoc
   */
  public function toEntityValues(array $formData, ?array $currentEntityValues, ?int $contactId): array {
    $fields = $this->formSpec->getFields();
    foreach ($fields as $fieldName => $field) {
      if (isset($formData[$fieldName])) {
        $formData[$fieldName] = $field->getDataTransformer()->toEntityValue($formData[$fieldName], $field);
      }
    }

    return $formData;
  }

}
