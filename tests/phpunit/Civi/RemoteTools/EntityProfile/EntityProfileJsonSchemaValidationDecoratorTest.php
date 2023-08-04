<?php
/*
 * Copyright (C) 2023 SYSTOPIA GmbH
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

namespace Civi\RemoteTools\EntityProfile;

use Civi\RemoteTools\Form\FormSpec\FormSpec;
use Civi\RemoteTools\Form\FormSpec\ValidatorInterface;
use Civi\RemoteTools\JsonSchema\FormSpec\JsonSchemaFactoryInterface;
use Civi\RemoteTools\JsonSchema\FormSpec\JsonSchemaFormSpecValidator;
use Civi\RemoteTools\JsonSchema\Validation\ValidatorInterface as JsonSchemaValidatorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\RemoteTools\EntityProfile\EntityProfileJsonSchemaValidationDecorator
 */
final class EntityProfileJsonSchemaValidationDecoratorTest extends TestCase {

  private EntityProfileJsonSchemaValidationDecorator $decorator;

  /**
   * @var \Civi\RemoteTools\EntityProfile\RemoteEntityProfileInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $profileMock;

  protected function setUp(): void {
    parent::setUp();
    $this->profileMock = $this->createMock(RemoteEntityProfileInterface::class);
    $jsonSchemaFactoryMock = $this->createMock(JsonSchemaFactoryInterface::class);
    $jsonSchemaValidatorMock = $this->createMock(JsonSchemaValidatorInterface::class);
    $this->decorator = new EntityProfileJsonSchemaValidationDecorator(
      $this->profileMock, $jsonSchemaFactoryMock, $jsonSchemaValidatorMock,
    );
  }

  public function testGetCreateFormSpec(): void {
    $arguments = ['foo' => 'bar'];
    $entityFields = ['field' => []];
    $contactId = 2;

    $formSpec = new FormSpec('Test');
    $formSpec->appendValidator($this->createMock(ValidatorInterface::class));
    $this->profileMock->method('getCreateFormSpec')
      ->with($arguments, $entityFields, $contactId)
      ->willReturn($formSpec);

    static::assertSame($formSpec, $this->decorator->getCreateFormSpec($arguments, $entityFields, $contactId));
    static::assertInstanceOf(JsonSchemaFormSpecValidator::class, $formSpec->getValidators()[0]);
  }

  public function testGetUpdateFormSpec(): void {
    $entityFields = ['field' => []];
    $entityValues = ['field' => 'value'];
    $contactId = 2;

    $formSpec = new FormSpec('Test');
    $formSpec->appendValidator($this->createMock(ValidatorInterface::class));
    $this->profileMock->method('getUpdateFormSpec')
      ->with($entityValues, $entityFields, $contactId)
      ->willReturn($formSpec);

    static::assertSame($formSpec, $this->decorator->getUpdateFormSpec($entityValues, $entityFields, $contactId));
    static::assertInstanceOf(JsonSchemaFormSpecValidator::class, $formSpec->getValidators()[0]);
  }

}
