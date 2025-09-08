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

namespace Civi\RemoteTools\JsonSchema\FormSpec\Factory;

use Civi\RemoteTools\Form\FormSpec\AbstractFormField;
use Civi\RemoteTools\JsonSchema\FormSpec\RootFieldJsonSchemaFactoryInterface;
use Civi\RemoteTools\JsonSchema\JsonSchema;
use Civi\RemoteTools\JsonSchema\JsonSchemaString;
use Webmozart\Assert\Assert;

final class DateTimeFieldFactory extends AbstractFieldJsonSchemaFactory {

  protected function doCreateSchema(
    AbstractFormField $field,
    RootFieldJsonSchemaFactoryInterface $factory
  ): JsonSchema {
    $keywords = ['format' => 'date-time'];
    Assert::nullOrString($field->getDefaultValue());
    if ($field->hasDefaultValue()) {
      $keywords['default'] = $field->getDefaultValue();
      if ($field->isReadOnly()) {
        $keywords['const'] = $keywords['default'];
      }
    }
    if ($field->isReadOnly()) {
      $keywords['readOnly'] = TRUE;
    }

    return new JsonSchemaString($keywords, $field->isNullable());
  }

  public function supportsField(AbstractFormField $field): bool {
    return 'string' === $field->getDataType() && 'dateTime' === $field->getInputType();
  }

}
