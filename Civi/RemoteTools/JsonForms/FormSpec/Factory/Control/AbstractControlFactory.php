<?php
declare(strict_types = 1);

namespace Civi\RemoteTools\JsonForms\FormSpec\Factory\Control;

use Civi\RemoteTools\Form\FormSpec\AbstractFormField;
use Civi\RemoteTools\Form\FormSpec\FormElementInterface;
use Civi\RemoteTools\JsonForms\FormSpec\ElementUiSchemaFactoryInterface;
use Civi\RemoteTools\JsonForms\FormSpec\Factory\AbstractConcreteElementUiSchemaFactory;
use Civi\RemoteTools\JsonForms\JsonFormsElement;
use Webmozart\Assert\Assert;

abstract class AbstractControlFactory extends AbstractConcreteElementUiSchemaFactory {

  public function createSchema(
    FormElementInterface $element,
    ElementUiSchemaFactoryInterface $factory
  ): JsonFormsElement {
    Assert::isInstanceOf($element, AbstractFormField::class);
    /** @var \Civi\RemoteTools\Form\FormSpec\AbstractFormField $element */

    return $this->createFieldSchema($element, $factory);
  }

  public function supportsElement(FormElementInterface $element): bool {
    return $element instanceof AbstractFormField && $this->supportsField($element);
  }

  protected function getScope(AbstractFormField $field): string {
    return '/#properties/' . $field->getName();
  }

  abstract protected function createFieldSchema(
    AbstractFormField $field,
    ElementUiSchemaFactoryInterface $factory
  ): JsonFormsElement;

  abstract protected function supportsField(AbstractFormField $field): bool;

}
