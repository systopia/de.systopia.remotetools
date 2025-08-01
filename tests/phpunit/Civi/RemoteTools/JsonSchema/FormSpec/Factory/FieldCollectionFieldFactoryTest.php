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

use Civi\RemoteTools\Form\FormSpec\Field\FieldCollectionField;
use Civi\RemoteTools\Form\FormSpec\Field\IntegerField;
use Civi\RemoteTools\JsonSchema\FormSpec\RootFieldJsonSchemaFactoryInterface;
use Civi\RemoteTools\JsonSchema\JsonSchemaInteger;
use Civi\RemoteTools\JsonSchema\JsonSchemaObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\RemoteTools\JsonSchema\FormSpec\Factory\FieldCollectionFieldFactory
 */
final class FieldCollectionFieldFactoryTest extends TestCase {

  private FieldCollectionFieldFactory $factory;

  protected function setUp(): void {
    parent::setUp();
    $this->factory = new FieldCollectionFieldFactory();
  }

  public function testCreateSchema(): void {
    $integerField = new IntegerField('integer', 'Integer');
    $field = (new FieldCollectionField('test', 'Test', [$integerField]))
      ->setNullable(TRUE);
    $integerSchema = new JsonSchemaObject([]);

    $rootFactory = $this->createMock(RootFieldJsonSchemaFactoryInterface::class);
    $rootFactory->expects(static::once())->method('createSchema')
      ->with($integerField)
      ->willReturn($integerSchema);

    static::assertEquals(
      new JsonSchemaObject(['integer' => $integerSchema], ['additionalProperties' => FALSE], TRUE),
      $this->factory->createSchema($field, $rootFactory)
    );
  }

  public function testCreateSchemaExtended(): void {
    $integerField = (new IntegerField('integer', 'Integer'))->setRequired(TRUE);
    $field = (new FieldCollectionField('test', 'Test', []))
      ->addField($integerField)
      ->setDefaultValue(['integer' => 123])
      ->setReadOnly(TRUE);
    $integerSchema = new JsonSchemaInteger([]);

    $rootFactory = $this->createMock(RootFieldJsonSchemaFactoryInterface::class);
    $rootFactory->expects(static::once())->method('createSchema')
      ->willReturnCallback(function (IntegerField $field) use ($integerField, $integerSchema) {
        static::assertSame($integerField, $field);
        // Read only and default value are inherited.
        static::assertTrue($field->isReadOnly());
        static::assertSame(123, $field->getDefaultValue());

        return $integerSchema;
      });

    static::assertEquals(
      new JsonSchemaObject(['integer' => $integerSchema], [
        'additionalProperties' => FALSE,
        'readOnly' => TRUE,
        'required' => ['integer'],
      ]),
      $this->factory->createSchema($field, $rootFactory)
    );
  }

  public function testSupportsElement(): void {
    static::assertTrue($this->factory->supportsField(new FieldCollectionField('test', 'Test')));
    static::assertFalse($this->factory->supportsField(new IntegerField('test', 'Test')));
  }

}
