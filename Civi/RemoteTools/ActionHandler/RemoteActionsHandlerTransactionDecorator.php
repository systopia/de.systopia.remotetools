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

final class RemoteActionsHandlerTransactionDecorator extends AbstractRemoteEntityActionsHandlerDecorator {

  private TransactionFactory $transactionFactory;

  public function __construct(RemoteEntityActionsHandlerInterface $handler, TransactionFactory $transactionFactory) {
    parent::__construct($handler);
    $this->transactionFactory = $transactionFactory;
  }

  public function delete(RemoteDeleteAction $action): array {
    $transaction = $this->transactionFactory->createTransaction();
    try {
      return parent::delete($action);
    }
    // @phpstan-ignore-next-line Dead catch clause.
    catch (\Throwable $e) {
      // This just sets a flag. Rollback is actually performed on commit() method call in finally block.
      $transaction->rollback();

      throw $e;
    }
    finally {
      $transaction->commit();
    }
  }

  public function submitCreateForm(RemoteSubmitCreateFormAction $action): array {
    $transaction = $this->transactionFactory->createTransaction();
    try {
      return parent::submitCreateForm($action);
    }
    // @phpstan-ignore-next-line Dead catch clause.
    catch (\Throwable $e) {
      $transaction->rollback();

      throw $e;
    }
    finally {
      $transaction->commit();
    }
  }

  public function submitUpdateForm(RemoteSubmitUpdateFormAction $action): array {
    $transaction = $this->transactionFactory->createTransaction();
    try {
      return parent::submitUpdateForm($action);
    }
    // @phpstan-ignore-next-line Dead catch clause.
    catch (\Throwable $e) {
      $transaction->rollback();

      throw $e;
    }
    finally {
      $transaction->commit();
    }
  }

}
