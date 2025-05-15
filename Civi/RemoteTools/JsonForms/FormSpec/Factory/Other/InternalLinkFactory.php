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

namespace Civi\RemoteTools\JsonForms\FormSpec\Factory\Other;

use Civi\RemoteTools\Form\FormSpec\Other\InternalLinkElement;
use Civi\RemoteTools\Form\FormSpec\FormElementInterface;
use Civi\RemoteTools\JsonForms\FormSpec\ElementUiSchemaFactoryInterface;
use Civi\RemoteTools\JsonForms\FormSpec\Factory\AbstractConcreteElementUiSchemaFactory;
use Civi\RemoteTools\JsonForms\JsonFormsElement;
use Webmozart\Assert\Assert;

/**
 * Creates a custom JSON Forms element for internal links.
 */
final class InternalLinkFactory extends AbstractConcreteElementUiSchemaFactory {

  public function supportsElement(FormElementInterface $element): bool {
    return $element instanceof InternalLinkElement;
  }

  protected function doCreateSchema(
    FormElementInterface $element,
    ElementUiSchemaFactoryInterface $factory
  ): JsonFormsElement {
    Assert::isInstanceOf($element, InternalLinkElement::class);
    /** @var \Civi\RemoteTools\Form\FormSpec\Other\InternalLinkElement $element */
    return new JsonFormsElement('InternalLink', [
      'url' => $element->getUrl(),
      'label' => $element->getLabel(),
      'description' => $element->getDescription(),
      'filename' => $element->getFilename(),
    ]);
  }

}
