<?php
declare(strict_types = 1);

namespace Civi\RemoteTools\JsonSchema\FormSpec\Factory;

use Civi\RemoteTools\Form\FormSpec\AbstractFormField;
use Civi\RemoteTools\Form\FormSpec\Field\AbstractNumberField;
use Civi\RemoteTools\Form\FormSpec\Field\FloatField;
use Civi\RemoteTools\JsonSchema\JsonSchema;
use Civi\RemoteTools\JsonSchema\JsonSchemaNumber;

final class NumberFieldFactory extends AbstractFieldJsonSchemaFactory {

  public static function getPriority(): int {
    return -1;
  }

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
      if ($field instanceof FloatField && NULL !== $field->getPrecision()) {
        $keywords['precision'] = $field->getPrecision();
      }
    }

    return new JsonSchemaNumber($keywords, $field->isNullable());
  }

  public function supportsField(AbstractFormField $field): bool {
    return 'number' === $field->getDataType();
  }

}
