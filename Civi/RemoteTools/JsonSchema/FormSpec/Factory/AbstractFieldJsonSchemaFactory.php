<?php
declare(strict_types = 1);

namespace Civi\RemoteTools\JsonSchema\FormSpec\Factory;

use Civi\RemoteTools\JsonSchema\FormSpec\FieldJsonSchemaFactoryInterface;

abstract class AbstractFieldJsonSchemaFactory implements FieldJsonSchemaFactoryInterface {

  public static function getPriority(): int {
    return 0;
  }

}
