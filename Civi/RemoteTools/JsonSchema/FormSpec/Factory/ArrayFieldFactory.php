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
use Civi\RemoteTools\Form\FormSpec\Field\ArrayField;
use Civi\RemoteTools\Form\FormSpec\FormSpec;
use Civi\RemoteTools\JsonSchema\FormSpec\JsonSchemaFactoryInterface;
use Civi\RemoteTools\JsonSchema\JsonSchema;
use Civi\RemoteTools\JsonSchema\JsonSchemaArray;

final class ArrayFieldFactory extends AbstractFieldJsonSchemaFactory {

  protected function doCreateSchema(AbstractFormField $field, JsonSchemaFactoryInterface $factory): JsonSchema {
    assert($field instanceof ArrayField);

    $keywords = [];
    if ($field->hasDefaultValue()) {
      if (NULL === $field->getDefaultValue()) {
        $default = NULL;
      }
      else {
        $default = JsonSchema::convertToJsonSchemaArray($field->getDefaultValue());
      }
      $keywords['default'] = $default;
    }
    if ($field->isReadOnly()) {
      $keywords['readOnly'] = TRUE;
      $keywords['const'] = $default ?? NULL;
    }
    if (NULL !== $field->getMinItems()) {
      $keywords['minItems'] = $field->getMinItems();
    }
    if (NULL !== $field->getMaxItems()) {
      $keywords['maxItems'] = $field->getMaxItems();
    }

    $items = $factory->createJsonSchema(new FormSpec('', $field->getFields()));

    return new JsonSchemaArray($items, $keywords, $field->isNullable());
  }

  public function supportsField(AbstractFormField $field): bool {
    return $field instanceof ArrayField;
  }

}
