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
use Civi\RemoteTools\Form\FormSpec\Field\ArrayField;
use Civi\RemoteTools\JsonForms\Control\JsonFormsArray;
use Civi\RemoteTools\JsonForms\FormSpec\ElementUiSchemaFactoryInterface;
use Civi\RemoteTools\JsonForms\JsonFormsControl;
use Civi\RemoteTools\JsonForms\JsonFormsElement;
use Webmozart\Assert\Assert;

final class ArrayControlFactory extends AbstractControlFactory {

  protected function createInputSchema(
    AbstractFormInput $input,
    ElementUiSchemaFactoryInterface $factory
  ): JsonFormsElement {
    Assert::isInstanceOf($input, ArrayField::class);

    $elements = [];
    foreach ($input->getFields() as $itemField) {
      $elements[] = $factory->createSchema($itemField);
    }
    Assert::allIsInstanceOf($elements, JsonFormsControl::class);

    $options = ['itemLayout' => $input->getItemLayout()];
    if (NULL !== $input->getAddButtonLabel()) {
      $options['addButtonLabel'] = $input->getAddButtonLabel();
    }
    if (NULL !== $input->getRemoveButtonLabel()) {
      $options['removeButtonLabel'] = $input->getRemoveButtonLabel();
    }

    return new JsonFormsArray(
      $this->getScope($input),
      $input->getLabel(),
      $input->getDescription(),
      $elements,
      $options
    );
  }

  protected function supportsInput(AbstractFormInput $input): bool {
    return $input instanceof ArrayField;
  }

}
