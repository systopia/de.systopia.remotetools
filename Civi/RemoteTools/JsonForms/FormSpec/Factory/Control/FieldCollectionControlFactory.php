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

namespace Civi\RemoteTools\JsonForms\FormSpec\Factory\Control;

use Civi\RemoteTools\Form\FormSpec\AbstractFormInput;
use Civi\RemoteTools\Form\FormSpec\Field\FieldCollectionField;
use Civi\RemoteTools\JsonForms\FormSpec\ElementUiSchemaFactoryInterface;
use Civi\RemoteTools\JsonForms\JsonFormsControl;
use Civi\RemoteTools\JsonForms\JsonFormsElement;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsGroup;
use Webmozart\Assert\Assert;

final class FieldCollectionControlFactory extends AbstractControlFactory {

  protected function createInputSchema(
    AbstractFormInput $input,
    string $scopePrefix,
    ElementUiSchemaFactoryInterface $factory
  ): JsonFormsElement {
    Assert::isInstanceOf($input, FieldCollectionField::class);

    $scopePrefix = rtrim($this->getScope($input, $scopePrefix), '/') . '/properties';
    $elements = [];
    foreach ($input->getFields() as $itemField) {
      $elements[] = $factory->createSchema($itemField, $scopePrefix);
    }
    Assert::allIsInstanceOf($elements, JsonFormsControl::class);

    return new JsonFormsGroup($input->getLabel(), $elements, $input->getDescription());
  }

  protected function supportsInput(AbstractFormInput $input): bool {
    return $input instanceof FieldCollectionField;
  }

}
