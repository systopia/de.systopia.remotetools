<?php
declare(strict_types = 1);

namespace Civi\RemoteTools\JsonForms\FormSpec;

use Civi\RemoteTools\Form\FormSpec\FormSpec;
use Civi\RemoteTools\JsonForms\JsonFormsElement;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsGroup;

final class UiSchemaFactory implements UiSchemaFactoryInterface {

  private ElementUiSchemaFactoryInterface $factory;

  public function __construct(ElementUiSchemaFactoryInterface $factory) {
    $this->factory = $factory;
  }

  public function createUiSchema(FormSpec $formSpec): JsonFormsElement {
    $elements = array_map([$this->factory, 'createSchema'], $formSpec->getElements());

    return new JsonFormsGroup($formSpec->getTitle(), $elements);
  }

}
