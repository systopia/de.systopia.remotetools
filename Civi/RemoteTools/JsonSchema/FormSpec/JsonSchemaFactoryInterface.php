<?php
declare(strict_types = 1);

namespace Civi\RemoteTools\JsonSchema\FormSpec;

use Civi\RemoteTools\Form\FormSpec\FormSpec;
use Civi\RemoteTools\JsonSchema\JsonSchema;

interface JsonSchemaFactoryInterface {

  public function createJsonSchema(FormSpec $formSpec): JsonSchema;

}
