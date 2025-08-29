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

namespace Civi\RemoteTools\JsonForms\FormSpec\Factory\Control;

use Civi\RemoteTools\Form\FormSpec\Field\FieldCollectionField;
use Civi\RemoteTools\Form\FormSpec\Field\IntegerField;
use Civi\RemoteTools\JsonForms\FormSpec\ElementUiSchemaFactoryInterface;
use Civi\RemoteTools\JsonForms\JsonFormsControl;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsGroup;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\RemoteTools\JsonForms\FormSpec\Factory\Control\FieldCollectionControlFactory
 */
final class FieldCollectionControlFactoryTest extends TestCase {

  private FieldCollectionControlFactory $factory;

  protected function setUp(): void {
    parent::setUp();
    $this->factory = new FieldCollectionControlFactory();
  }

  public function testCreateSchema(): void {
    $integerField = new IntegerField('integer', 'Integer');
    $field = (new FieldCollectionField('test', 'Test', [$integerField]))
      ->setDescription('Description');
    $integerFieldControl = new JsonFormsControl('#/properties/test/properties/integer', 'Integer');

    $uiSchemaFactory = $this->createMock(ElementUiSchemaFactoryInterface::class);
    $uiSchemaFactory->expects(static::once())->method('createSchema')
      ->with($integerField, '#/properties/test/properties')
      ->willReturn($integerFieldControl);

    static::assertEquals(
      new JsonFormsGroup('Test', [$integerFieldControl], 'Description'),
      $this->factory->createSchema($field, '#/properties', $uiSchemaFactory)
    );
  }

  public function testCreateSchemaWithoutName(): void {
    $integerField = new IntegerField('integer', 'Integer');
    $field = new FieldCollectionField('', 'Test', [$integerField]);
    $integerFieldControl = new JsonFormsControl('#/properties/integer', 'Integer');

    $uiSchemaFactory = $this->createMock(ElementUiSchemaFactoryInterface::class);
    $uiSchemaFactory->expects(static::once())->method('createSchema')
      ->with($integerField, '#/properties')
      ->willReturn($integerFieldControl);

    static::assertEquals(
      new JsonFormsGroup('Test', [$integerFieldControl], ''),
      $this->factory->createSchema($field, '#', $uiSchemaFactory)
    );
  }

  public function testSupportsElement(): void {
    static::assertTrue($this->factory->supportsElement(new FieldCollectionField('test', 'Test')));
    static::assertFalse($this->factory->supportsElement(new IntegerField('test', 'Test')));
  }

}
