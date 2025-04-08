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
use Civi\RemoteTools\Form\FormSpec\Field\AbstractMultiOptionField;
use Civi\RemoteTools\JsonSchema\JsonSchema;
use Civi\RemoteTools\JsonSchema\JsonSchemaArray;
use Civi\RemoteTools\JsonSchema\Util\JsonSchemaUtil;
use Webmozart\Assert\Assert;

final class MultiOptionFieldFactory extends AbstractFieldJsonSchemaFactory {

  protected function doCreateSchema(AbstractFormField $field): JsonSchema {
    Assert::isInstanceOf($field, AbstractMultiOptionField::class);
    /** @var \Civi\RemoteTools\Form\FormSpec\Field\AbstractMultiOptionField $field */

    $keywords = [
      'uniqueItems' => TRUE,
    ];
    if (NULL !== $field->getMinItems()) {
      $keywords['minItems'] = $field->getMinItems();
    }
    if (NULL !== $field->getMaxItems()) {
      $keywords['maxItems'] = $field->getMaxItems();
    }
    if ($field->hasDefaultValue()) {
      $keywords['default'] = $field->getDefaultValue();
    }
    if ($field->isReadOnly()) {
      $keywords['readOnly'] = TRUE;
      $keywords['const'] = $field->getDefaultValue();
    }

    Assert::notEmpty($field->getOptions(), sprintf('Options must not be empty (field: %s)', $field->getName()));
    $items = new JsonSchema([
      'type' => ['string', 'integer'],
      'oneOf' => JsonSchemaUtil::buildTitledOneOf($field->getOptions()),
    ]);

    return new JsonSchemaArray($items, $keywords, $field->isNullable());
  }

  public function supportsField(AbstractFormField $field): bool {
    return $field instanceof AbstractMultiOptionField;
  }

}
