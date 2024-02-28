<?php
declare(strict_types = 1);

namespace Civi\RemoteTools\JsonSchema\FormSpec;

use Civi\RemoteTools\Form\FormSpec\AbstractFormField;
use Civi\RemoteTools\JsonSchema\JsonSchema;

interface FieldJsonSchemaFactoryInterface {

  public const SERVICE_TAG = 'remote_tools.json_schema.form_spec.field_factory';

  public static function getPriority(): int;

  public function createSchema(AbstractFormField $field): JsonSchema;

  public function supportsField(AbstractFormField $field): bool;

}
