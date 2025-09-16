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
use Civi\RemoteTools\Form\FormSpec\Field\FieldListField;
use Civi\RemoteTools\Form\FormSpec\FieldDataTransformerInterface;
use Webmozart\Assert\Assert;

final class FieldListFieldDataTransformer implements FieldDataTransformerInterface {

  private static self $instance;

  public static function getInstance(): FieldListFieldDataTransformer {
    return self::$instance ??= new self();
  }

  public function toEntityValue(mixed $data, AbstractFormField $field, ?array $defaultValuesInList = NULL): mixed {
    assert($field instanceof FieldListField);
    if (is_array($data)) {
      $itemField = $field->getItemField();
      $itemFieldTransformer = $itemField->getDataTransformer();
      if (NULL === $defaultValuesInList) {
        $defaultValuesInList = $field->getDefaultValue();
      }
      else {
        Assert::allNullOrIsArray($defaultValuesInList);
        /** @var list<mixed> $defaultValuesInList */
        $defaultValuesInList = array_merge(...array_filter($defaultValuesInList, fn ($value) => $value !== NULL));
      }

      foreach ($data as &$value) {
        $value = $itemFieldTransformer->toEntityValue($value, $itemField, $defaultValuesInList);
      }
    }

    return $data;
  }

}
