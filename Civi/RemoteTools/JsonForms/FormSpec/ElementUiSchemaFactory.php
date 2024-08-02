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

use Civi\RemoteTools\Form\FormSpec\FormElementInterface;
use Civi\RemoteTools\JsonForms\JsonFormsElement;

final class ElementUiSchemaFactory implements ElementUiSchemaFactoryInterface {

  /**
   * @phpstan-var iterable<ConcreteElementUiSchemaFactoryInterface>
   */
  private iterable $factories;

  /**
   * @phpstan-param iterable<ConcreteElementUiSchemaFactoryInterface> $factories
   */
  public function __construct(iterable $factories) {
    $this->factories = $factories;
  }

  public function createSchema(FormElementInterface $element): JsonFormsElement {
    foreach ($this->factories as $factory) {
      if ($factory->supportsElement($element)) {
        return $factory->createSchema($element, $this);
      }
    }

    throw new \InvalidArgumentException(sprintf(
      'Form element of type "%s" is not supported (class: %s)',
      $element->getType(),
      get_class($element),
    ));
  }

}
