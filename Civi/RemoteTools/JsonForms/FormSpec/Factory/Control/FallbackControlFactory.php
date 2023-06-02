<?php
declare(strict_types = 1);

namespace Civi\RemoteTools\JsonForms\FormSpec\Factory\Control;

use Civi\RemoteTools\Form\FormSpec\AbstractFormField;
use Civi\RemoteTools\JsonForms\FormSpec\ElementUiSchemaFactoryInterface;
use Civi\RemoteTools\JsonForms\JsonFormsControl;
use Civi\RemoteTools\JsonForms\JsonFormsElement;

final class FallbackControlFactory extends AbstractControlFactory {

  public static function getPriority(): int {
    return -100;
  }

  public function createFieldSchema(
    AbstractFormField $field,
    ElementUiSchemaFactoryInterface $factory
  ): JsonFormsElement {
    return new JsonFormsControl($this->getScope($field), $field->getLabel(), $field->getDescription());
  }

  protected function supportsField(AbstractFormField $field): bool {
    return TRUE;
  }

}
