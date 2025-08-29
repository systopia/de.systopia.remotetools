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
use Civi\RemoteTools\Form\FormSpec\Field\FieldListField;
use Civi\RemoteTools\JsonForms\Control\JsonFormsArray;
use Civi\RemoteTools\JsonForms\FormSpec\ElementUiSchemaFactoryInterface;
use Civi\RemoteTools\JsonForms\JsonFormsElement;
use Civi\RemoteTools\JsonForms\JsonFormsLayout;
use Webmozart\Assert\Assert;

final class FieldListControlFactory extends AbstractControlFactory {

  protected function createInputSchema(
    AbstractFormInput $input,
    string $scopePrefix,
    ElementUiSchemaFactoryInterface $factory
  ): JsonFormsElement {
    Assert::isInstanceOf($input, FieldListField::class);

    $input->getItemField()->setName('');
    $element = $factory->createSchema($input->getItemField(), '#');
    if ($element instanceof JsonFormsLayout
      && ('TableRow' === $input->getItemLayout() || !$this->hasLabelOrDescription($element))
    ) {
      $elements = $element->getElements();
    }
    else {
      $elements = [$element];
    }

    $options = ['itemLayout' => $input->getItemLayout()];
    if (NULL !== $input->getAddButtonLabel()) {
      $options['addButtonLabel'] = $input->getAddButtonLabel();
    }
    if (NULL !== $input->getRemoveButtonLabel()) {
      $options['removeButtonLabel'] = $input->getRemoveButtonLabel();
    }

    return new JsonFormsArray(
      $this->getScope($input, $scopePrefix),
      $input->getLabel(),
      $input->getDescription(),
      $elements,
      $options
    );
  }

  protected function supportsInput(AbstractFormInput $input): bool {
    return $input instanceof FieldListField;
  }

  private function hasLabelOrDescription(JsonFormsLayout $layout): bool {
    return '' !== $layout->getKeywordValueOrDefault('label', '')
      || '' !== $layout->getKeywordValueOrDefault('description', '');
  }

}
