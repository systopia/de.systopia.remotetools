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

namespace Civi\RemoteTools\JsonForms\FormSpec\Factory;

use Civi\RemoteTools\Form\FormSpec\FormElementInterface;
use Civi\RemoteTools\JsonForms\FormSpec\ConcreteElementUiSchemaFactoryInterface;
use Civi\RemoteTools\JsonForms\FormSpec\ElementUiSchemaFactoryInterface;
use Civi\RemoteTools\JsonForms\FormSpec\RuleFactory;
use Civi\RemoteTools\JsonForms\JsonFormsElement;
use Civi\RemoteTools\JsonSchema\JsonSchema;

abstract class AbstractConcreteElementUiSchemaFactory implements ConcreteElementUiSchemaFactoryInterface {

  public static function getPriority(): int {
    return 0;
  }

  final public function createSchema(
    FormElementInterface $element,
    string $scopePrefix,
    ElementUiSchemaFactoryInterface $factory
  ): JsonFormsElement {
    $jsonFormsElement = $this->doCreateSchema($element, $scopePrefix, $factory);

    if ([] !== $element->getCssClasses() && !isset($jsonFormsElement['options']['cssClasses'])) {
      /** @var \Civi\RemoteTools\JsonSchema\JsonSchema $options */
      // @phpstan-ignore voku.Coalesce
      $options = $jsonFormsElement['options'] ??= new JsonSchema([]);
      $options['cssClasses'] = $element->getCssClasses();
    }

    if (NULL !== $element->getRule()) {
      $jsonFormsElement['rule'] = RuleFactory::createJsonFormsRule($element->getRule());
    }

    return $jsonFormsElement;
  }

  abstract protected function doCreateSchema(
    FormElementInterface $element,
    string $scopePrefix,
    ElementUiSchemaFactoryInterface $factory
  ): JsonFormsElement;

}
