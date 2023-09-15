<?php
declare(strict_types = 1);

namespace Civi\RemoteTools\JsonForms\FormSpec\Factory\Control;

use Civi\RemoteTools\Form\FormSpec\AbstractFormInput;
use Civi\RemoteTools\Form\FormSpec\Field\MultilineTextField;
use Civi\RemoteTools\JsonForms\FormSpec\ElementUiSchemaFactoryInterface;
use Civi\RemoteTools\JsonForms\JsonFormsControl;
use Civi\RemoteTools\JsonForms\JsonFormsElement;
use Webmozart\Assert\Assert;

final class MultiLineTextControlFactory extends AbstractControlFactory {

  protected function createInputSchema(
    AbstractFormInput $input,
    ElementUiSchemaFactoryInterface $factory
  ): JsonFormsElement {
    Assert::isInstanceOf($input, MultilineTextField::class);

    return new JsonFormsControl(
      $this->getScope($input), $input->getLabel(), $input->getDescription(), ['multi' => TRUE],
    );
  }

  protected function supportsInput(AbstractFormInput $input): bool {
    return $input instanceof MultilineTextField;
  }

}
