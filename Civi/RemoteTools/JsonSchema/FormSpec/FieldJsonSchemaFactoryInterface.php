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

interface FieldJsonSchemaFactoryInterface {

  public const SERVICE_TAG = 'remote_tools.json_schema.form_spec.field_factory';

  public static function getPriority(): int;

  public function createSchema(AbstractFormField $field, RootFieldJsonSchemaFactoryInterface $factory): JsonSchema;

  /**
   * @param list<mixed> $defaultValues
   *
   * @return list<mixed>
   */
  public function convertDefaultValuesInList(
    AbstractFormField $field,
    array $defaultValues,
    RootFieldJsonSchemaFactoryInterface $factory
  ): array;

  public function supportsField(AbstractFormField $field): bool;

}
