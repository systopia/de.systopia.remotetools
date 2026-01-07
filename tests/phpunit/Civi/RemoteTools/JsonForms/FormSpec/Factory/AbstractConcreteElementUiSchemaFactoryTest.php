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

namespace Civi\RemoteTools\JsonForms\FormSpec\Factory;

use Civi\RemoteTools\Form\FormSpec\Field\IntegerField;
use Civi\RemoteTools\Form\FormSpec\FormElementInterface;
use Civi\RemoteTools\Form\FormSpec\Rule\FormRule;
use Civi\RemoteTools\JsonForms\FormSpec\ElementUiSchemaFactoryInterface;
use Civi\RemoteTools\JsonForms\JsonFormsControl;
use Civi\RemoteTools\JsonForms\JsonFormsElement;
use Civi\RemoteTools\JsonForms\JsonFormsRule;
use Civi\RemoteTools\JsonSchema\JsonSchema;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\RemoteTools\JsonForms\FormSpec\Factory\AbstractConcreteElementUiSchemaFactory
 */
final class AbstractConcreteElementUiSchemaFactoryTest extends TestCase {

  private AbstractConcreteElementUiSchemaFactory $concreteElementUiSchemaFactory;

  protected function setUp(): void {
    parent::setUp();
    $this->concreteElementUiSchemaFactory = new class extends AbstractConcreteElementUiSchemaFactory {

      protected function doCreateSchema(
        FormElementInterface $element,
        string $scopePrefix,
        ElementUiSchemaFactoryInterface $factory
      ): JsonFormsElement {
        return new JsonFormsControl("$scopePrefix/test", 'Test');
      }

      public function supportsElement(FormElementInterface $element): bool {
        return TRUE;
      }

    };
  }

  public function testCreateSchema(): void {
    $factory = $this->createMock(ElementUiSchemaFactoryInterface::class);
    $element = new IntegerField('name', 'label');
    $element->setCssClasses(['some-class', 'another-class']);
    $element->setRule(new FormRule('SHOW', ['foo' => ['=', 'bar']]));
    $schema = $this->concreteElementUiSchemaFactory->createSchema($element, '#/prefix', $factory);

    static::assertInstanceOf(JsonFormsControl::class, $schema);
    static::assertSame('#/prefix/test', $schema->getScope());
    static::assertInstanceOf(JsonSchema::class, $schema['options']);
    static::assertSame(['some-class', 'another-class'], $schema['options']['cssClasses']);
    static::assertInstanceOf(JsonFormsRule::class, $schema['rule']);
  }

  public function testGetPriority(): void {
    static::assertSame(0, $this->concreteElementUiSchemaFactory::getPriority());
  }

}
