<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
 *
 *  This program is free software: you can redistribute it and/or modify it under
 *  the terms of the GNU Affero General Public License as published by the Free
 *  Software Foundation, either version 3 of the License, or (at your option) any
 *  later version.
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
use Civi\RemoteTools\Form\FormSpec\Field\CalculateField;
use Civi\RemoteTools\JsonSchema\FormSpec\RootFieldJsonSchemaFactoryInterface;
use Civi\RemoteTools\JsonSchema\JsonSchema;
use Civi\RemoteTools\JsonSchema\JsonSchemaCalculate;
use Civi\RemoteTools\JsonSchema\JsonSchemaDataPointer;
use Webmozart\Assert\Assert;

final class CalculateFieldFactory extends AbstractFieldJsonSchemaFactory {

  public static function getPriority(): int {
    return 10;
  }

  protected function doCreateSchema(
    AbstractFormField $field,
    RootFieldJsonSchemaFactoryInterface $factory
  ): JsonSchema {
    assert($field instanceof CalculateField);

    $variables = [];
    $expression = preg_replace_callback(
      '/\{([^}]+)\}/',
      function (array $matches) use (&$variables) {
        $variables[$matches[1]] = new JsonSchemaDataPointer('1/' . $matches[1]);

        return $matches[1];
      },
      $field->getExpression()
    );
    if (NULL === $expression) {
      throw new \RuntimeException(preg_last_error_msg());
    }

    Assert::nullOrScalar($field->getDefaultValue());
    return new JsonSchemaCalculate($field->getDataType(), $expression, $variables, $field->getDefaultValue());
  }

  public function supportsField(AbstractFormField $field): bool {
    return $field instanceof CalculateField;
  }

}
