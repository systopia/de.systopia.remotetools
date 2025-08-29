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

use Civi\RemoteTools\Form\FormSpec\Field\CalculateField;
use Civi\RemoteTools\Form\FormSpec\Field\IntegerField;
use Civi\RemoteTools\JsonSchema\FormSpec\RootFieldJsonSchemaFactoryInterface;
use Civi\RemoteTools\JsonSchema\JsonSchemaCalculate;
use Civi\RemoteTools\JsonSchema\JsonSchemaDataPointer;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\RemoteTools\JsonSchema\FormSpec\Factory\CalculateFieldFactory
 */
final class CalculateFieldFactoryTest extends TestCase {

  /**
   * @var \Civi\RemoteTools\JsonSchema\FormSpec\Factory\CalculateFieldFactory
   */
  private CalculateFieldFactory $factory;

  protected function setUp(): void {
    parent::setUp();
    $this->factory = new CalculateFieldFactory();
  }

  public function testCreateSchema(): void {
    $field = (new CalculateField('test', 'Test', '{fieldA} * {fieldB} + 2', 'integer'))
      ->setDefaultValue(12);

    $jsonSchemaFactory = $this->createMock(RootFieldJsonSchemaFactoryInterface::class);
    static::assertEquals(
      new JsonSchemaCalculate(
        'integer',
        'fieldA * fieldB + 2',
        [
          'fieldA' => new JsonSchemaDataPointer('1/fieldA'),
          'fieldB' => new JsonSchemaDataPointer('1/fieldB'),
        ],
        12
      ),
      $this->factory->createSchema($field, $jsonSchemaFactory)
    );
  }

  public function testSupportsField(): void {
    static::assertTrue($this->factory->supportsField(new CalculateField('test', 'Test', '1+2')));
    static::assertFalse($this->factory->supportsField(new IntegerField('test', 'Test')));
  }

}
