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
use Civi\RemoteTools\JsonSchema\JsonSchema;
use Civi\RemoteTools\JsonSchema\JsonSchemaBoolean;

final class BooleanFieldFactory extends AbstractFieldJsonSchemaFactory {

  public function createSchema(AbstractFormField $field): JsonSchema {
    $keywords = [];
    if ($field->hasDefaultValue()) {
      $keywords['default'] = $field->getDefaultValue();
    }

    return new JsonSchemaBoolean($keywords, $field->isNullable());
  }

  public function supportsField(AbstractFormField $field): bool {
    return 'boolean' === $field->getDataType();
  }

}
