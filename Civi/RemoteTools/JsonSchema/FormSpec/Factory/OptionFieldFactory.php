<?php
declare(strict_types = 1);

namespace Civi\RemoteTools\JsonSchema\FormSpec\Factory;

use Civi\RemoteTools\Form\FormSpec\AbstractFormField;
use Civi\RemoteTools\Form\FormSpec\Field\AbstractOptionField;
use Civi\RemoteTools\JsonSchema\JsonSchema;
use Civi\RemoteTools\JsonSchema\Util\JsonSchemaUtil;
use Webmozart\Assert\Assert;

final class OptionFieldFactory extends AbstractFieldJsonSchemaFactory {

  public function createSchema(AbstractFormField $field): JsonSchema {
    Assert::isInstanceOf($field, AbstractOptionField::class);
    /** @var \Civi\RemoteTools\Form\FormSpec\Field\AbstractOptionField $field */
    $keywords = [
      'type' => ['string', 'integer'],
      'oneOf' => JsonSchemaUtil::buildTitledOneOf($field->getOptions()),
    ];
    if ($field->isNullable()) {
      $keywords['type'][] = 'null';
      $keywords['oneOf'][] = JsonSchema::fromArray(['const' => NULL]);
    }
    if ($field->hasDefaultValue()) {
      $keywords['default'] = $field->getDefaultValue();
    }

    return new JsonSchema($keywords);
  }

  public function supportsField(AbstractFormField $field): bool {
    return $field instanceof AbstractOptionField;
  }

}
