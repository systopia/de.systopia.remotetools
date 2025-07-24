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

use Civi\RemoteTools\Form\FormSpec\Field\ArrayField;
use Civi\RemoteTools\Form\FormSpec\Field\IntegerField;
use Civi\RemoteTools\JsonForms\Control\JsonFormsArray;
use Civi\RemoteTools\JsonForms\FormSpec\ElementUiSchemaFactoryInterface;
use Civi\RemoteTools\JsonForms\JsonFormsControl;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\RemoteTools\JsonForms\FormSpec\Factory\Control\ArrayControlFactory
 */
final class ArrayControlFactoryTest extends TestCase {

  private ArrayControlFactory $factory;

  protected function setUp(): void {
    parent::setUp();
    $this->factory = new ArrayControlFactory();
  }

  public function testCreateSchema(): void {
    $itemField = new IntegerField('integer', 'Integer');
    $field = new ArrayField('test', 'Test', [$itemField]);
    $itemFieldControl = new JsonFormsControl('#/properties/integer', 'Integer');

    $uiSchemaFactory = $this->createMock(ElementUiSchemaFactoryInterface::class);
    $uiSchemaFactory->expects(static::once())->method('createSchema')
      ->with($itemField)
      ->willReturn($itemFieldControl);

    static::assertEquals(
      new JsonFormsArray('#/properties/test', 'Test', '', [$itemFieldControl], ['itemLayout' => 'TableRow']),
      $this->factory->createSchema($field, $uiSchemaFactory)
    );
  }

  public function testCreateSchemaWithOptions(): void {
    $itemField = new IntegerField('integer', 'Integer');
    $field = (new ArrayField('test', 'Test', []))
      ->insertField($itemField, 0)
      ->setDescription('Description')
      ->setItemLayout('VerticalLayout')
      ->setAddButtonLabel('Add')
      ->setRemoveButtonLabel('Remove');
    $itemFieldControl = new JsonFormsControl('#/properties/integer', 'Integer');

    $uiSchemaFactory = $this->createMock(ElementUiSchemaFactoryInterface::class);
    $uiSchemaFactory->expects(static::once())->method('createSchema')
      ->with($itemField)
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
      $this->factory->createSchema($field, $uiSchemaFactory)
    );
  }

  public function testSupportsElement(): void {
    static::assertTrue($this->factory->supportsElement(new ArrayField('test', 'Test', [])));
    static::assertFalse($this->factory->supportsElement(new IntegerField('test', 'Test')));
  }

}
