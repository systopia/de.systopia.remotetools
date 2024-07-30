<?php
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
