<?php
declare(strict_types = 1);

namespace Civi\RemoteTools\JsonForms\FormSpec;

use Civi\RemoteTools\Form\FormSpec\FormElementInterface;
use Civi\RemoteTools\JsonForms\JsonFormsElement;

final class ElementUiSchemaFactory implements ElementUiSchemaFactoryInterface {

  /**
   * @phpstan-var iterable<ConcreteElementUiSchemaFactoryInterface>
   */
  private iterable $factories;

  /**
   * @phpstan-param iterable<ConcreteElementUiSchemaFactoryInterface> $factories
   */
  public function __construct(iterable $factories) {
    $this->factories = $factories;
  }

  public function createSchema(FormElementInterface $element): JsonFormsElement {
    foreach ($this->factories as $factory) {
      if ($factory->supportsElement($element)) {
        return $factory->createSchema($element, $this);
      }
    }

    throw new \InvalidArgumentException(sprintf(
      'Form element of type "%s" is not supported (class: %s)',
      $element->getType(),
      get_class($element),
    ));
  }

}
