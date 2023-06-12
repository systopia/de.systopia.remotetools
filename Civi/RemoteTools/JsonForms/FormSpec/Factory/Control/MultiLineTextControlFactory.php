<?php
declare(strict_types = 1);

namespace Civi\RemoteTools\JsonForms\FormSpec\Factory\Control;

use Civi\RemoteTools\Form\FormSpec\AbstractFormField;
use Civi\RemoteTools\Form\FormSpec\Field\MultilineTextField;
use Civi\RemoteTools\JsonForms\FormSpec\ElementUiSchemaFactoryInterface;
use Civi\RemoteTools\JsonForms\JsonFormsControl;
use Civi\RemoteTools\JsonForms\JsonFormsElement;
use Webmozart\Assert\Assert;

final class MultiLineTextControlFactory extends AbstractControlFactory {

  protected function createFieldSchema(
    AbstractFormField $field,
    ElementUiSchemaFactoryInterface $factory
  ): JsonFormsElement {
    Assert::isInstanceOf($field, MultilineTextField::class);

    return new JsonFormsControl(
      $this->getScope($field),
      $field->getLabel(),
      $field->getDescription(),
      NULL,
      NULL,
      ['multi' => TRUE],
    );
  }

  protected function supportsField(AbstractFormField $field): bool {
    return $field instanceof MultilineTextField;
  }

}
