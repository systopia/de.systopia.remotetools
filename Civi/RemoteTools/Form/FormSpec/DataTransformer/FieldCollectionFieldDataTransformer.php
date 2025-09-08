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

namespace Civi\RemoteTools\Form\FormSpec\DataTransformer;

use Civi\RemoteTools\Form\FormSpec\AbstractFormField;
use Civi\RemoteTools\Form\FormSpec\Field\FieldCollectionField;
use Civi\RemoteTools\Form\FormSpec\FieldDataTransformerInterface;

final class FieldCollectionFieldDataTransformer implements FieldDataTransformerInterface {

  private static self $instance;

  public static function getInstance(): FieldCollectionFieldDataTransformer {
    return self::$instance ??= new self();
  }

  public function toEntityValue(mixed $data, AbstractFormField $field, ?array $defaultValuesInList = NULL): mixed {
    assert($field instanceof FieldCollectionField);
    if (is_array($data)) {
      foreach ($field->getFields() as $subField) {
        if (array_key_exists($subField->getName(), $data)) {
          $subFieldDefaultValuesInList = NULL === $defaultValuesInList ? NULL
            : array_column($defaultValuesInList, $subField->getName());
          $data[$subField->getName()] = $subField->getDataTransformer()
            ->toEntityValue($data[$subField->getName()], $subField, $subFieldDefaultValuesInList);
        }
      }
    }

    return $data;
  }

}
