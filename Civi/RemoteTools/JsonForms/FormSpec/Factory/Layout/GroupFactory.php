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

namespace Civi\RemoteTools\JsonForms\FormSpec\Factory\Layout;

use Civi\RemoteTools\Form\FormSpec\FormElementContainer;
use Civi\RemoteTools\Form\FormSpec\FormElementInterface;
use Civi\RemoteTools\JsonForms\FormSpec\ElementUiSchemaFactoryInterface;
use Civi\RemoteTools\JsonForms\FormSpec\Factory\AbstractConcreteElementUiSchemaFactory;
use Civi\RemoteTools\JsonForms\JsonFormsElement;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsCloseableGroup;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsGroup;
use Webmozart\Assert\Assert;

final class GroupFactory extends AbstractConcreteElementUiSchemaFactory {

  public function supportsElement(FormElementInterface $element): bool {
    return $element instanceof FormElementContainer;
  }

  protected function doCreateSchema(
    FormElementInterface $element,
    ElementUiSchemaFactoryInterface $factory
  ): JsonFormsElement {
    Assert::isInstanceOf($element, FormElementContainer::class);
    /** @var \Civi\RemoteTools\Form\FormSpec\FormElementContainer $element */
    $elements = array_map([$factory, 'createSchema'], $element->getElements());

    if ($element->isCollapsible()) {
      return new JsonFormsCloseableGroup($element->getTitle(), $elements, $element->getDescription());
    }

    return new JsonFormsGroup($element->getTitle(), $elements, $element->getDescription());
  }

}
