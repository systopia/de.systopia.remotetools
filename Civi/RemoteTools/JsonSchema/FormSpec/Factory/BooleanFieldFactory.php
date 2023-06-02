<?php
declare(strict_types = 1);

namespace Civi\RemoteTools\JsonSchema\FormSpec\Factory;

use Civi\RemoteTools\Form\FormSpec\AbstractFormField;
use Civi\RemoteTools\JsonSchema\JsonSchema;
use Civi\RemoteTools\JsonSchema\JsonSchemaBoolean;

final class BooleanFieldFactory extends AbstractFieldJsonSchemaFactory {

  public function createSchema(AbstractFormField $field): JsonSchema {
    $keywords = [];
    if ($field->hasDefaultValue()) {
      $keywords['default'] = $field->getDefaultValue();
    }

    return new JsonSchemaBoolean($keywords, $field->isNullable());
  }

  public function supportsField(AbstractFormField $field): bool {
    return 'boolean' === $field->getDataType();
  }

}
