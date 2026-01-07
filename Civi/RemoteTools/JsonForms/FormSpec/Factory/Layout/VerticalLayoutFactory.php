<?php
/*
 * Copyright (C) 2026 SYSTOPIA GmbH
 *
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types = 1);

namespace Civi\RemoteTools\JsonForms\FormSpec\Factory\Layout;

use Civi\RemoteTools\Form\FormSpec\FormElementContainer;
use Civi\RemoteTools\Form\FormSpec\FormElementInterface;
use Civi\RemoteTools\JsonForms\FormSpec\ElementUiSchemaFactoryInterface;
use Civi\RemoteTools\JsonForms\FormSpec\Factory\AbstractConcreteElementUiSchemaFactory;
use Civi\RemoteTools\JsonForms\JsonFormsElement;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsVerticalLayout;
use Webmozart\Assert\Assert;

final class VerticalLayoutFactory extends AbstractConcreteElementUiSchemaFactory {

  public static function getPriority(): int {
    return GroupFactory::getPriority() + 1;
  }

  public function supportsElement(FormElementInterface $element): bool {
    return $element instanceof FormElementContainer
      && NULL === $element->getTitle()
      && NULL === $element->getDescription()
      && FALSE === $element->isCollapsible();
  }

  protected function doCreateSchema(
    FormElementInterface $element,
    string $scopePrefix,
    ElementUiSchemaFactoryInterface $factory
  ): JsonFormsElement {
    Assert::isInstanceOf($element, FormElementContainer::class);
    $elements = array_map(
      fn ($childElement) => $factory->createSchema($childElement, $scopePrefix),
      $element->getElements()
    );

    return new JsonFormsVerticalLayout($elements);
  }

}
