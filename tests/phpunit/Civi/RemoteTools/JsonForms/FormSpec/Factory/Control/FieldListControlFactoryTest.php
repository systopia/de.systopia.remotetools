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
use Civi\RemoteTools\Form\FormSpec\Field\FieldListField;
use Civi\RemoteTools\Form\FormSpec\Field\IntegerField;
use Civi\RemoteTools\JsonForms\Control\JsonFormsArray;
use Civi\RemoteTools\JsonForms\FormSpec\ElementUiSchemaFactoryInterface;
use Civi\RemoteTools\JsonForms\JsonFormsControl;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsGroup;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\RemoteTools\JsonForms\FormSpec\Factory\Control\FieldListControlFactory
 */
final class FieldListControlFactoryTest extends TestCase {

  private FieldListControlFactory $factory;

  protected function setUp(): void {
    parent::setUp();
    $this->factory = new FieldListControlFactory();
  }

  public function testCreateSchema(): void {
    $itemField = new IntegerField('abc', 'Integer');
    $field = new FieldListField('test', 'Test', $itemField);
    $itemFieldControl = new JsonFormsControl('#/', 'Integer');

    $uiSchemaFactory = $this->createMock(ElementUiSchemaFactoryInterface::class);
    $uiSchemaFactory->expects(static::once())->method('createSchema')
      ->with($itemField, '#')
      ->willReturn($itemFieldControl);

    static::assertEquals(
      new JsonFormsArray('#/properties/test', 'Test', '', [$itemFieldControl], ['itemLayout' => 'TableRow']),
      $this->factory->createSchema($field, '#/properties', $uiSchemaFactory)
    );

    static::assertSame('', $itemField->getName());
  }

  public function testCreateSchemaWithOptions(): void {
    $itemField = new IntegerField('', 'Integer');
    $field = (new FieldListField('test', 'Test', $itemField))
      ->setDescription('Description')
      ->setItemLayout('VerticalLayout')
      ->setAddButtonLabel('Add')
      ->setRemoveButtonLabel('Remove');
    $itemFieldControl = new JsonFormsControl('#/', 'Integer');

    $uiSchemaFactory = $this->createMock(ElementUiSchemaFactoryInterface::class);
    $uiSchemaFactory->expects(static::once())->method('createSchema')
      ->with($itemField, '#')
      ->willReturn($itemFieldControl);

    static::assertEquals(
      new JsonFormsArray(
        '#/properties/test',
        'Test',
        'Description',
        [$itemFieldControl],
        [
          'itemLayout' => 'VerticalLayout',
          'addButtonLabel' => 'Add',
          'removeButtonLabel' => 'Remove',
        ]
      ),
      $this->factory->createSchema($field, '#/properties', $uiSchemaFactory)
    );
  }

  public function testCreateWithFieldCollectionField(): void {
    $itemField = new FieldCollectionField('', 'Collection');
    $field = new FieldListField('test', 'Test', $itemField);
    $control = new JsonFormsControl('#/properties/abc', 'Abc');
    $itemFieldElement = new JsonFormsGroup('Collection', [$control]);

    $uiSchemaFactory = $this->createMock(ElementUiSchemaFactoryInterface::class);
    $uiSchemaFactory->expects(static::once())->method('createSchema')
      ->with($itemField, '#')
      ->willReturn($itemFieldElement);

    // With "TableRow" the elements of the group are used as element of the array control.
    static::assertEquals(
      new JsonFormsArray('#/properties/test', 'Test', '', [$control], ['itemLayout' => 'TableRow']),
      $this->factory->createSchema($field, '#/properties', $uiSchemaFactory)
    );
  }

  public function testCreateWithVerticalLayoutAndFieldCollectionField(): void {
    $itemField = new FieldCollectionField('', 'Collection');
    $field = (new FieldListField('test', 'Test', $itemField))
      ->setItemLayout(FieldListField::LAYOUT_VERTICAL);
    $control = new JsonFormsControl('#/properties/abc', 'Abc');
    $itemFieldElement = new JsonFormsGroup('Collection', [$control]);

    $uiSchemaFactory = $this->createMock(ElementUiSchemaFactoryInterface::class);
    $uiSchemaFactory->expects(static::once())->method('createSchema')
      ->with($itemField, '#')
      ->willReturn($itemFieldElement);

    // With "VerticalLayout" the group itself is used as element of the array control, if it has label or description.
    static::assertEquals(
      new JsonFormsArray('#/properties/test', 'Test', '', [$itemFieldElement], ['itemLayout' => 'VerticalLayout']),
      $this->factory->createSchema($field, '#/properties', $uiSchemaFactory)
    );
  }

  public function testSupportsElement(): void {
    $integerField = new IntegerField('test', 'Test');
    static::assertTrue($this->factory->supportsElement(new FieldListField('test', 'Test', $integerField)));
    static::assertFalse($this->factory->supportsElement($integerField));
  }

}
