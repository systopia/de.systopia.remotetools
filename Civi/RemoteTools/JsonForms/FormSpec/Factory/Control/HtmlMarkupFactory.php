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

namespace Civi\RemoteTools\JsonForms\FormSpec\Factory\Control;

use Civi\RemoteTools\Form\FormSpec\FormElementInterface;
use Civi\RemoteTools\Form\FormSpec\Markup\HtmlElement;
use Civi\RemoteTools\JsonForms\FormSpec\ElementUiSchemaFactoryInterface;
use Civi\RemoteTools\JsonForms\FormSpec\Factory\AbstractConcreteElementUiSchemaFactory;
use Civi\RemoteTools\JsonForms\JsonFormsElement;
use Civi\RemoteTools\JsonForms\JsonFormsMarkup;
use Webmozart\Assert\Assert;

final class HtmlMarkupFactory extends AbstractConcreteElementUiSchemaFactory {

  public function createSchema(
    FormElementInterface $element,
    ElementUiSchemaFactoryInterface $factory
  ): JsonFormsElement {
    Assert::isInstanceOf($element, HtmlElement::class);
    /** @var \Civi\RemoteTools\Form\FormSpec\Markup\HtmlElement $element */
    return new JsonFormsMarkup($element->getContent());
  }

  public function supportsElement(FormElementInterface $element): bool {
    return $element instanceof HtmlElement;
  }

}
