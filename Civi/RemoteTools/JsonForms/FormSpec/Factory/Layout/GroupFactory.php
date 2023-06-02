<?php
declare(strict_types = 1);

namespace Civi\RemoteTools\JsonForms\FormSpec\Factory\Layout;

use Civi\RemoteTools\Form\FormSpec\FormElementContainer;
use Civi\RemoteTools\Form\FormSpec\FormElementInterface;
use Civi\RemoteTools\JsonForms\FormSpec\ElementUiSchemaFactoryInterface;
use Civi\RemoteTools\JsonForms\FormSpec\Factory\AbstractConcreteElementUiSchemaFactory;
use Civi\RemoteTools\JsonForms\JsonFormsElement;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsGroup;
use Webmozart\Assert\Assert;

final class GroupFactory extends AbstractConcreteElementUiSchemaFactory {

  public function createSchema(
    FormElementInterface $element,
    ElementUiSchemaFactoryInterface $factory
  ): JsonFormsElement {
    Assert::isInstanceOf($element, FormElementContainer::class);
    /** @var \Civi\RemoteTools\Form\FormSpec\FormElementContainer $element */
    $elements = array_map([$factory, 'createSchema'], $element->getElements());

    return new JsonFormsGroup($element->getTitle(), $elements);
  }

  public function supportsElement(FormElementInterface $element): bool {
    return $element instanceof FormElementContainer;
  }

}
