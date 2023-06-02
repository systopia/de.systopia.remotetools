<?php
declare(strict_types = 1);

namespace Civi\RemoteTools\JsonSchema\FormSpec\Factory;

use Civi\RemoteTools\Form\FormSpec\AbstractFormField;
use Civi\RemoteTools\Form\FormSpec\Field\AbstractNumberField;
use Civi\RemoteTools\JsonSchema\JsonSchema;
use Civi\RemoteTools\JsonSchema\JsonSchemaInteger;

final class IntegerFieldFactory extends AbstractFieldJsonSchemaFactory {

  public function createSchema(AbstractFormField $field): JsonSchema {
    $keywords = [];
    if ($field->hasDefaultValue()) {
      $keywords['default'] = $field->getDefaultValue();
    }
    if ($field instanceof AbstractNumberField) {
      if (NULL !== $field->getMaximum()) {
        $keywords['maximum'] = $field->getMaximum();
      }
      if (NULL !== $field->getMinimum()) {
        $keywords['minimum'] = $field->getMinimum();
      }
    }

    return new JsonSchemaInteger($keywords, $field->isNullable());
  }

  public function supportsField(AbstractFormField $field): bool {
    return 'integer' === $field->getDataType();
  }

}
