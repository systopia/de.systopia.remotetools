<?php
declare(strict_types = 1);

namespace Civi\RemoteTools\JsonSchema\FormSpec;

use Civi\RemoteTools\Form\FormSpec\FormSpec;
use Civi\RemoteTools\JsonSchema\JsonSchema;
use Civi\RemoteTools\JsonSchema\JsonSchemaObject;

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
          $field->getFieldType(),
          $field->getName(),
          get_class($field),
        ));
      }
    }

    return new JsonSchemaObject($properties, ['required' => $required]);
  }

}
