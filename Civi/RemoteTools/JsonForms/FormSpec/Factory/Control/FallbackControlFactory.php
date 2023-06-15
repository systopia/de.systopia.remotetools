<?php
declare(strict_types = 1);

namespace Civi\RemoteTools\JsonForms\FormSpec\Factory\Control;

use Civi\RemoteTools\Form\FormSpec\AbstractFormInput;
use Civi\RemoteTools\JsonForms\FormSpec\ElementUiSchemaFactoryInterface;
use Civi\RemoteTools\JsonForms\JsonFormsControl;
use Civi\RemoteTools\JsonForms\JsonFormsElement;

final class FallbackControlFactory extends AbstractControlFactory {

  public static function getPriority(): int {
    return -100;
  }

  public function createInputSchema(
    AbstractFormInput $input,
    ElementUiSchemaFactoryInterface $factory
  ): JsonFormsElement {
    return new JsonFormsControl($this->getScope($input), $input->getLabel(), $input->getDescription());
  }

  protected function supportsInput(AbstractFormInput $input): bool {
    return TRUE;
  }

}
