<?php
declare(strict_types = 1);

namespace Civi\RemoteTools\JsonForms\FormSpec;

use Civi\RemoteTools\Form\FormSpec\FormElementInterface;
use Civi\RemoteTools\JsonForms\JsonFormsElement;

interface ConcreteElementUiSchemaFactoryInterface {

  public const SERVICE_TAG = 'remote_tools.json_forms.form_spec.element_factory';

  public static function getPriority(): int;

  public function createSchema(
    FormElementInterface $element,
    ElementUiSchemaFactoryInterface $factory
  ): JsonFormsElement;

  public function supportsElement(FormElementInterface $element): bool;

}
