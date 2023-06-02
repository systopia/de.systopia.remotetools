<?php
declare(strict_types = 1);

namespace Civi\RemoteTools\JsonForms\FormSpec;

use Civi\RemoteTools\Form\FormSpec\FormSpec;
use Civi\RemoteTools\JsonForms\JsonFormsElement;

interface UiSchemaFactoryInterface {

  public function createUiSchema(FormSpec $formSpec): JsonFormsElement;

}
