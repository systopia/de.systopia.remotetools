<?php
declare(strict_types = 1);

namespace Civi\RemoteTools\JsonSchema\FormSpec\Factory;

use Civi\RemoteTools\Form\FormSpec\AbstractFormField;
use Civi\RemoteTools\Form\FormSpec\Field\AbstractTextField;
use Civi\RemoteTools\JsonSchema\JsonSchema;
use Civi\RemoteTools\JsonSchema\JsonSchemaString;

final class StringFieldFactory extends AbstractFieldJsonSchemaFactory {

  public static function getPriority(): int {
    return -1;
  }

  public function createSchema(AbstractFormField $field): JsonSchema {
    $keywords = [];
    if ($field->hasDefaultValue()) {
      $keywords['default'] = $field->getDefaultValue();
    }
    if ($field instanceof AbstractTextField) {
      if (NULL !== $field->getMaxLength()) {
        $keywords['maxLength'] = $field->getMaxLength();
      }
      if (NULL !== $field->getMinLength()) {
        $keywords['minLength'] = $field->getMinLength();
      }
      if (NULL !== $field->getPattern()) {
        $keywords['pattern'] = $field->getPattern();
      }
    }

    return new JsonSchemaString($keywords, $field->isNullable());
  }

  public function supportsField(AbstractFormField $field): bool {
    return 'string' === $field->getDataType();
  }

}
