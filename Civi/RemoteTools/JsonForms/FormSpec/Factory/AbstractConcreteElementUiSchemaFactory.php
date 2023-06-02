<?php
declare(strict_types = 1);

namespace Civi\RemoteTools\JsonForms\FormSpec\Factory;

use Civi\RemoteTools\JsonForms\FormSpec\ConcreteElementUiSchemaFactoryInterface;

abstract class AbstractConcreteElementUiSchemaFactory implements ConcreteElementUiSchemaFactoryInterface {

  public static function getPriority(): int {
    return 0;
  }

}
