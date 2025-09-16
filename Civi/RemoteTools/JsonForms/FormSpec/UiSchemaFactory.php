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

namespace Civi\RemoteTools\JsonForms\FormSpec;

use Civi\RemoteTools\Form\FormSpec\FormSpec;
use Civi\RemoteTools\JsonForms\JsonFormsElement;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsGroup;

final class UiSchemaFactory implements UiSchemaFactoryInterface {

  private ElementUiSchemaFactoryInterface $factory;

  public function __construct(ElementUiSchemaFactoryInterface $factory) {
    $this->factory = $factory;
  }

  public function createUiSchema(FormSpec $formSpec): JsonFormsElement {
    $elements = array_map(
      fn ($element) => $this->factory->createSchema($element, '#/properties'),
      $formSpec->getElements()
    );

    return new JsonFormsGroup($formSpec->getTitle(), $elements, NULL, ['submitMethod' => $formSpec->getSubmitMethod()]);
  }

}
