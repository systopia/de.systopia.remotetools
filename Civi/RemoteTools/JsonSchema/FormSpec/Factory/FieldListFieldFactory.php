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

namespace Civi\RemoteTools\JsonSchema\FormSpec\Factory;

use Civi\RemoteTools\Form\FormSpec\AbstractFormField;
use Civi\RemoteTools\Form\FormSpec\Field\FieldListField;
use Civi\RemoteTools\JsonSchema\FormSpec\RootFieldJsonSchemaFactoryInterface;
use Civi\RemoteTools\JsonSchema\JsonSchema;
use Civi\RemoteTools\JsonSchema\JsonSchemaArray;
use Webmozart\Assert\Assert;

final class FieldListFieldFactory extends AbstractFieldJsonSchemaFactory {

  protected function doCreateSchema(
    AbstractFormField $field,
    RootFieldJsonSchemaFactoryInterface $factory
  ): JsonSchema {
    assert($field instanceof FieldListField);

    $keywords = [];
    if ($field->hasDefaultValue()) {
      if (NULL === $field->getDefaultValue()) {
        $default = NULL;
      }
      else {
        assert(is_array($field->getDefaultValue()));
        $default = JsonSchema::convertToJsonSchemaArray(
          $factory->convertDefaultValuesInList($field->getItemField(), $field->getDefaultValue())
        );
      }
      $keywords['default'] = $default;
      if ($field->isReadOnly() && (NULL === $default || [] === $default)) {
        // We cannot use the const keyword if default isn't empty because it
        // might contain time dependent data (e.g. hashes in file URLs).
        $keywords['const'] = $default;
      }
    }

    if ($field->isReadOnly()) {
      $field->getItemField()->setReadOnly(TRUE);
      $keywords['readOnly'] = TRUE;
    }

    $keywords['uniqueItems'] = $field->isUniqueItems();
    if (NULL !== $field->getMinItems()) {
      $keywords['minItems'] = $field->getMinItems();
    }
    if (NULL !== $field->getMaxItems()) {
      $keywords['maxItems'] = $field->getMaxItems();
    }

    $items = $factory->createSchema($field->getItemField()->unsetDefaultValue());

    return new JsonSchemaArray($items, $keywords, $field->isNullable());
  }

  public function convertDefaultValuesInList(
    AbstractFormField $field,
    array $defaultValues,
    RootFieldJsonSchemaFactoryInterface $factory
  ): array {
    assert($field instanceof FieldListField);

    foreach ($defaultValues as &$defaultValue) {
      Assert::nullOrIsList($defaultValue);
      if (NULL !== $defaultValue) {
        $defaultValue = $factory->convertDefaultValuesInList($field->getItemField(), $defaultValue);
      }
    }

    return $defaultValues;
  }

  public function supportsField(AbstractFormField $field): bool {
    return $field instanceof FieldListField;
  }

}
