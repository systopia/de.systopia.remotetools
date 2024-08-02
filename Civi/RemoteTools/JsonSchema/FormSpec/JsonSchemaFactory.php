<?php
/*
 * Copyright (C) 2024 SYSTOPIA GmbH
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

namespace Civi\RemoteTools\JsonSchema\FormSpec;

use Civi\RemoteTools\Form\FormSpec\FormSpec;
use Civi\RemoteTools\JsonSchema\JsonSchema;
use Civi\RemoteTools\JsonSchema\JsonSchemaObject;
use Civi\RemoteTools\JsonSchema\JsonSchemaString;

final class JsonSchemaFactory implements JsonSchemaFactoryInterface {

  /**
   * @phpstan-var iterable<FieldJsonSchemaFactoryInterface>
   */
  private iterable $schemaFactories;

  /**
   * @phpstan-param iterable<FieldJsonSchemaFactoryInterface> $schemaFactories
   */
  public function __construct(iterable $schemaFactories) {
    $this->schemaFactories = $schemaFactories;
  }

  public function createJsonSchema(FormSpec $formSpec): JsonSchema {
    $properties = [];
    $required = [];
    foreach ($formSpec->getFields() as $field) {
      if ($field->isRequired()) {
        $required[] = $field->getName();
      }

      foreach ($this->schemaFactories as $fieldSchemaFactory) {
        if ($fieldSchemaFactory->supportsField($field)) {
          $properties[$field->getName()] = $fieldSchemaFactory->createSchema($field);
          break;
        }
      }

      if (!isset($properties[$field->getName()])) {
        throw new \InvalidArgumentException(sprintf(
          'Unsupported field type "%s" (field: %s, class: %s)',
          $field->getInputType(),
          $field->getName(),
          get_class($field),
        ));
      }
    }

    $oneOf = [];
    foreach ($formSpec->getSubmitButtons() as $name => $buttons) {
      // Require one of the buttons to be pressed.
      $oneOf[] = new JsonSchema(['required' => [$name]]);
      $values = [];
      foreach ($buttons as $button) {
        $values[] = $button->getValue();
      }

      $properties[$name] = new JsonSchemaString(['enum' => $values]);
    }

    $keywords = [
      'required' => $required,
      'additionalProperties' => FALSE,
    ];
    if ([] !== $oneOf) {
      $keywords['oneOf'] = $oneOf;
    }

    return new JsonSchemaObject($properties, $keywords);
  }

}
