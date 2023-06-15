<?php
declare(strict_types = 1);

namespace Civi\RemoteTools\JsonForms\FormSpec\Factory\Control;

use Civi\RemoteTools\Form\FormSpec\AbstractFormInput;
use Civi\RemoteTools\Form\FormSpec\FormElementInterface;
use Civi\RemoteTools\JsonForms\FormSpec\ElementUiSchemaFactoryInterface;
use Civi\RemoteTools\JsonForms\FormSpec\Factory\AbstractConcreteElementUiSchemaFactory;
use Civi\RemoteTools\JsonForms\JsonFormsElement;
use Webmozart\Assert\Assert;

abstract class AbstractControlFactory extends AbstractConcreteElementUiSchemaFactory {

  final public function createSchema(
    FormElementInterface $element,
    ElementUiSchemaFactoryInterface $factory
  ): JsonFormsElement {
    Assert::isInstanceOf($element, AbstractFormInput::class);
    /** @var \Civi\RemoteTools\Form\FormSpec\AbstractFormInput $element */

    return $this->createInputSchema($element, $factory);
  }

  final public function supportsElement(FormElementInterface $element): bool {
    return $element instanceof AbstractFormInput && $this->supportsInput($element);
  }

  final protected function getScope(AbstractFormInput $field): string {
    return '#/properties/' . $field->getName();
  }

  abstract protected function createInputSchema(
    AbstractFormInput $input,
    ElementUiSchemaFactoryInterface $factory
  ): JsonFormsElement;

  abstract protected function supportsInput(AbstractFormInput $input): bool;

}
