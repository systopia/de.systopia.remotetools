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

use Civi\RemoteTools\Form\FormSpec\Field\FieldListField;
use Civi\RemoteTools\Form\FormSpec\Field\IntegerField;
use Civi\RemoteTools\JsonSchema\FormSpec\RootFieldJsonSchemaFactoryInterface;
use Civi\RemoteTools\JsonSchema\JsonSchema;
use Civi\RemoteTools\JsonSchema\JsonSchemaArray;
use Civi\RemoteTools\JsonSchema\JsonSchemaInteger;
use Civi\RemoteTools\JsonSchema\JsonSchemaObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\RemoteTools\JsonSchema\FormSpec\Factory\FieldListFieldFactory
 */
final class FieldListFieldFactoryTest extends TestCase {

  private FieldListFieldFactory $factory;

  protected function setUp(): void {
    parent::setUp();
    $this->factory = new FieldListFieldFactory();
  }

  public function testCreateSchema(): void {
    $itemField = new IntegerField('', 'Integer');
    $field = (new FieldListField('test', 'Test', $itemField))
      ->setNullable(TRUE);
    $itemSchema = new JsonSchemaInteger([]);

    $rootFactory = $this->createMock(RootFieldJsonSchemaFactoryInterface::class);
    $rootFactory->expects(static::once())->method('createSchema')
      ->with($itemField)
      ->willReturn($itemSchema);

    static::assertEquals(
      new JsonSchemaArray($itemSchema, ['uniqueItems' => FALSE], TRUE),
      $this->factory->createSchema($field, $rootFactory)
    );
  }

  public function testCreateSchemaExtended(): void {
    $itemField = new IntegerField('', 'Integer');
    $field = (new FieldListField('test', 'Test', $itemField))
      ->setDefaultValue([123])
      ->setReadOnly(TRUE)
      ->setMinItems(1)
      ->setMaxItems(3)
      ->setUniqueItems(TRUE);
    $itemSchema = new JsonSchemaObject([]);

    $rootFactory = $this->createMock(RootFieldJsonSchemaFactoryInterface::class);
    $rootFactory->expects(static::once())->method('createSchema')
      ->willReturnCallback(function (IntegerField $field) use ($itemField, $itemSchema) {
        static::assertSame($itemField, $field);
        // Read only is inherited.
        static::assertTrue($field->isReadOnly());

        return $itemSchema;
      })
      ->willReturn($itemSchema);

    $rootFactory->expects(static::once())->method('convertDefaultValuesInList')
      ->with($itemField, [123])
      ->willReturn([123]);

    static::assertEquals(
      new JsonSchemaArray($itemSchema, [
        'default' => JsonSchema::convertToJsonSchemaArray([123]),
        'readOnly' => TRUE,
        'minItems' => 1,
        'maxItems' => 3,
        'uniqueItems' => TRUE,
      ]),
      $this->factory->createSchema($field, $rootFactory)
    );
  }

  public function testSupportsElement(): void {
    $integerField = new IntegerField('test', 'Test');
    static::assertTrue($this->factory->supportsField(new FieldListField('test', 'Test', $integerField)));
    static::assertFalse($this->factory->supportsField($integerField));
  }

}
