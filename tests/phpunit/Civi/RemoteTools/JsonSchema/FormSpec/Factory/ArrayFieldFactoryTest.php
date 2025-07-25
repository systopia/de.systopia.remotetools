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

namespace Civi\RemoteTools\JsonSchema\FormSpec\Factory;

use Civi\RemoteTools\Form\FormSpec\Field\ArrayField;
use Civi\RemoteTools\Form\FormSpec\Field\IntegerField;
use Civi\RemoteTools\Form\FormSpec\FormSpec;
use Civi\RemoteTools\JsonSchema\FormSpec\JsonSchemaFactoryInterface;
use Civi\RemoteTools\JsonSchema\JsonSchema;
use Civi\RemoteTools\JsonSchema\JsonSchemaArray;
use Civi\RemoteTools\JsonSchema\JsonSchemaObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\RemoteTools\JsonSchema\FormSpec\Factory\ArrayFieldFactory
 */
final class ArrayFieldFactoryTest extends TestCase {

  private ArrayFieldFactory $factory;

  protected function setUp(): void {
    parent::setUp();
    $this->factory = new ArrayFieldFactory();
  }

  public function testCreateSchema(): void {
    $itemField = new IntegerField('integer', 'Integer');
    $field = (new ArrayField('test', 'Test', [$itemField]))
      ->setNullable(TRUE);
    $itemSchema = new JsonSchemaObject([]);

    $jsonSchemaFactory = $this->createMock(JsonSchemaFactoryInterface::class);
    $jsonSchemaFactory->expects(static::once())->method('createJsonSchema')
      ->with(new FormSpec('', [$itemField]))
      ->willReturn($itemSchema);

    static::assertEquals(
      new JsonSchemaArray($itemSchema, [], TRUE),
      $this->factory->createSchema($field, $jsonSchemaFactory)
    );
  }

  public function testCreateSchemaExtended(): void {
    $itemField = new IntegerField('integer', 'Integer');
    $field = (new ArrayField('test', 'Test', []))
      ->addField($itemField)
      ->setDefaultValue([['integer' => 123]])
      ->setReadOnly(TRUE)
      ->setMinItems(1)
      ->setMaxItems(3);
    $itemSchema = new JsonSchemaObject([]);

    $jsonSchemaFactory = $this->createMock(JsonSchemaFactoryInterface::class);
    $jsonSchemaFactory->expects(static::once())->method('createJsonSchema')
      ->with(new FormSpec('', [$itemField]))
      ->willReturn($itemSchema);

    static::assertEquals(
      new JsonSchemaArray($itemSchema, [
        'default' => JsonSchema::convertToJsonSchemaArray([['integer' => 123]]),
        'const' => JsonSchema::convertToJsonSchemaArray([['integer' => 123]]),
        'readOnly' => TRUE,
        'minItems' => 1,
        'maxItems' => 3,
      ]),
      $this->factory->createSchema($field, $jsonSchemaFactory)
    );
  }

  public function testSupportsElement(): void {
    static::assertTrue($this->factory->supportsField(new ArrayField('test', 'Test', [])));
    static::assertFalse($this->factory->supportsField(new IntegerField('test', 'Test')));
  }

}
