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
use Civi\RemoteTools\Form\FormSpec\Field\FieldCollectionField;
use Civi\RemoteTools\JsonSchema\FormSpec\RootFieldJsonSchemaFactoryInterface;
use Civi\RemoteTools\JsonSchema\JsonSchema;
use Civi\RemoteTools\JsonSchema\JsonSchemaObject;

final class FieldCollectionFieldFactory extends AbstractFieldJsonSchemaFactory {

  protected function doCreateSchema(
    AbstractFormField $field,
    RootFieldJsonSchemaFactoryInterface $factory
  ): JsonSchema {
    assert($field instanceof FieldCollectionField);

    $keywords = ['additionalProperties' => FALSE];
    if ($field->isReadOnly()) {
      $keywords['readOnly'] = TRUE;
    }

    $defaults = $field->getDefaultValue() ?? [];
    $properties = [];
    $required = [];
    foreach ($field->getFields() as $collectionField) {
      if ($field->isReadOnly()) {
        $collectionField->setReadOnly(TRUE);
      }
      if (array_key_exists($collectionField->getName(), $defaults)) {
        $collectionField->setDefaultValue($defaults[$collectionField->getName()]);
      }
      if ($collectionField->isRequired()) {
        $required[] = $collectionField->getName();
      }

      $properties[$collectionField->getName()] = $factory->createSchema($collectionField);
    }

    if ([] !== $required) {
      $keywords['required'] = $required;
    }

    return new JsonSchemaObject($properties, $keywords, $field->isNullable());
  }

  public function supportsField(AbstractFormField $field): bool {
    return $field instanceof FieldCollectionField;
  }

}
