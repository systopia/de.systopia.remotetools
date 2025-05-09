<?php
/*
 * Copyright (C) 2023 SYSTOPIA GmbH
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

namespace Civi\RemoteTools\JsonSchema\FormSpec\Factory;

use Civi\RemoteTools\Form\FormSpec\AbstractFormField;
use Civi\RemoteTools\JsonSchema\FormSpec\FieldJsonSchemaFactoryInterface;
use Civi\RemoteTools\JsonSchema\FormSpec\LimitValidationSchemaFactory;
use Civi\RemoteTools\JsonSchema\JsonSchema;

abstract class AbstractFieldJsonSchemaFactory implements FieldJsonSchemaFactoryInterface {

  public static function getPriority(): int {
    return 0;
  }

  public function createSchema(AbstractFormField $field): JsonSchema {
    $schema = $this->doCreateSchema($field);

    if (NULL !== $field->getLimitValidation()) {
      $schema['$limitValidation'] = LimitValidationSchemaFactory::createSchema($field->getLimitValidation());
    }

    return $schema;
  }

  abstract protected function doCreateSchema(AbstractFormField $field): JsonSchema;

}
