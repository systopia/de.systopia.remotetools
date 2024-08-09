<?php
/*
 * Copyright (C) 2024 SYSTOPIA GmbH
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

namespace Civi\RemoteTools\ActionHandler;

use Civi\RemoteTools\Api4\Action\RemoteDeleteAction;
use Civi\RemoteTools\Api4\Action\RemoteSubmitCreateFormAction;
use Civi\RemoteTools\Api4\Action\RemoteSubmitUpdateFormAction;
use Civi\RemoteTools\Database\TransactionFactory;
use Civi\RemoteTools\PHPUnit\Traits\CreateMockTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\RemoteTools\ActionHandler\RemoteActionsHandlerTransactionDecorator
 */
final class RemoteActionsHandlerTransactionDecoratorTest extends TestCase {

  use CreateMockTrait;

  private RemoteActionsHandlerTransactionDecorator $decorator;

  /**
   * @var \Civi\RemoteTools\ActionHandler\RemoteEntityActionsHandlerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $handlerMock;

  /**
   * @var \Civi\RemoteTools\Database\TransactionFactory&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $transactionFactoryMock;

  protected function setUp(): void {
    parent::setUp();
    $this->handlerMock = $this->createMock(RemoteEntityActionsHandlerInterface::class);
    $this->transactionFactoryMock = $this->createMock(TransactionFactory::class);
    $this->decorator = new RemoteActionsHandlerTransactionDecorator(
      $this->handlerMock,
      $this->transactionFactoryMock
    );
  }

  public function testDelete(): void {
    $action = $this->createApi4ActionMock(RemoteDeleteAction::class, 'RemoteEntity', 'delete');

    $transactionMock = $this->createMock(\CRM_Core_Transaction::class);
    $this->transactionFactoryMock->method('createTransaction')
      ->willReturn($transactionMock);

    $transactionMock->expects(static::once())->method('commit');
    $this->handlerMock->method('delete')
      ->with($action)
      ->willReturn(['foo' => 'bar']);

    static::assertSame(['foo' => 'bar'], $this->decorator->delete($action));
  }

  public function testDeleteRollback(): void {
    $action = $this->createApi4ActionMock(RemoteDeleteAction::class, 'RemoteEntity', 'delete');

    $transactionMock = $this->createMock(\CRM_Core_Transaction::class);
    $this->transactionFactoryMock->method('createTransaction')
      ->willReturn($transactionMock);

    $exception = new \Exception('test');
    $transactionMock->expects(static::once())->method('rollback');
    $transactionMock->expects(static::once())->method('commit');
    $this->handlerMock->method('delete')
      ->with($action)
      ->willThrowException($exception);

    static::expectExceptionObject($exception);
    $this->decorator->delete($action);
  }

  public function testSubmitCreateForm(): void {
    $action = $this->createApi4ActionMock(RemoteSubmitCreateFormAction::class, 'RemoteEntity', 'submitCreateForm');

    $transactionMock = $this->createMock(\CRM_Core_Transaction::class);
    $this->transactionFactoryMock->method('createTransaction')
      ->willReturn($transactionMock);

    $transactionMock->expects(static::once())->method('commit');
    $this->handlerMock->method('submitCreateForm')
      ->with($action)
      ->willReturn(['foo' => 'bar']);

    static::assertSame(['foo' => 'bar'], $this->decorator->submitCreateForm($action));
  }

  public function testSubmitCreateFormRollback(): void {
    $action = $this->createApi4ActionMock(RemoteSubmitCreateFormAction::class, 'RemoteEntity', 'submitCreateForm');

    $transactionMock = $this->createMock(\CRM_Core_Transaction::class);
    $this->transactionFactoryMock->method('createTransaction')
      ->willReturn($transactionMock);

    $exception = new \Exception('test');
    $transactionMock->expects(static::once())->method('rollback');
    $transactionMock->expects(static::once())->method('commit');
    $this->handlerMock->method('submitCreateForm')
      ->with($action)
      ->willThrowException($exception);

    static::expectExceptionObject($exception);
    $this->decorator->submitCreateForm($action);
  }

  public function testSubmitUpdateForm(): void {
    $action = $this->createApi4ActionMock(RemoteSubmitUpdateFormAction::class, 'RemoteEntity', 'submitUpdateForm');

    $transactionMock = $this->createMock(\CRM_Core_Transaction::class);
    $this->transactionFactoryMock->method('createTransaction')
      ->willReturn($transactionMock);

    $transactionMock->expects(static::once())->method('commit');
    $this->handlerMock->method('submitUpdateForm')
      ->with($action)
      ->willReturn(['foo' => 'bar']);

    static::assertSame(['foo' => 'bar'], $this->decorator->submitUpdateForm($action));
  }

  public function testSubmitUpdateFormRollback(): void {
    $action = $this->createApi4ActionMock(RemoteSubmitUpdateFormAction::class, 'RemoteEntity', 'submitUpdateForm');

    $transactionMock = $this->createMock(\CRM_Core_Transaction::class);
    $this->transactionFactoryMock->method('createTransaction')
      ->willReturn($transactionMock);

    $exception = new \Exception('test');
    $transactionMock->expects(static::once())->method('rollback');
    $transactionMock->expects(static::once())->method('commit');
    $this->handlerMock->method('submitUpdateForm')
      ->with($action)
      ->willThrowException($exception);

    static::expectExceptionObject($exception);
    $this->decorator->submitUpdateForm($action);
  }

}
