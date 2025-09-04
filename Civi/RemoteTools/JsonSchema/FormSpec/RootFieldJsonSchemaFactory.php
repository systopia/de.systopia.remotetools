<?php
/*
 * Copyright (C) 2024 SYSTOPIA GmbH
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as published by
 *  the Free Software Foundation in version 3.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types = 1);

namespace Civi\RemoteTools\JsonSchema\FormSpec;

use Civi\RemoteTools\Form\FormSpec\AbstractFormField;
use Civi\RemoteTools\JsonSchema\JsonSchema;

final class RootFieldJsonSchemaFactory implements RootFieldJsonSchemaFactoryInterface {

  /**
   * @phpstan-var iterable<FieldJsonSchemaFactoryInterface>
   */
  private iterable $schemaFactories;

  /**
   * @phpstan-param iterable<FieldJsonSchemaFactoryInterface> $schemaFactories
   */
  public function __construct(iterable $schemaFactories) {
    $this->schemaFactories = $schemaFactories;
  }

  public function createSchema(AbstractFormField $field): JsonSchema {
    $schema = $this->getSchemaFactory($field)->createSchema($field, $this);
    if (NULL !== $field->getLimitValidation() && !$schema->hasKeyword('$limitValidation')) {
      $schema['$limitValidation'] = LimitValidationSchemaFactory::createSchema($field->getLimitValidation());
    }

    return $schema;
  }

  public function convertDefaultValuesInList(AbstractFormField $field, array $defaultValues): array {
    return $this->getSchemaFactory($field)->convertDefaultValuesInList($field, $defaultValues, $this);
  }

  public function supportsField(AbstractFormField $field): bool {
    foreach ($this->schemaFactories as $schemaFactory) {
      if ($schemaFactory->supportsField($field)) {
        return TRUE;
      }
    }

    return FALSE;
  }

  private function getSchemaFactory(AbstractFormField $field): FieldJsonSchemaFactoryInterface {
    foreach ($this->schemaFactories as $schemaFactory) {
      if ($schemaFactory->supportsField($field)) {
        return $schemaFactory;
      }
    }

    throw new \InvalidArgumentException(sprintf(
      'Unsupported field type "%s" (field: %s, class: %s)',
      $field->getInputType(),
      $field->getName(),
      get_class($field),
    ));
  }

}
