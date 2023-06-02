<?php
declare(strict_types = 1);

namespace Civi\RemoteTools\JsonForms\FormSpec;

use Civi\RemoteTools\Form\FormSpec\FormElementInterface;
use Civi\RemoteTools\JsonForms\JsonFormsElement;

interface ElementUiSchemaFactoryInterface {

  public function createSchema(FormElementInterface $element): JsonFormsElement;

}
