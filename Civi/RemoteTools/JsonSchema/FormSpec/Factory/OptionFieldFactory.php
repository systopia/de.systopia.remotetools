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
use Civi\RemoteTools\Form\FormSpec\Field\AbstractOptionField;
use Civi\RemoteTools\JsonSchema\JsonSchema;
use Civi\RemoteTools\JsonSchema\Util\JsonSchemaUtil;
use Webmozart\Assert\Assert;

final class OptionFieldFactory extends AbstractFieldJsonSchemaFactory {

  public function createSchema(AbstractFormField $field): JsonSchema {
    Assert::isInstanceOf($field, AbstractOptionField::class);
    /** @var \Civi\RemoteTools\Form\FormSpec\Field\AbstractOptionField $field */
    $keywords = [
      'type' => ['string', 'integer'],
      'oneOf' => JsonSchemaUtil::buildTitledOneOf($field->getOptions()),
    ];
    if ($field->isNullable()) {
      $keywords['type'][] = 'null';
      $keywords['oneOf'][] = JsonSchema::fromArray(['const' => NULL]);
    }
    if ($field->hasDefaultValue()) {
      $keywords['default'] = $field->getDefaultValue();
    }

    return new JsonSchema($keywords);
  }

  public function supportsField(AbstractFormField $field): bool {
    return $field instanceof AbstractOptionField;
  }

}
