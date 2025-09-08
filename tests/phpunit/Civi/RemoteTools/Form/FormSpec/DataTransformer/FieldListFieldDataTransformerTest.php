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

namespace Civi\RemoteTools\Form\FormSpec\DataTransformer;

use Civi\RemoteTools\Form\FormSpec\AbstractFormField;
use Civi\RemoteTools\Form\FormSpec\Field\FieldListField;
use Civi\RemoteTools\Form\FormSpec\Field\TextField;
use Civi\RemoteTools\Form\FormSpec\FieldDataTransformerInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\RemoteTools\Form\FormSpec\DataTransformer\FieldListFieldDataTransformer
 */
final class FieldListFieldDataTransformerTest extends TestCase {

  public function test(): void {
    $itemField = (new TextField('', ''))->setDataTransformer(
      new class implements FieldDataTransformerInterface {

        public function toEntityValue(
          mixed $data,
          AbstractFormField $field,
          ?array $defaultValuesInList = NULL
        ): mixed {
          FieldListFieldDataTransformerTest::assertNull($defaultValuesInList);
          /** @var string $data */
          return $data . 'Test';
        }

      }
    );

    $field = new FieldListField('list', 'List', $itemField);
    static::assertSame(
      ['fooTest', 'barTest'],
      $field->getDataTransformer()->toEntityValue(['foo', 'bar'], $field, NULL)
    );

    static::assertNull($field->getDataTransformer()->toEntityValue(NULL, $field, NULL));

  }

  public function testWithDefaultValue(): void {
    $itemField = (new TextField('', ''))->setDataTransformer(
      new class implements FieldDataTransformerInterface {

        public function toEntityValue(
          mixed $data,
          AbstractFormField $field,
          ?array $defaultValuesInList = NULL
        ): mixed {
          FieldListFieldDataTransformerTest::assertSame(['foo', 'bar'], $defaultValuesInList);
          /** @var string $data */
          return $data . 'Test';
        }

      }
    );

    $field = (new FieldListField('list', 'List', $itemField))->setDefaultValue(['foo', 'bar']);
    static::assertSame(
      ['bazTest'],
      $field->getDataTransformer()->toEntityValue(['baz'], $field, NULL)
    );

  }

  public function testWithDefaultValuesInList(): void {
    $itemField = (new TextField('', ''))->setDataTransformer(
      new class implements FieldDataTransformerInterface {

        public function toEntityValue(
          mixed $data,
          AbstractFormField $field,
          ?array $defaultValuesInList = NULL
        ): mixed {
          // Values should be merged together.
          FieldListFieldDataTransformerTest::assertSame($defaultValuesInList, ['foo', 'bar']);
          /** @var string $data */
          return $data . 'Test';
        }

      }
    );

    $field = new FieldListField('list', 'List', $itemField);
    $defaultValuesInList = [['foo'], ['bar']];
    static::assertSame(
      ['fooTest', 'bazTest'],
      $field->getDataTransformer()->toEntityValue(['foo', 'baz'], $field, $defaultValuesInList)
    );

  }

}
