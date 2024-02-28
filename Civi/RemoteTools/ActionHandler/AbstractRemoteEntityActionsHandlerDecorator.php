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

namespace Civi\RemoteTools\ActionHandler;

use Civi\Api4\Generic\Result;
use Civi\RemoteTools\Api4\Action\RemoteDeleteAction;
use Civi\RemoteTools\Api4\Action\RemoteGetAction;
use Civi\RemoteTools\Api4\Action\RemoteGetCreateFormAction;
use Civi\RemoteTools\Api4\Action\RemoteGetFieldsAction;
use Civi\RemoteTools\Api4\Action\RemoteGetUpdateFormAction;
use Civi\RemoteTools\Api4\Action\RemoteSubmitCreateFormAction;
use Civi\RemoteTools\Api4\Action\RemoteSubmitUpdateFormAction;
use Civi\RemoteTools\Api4\Action\RemoteValidateCreateFormAction;
use Civi\RemoteTools\Api4\Action\RemoteValidateUpdateFormAction;

abstract class AbstractRemoteEntityActionsHandlerDecorator implements RemoteEntityActionsHandlerInterface {

  protected RemoteEntityActionsHandlerInterface $handler;

  public function __construct(RemoteEntityActionsHandlerInterface $handler) {
    $this->handler = $handler;
  }

  public function delete(RemoteDeleteAction $action): array {
    return $this->handler->delete($action);
  }

  public function get(RemoteGetAction $action): Result {
    return $this->handler->get($action);
  }

  public function getFields(RemoteGetFieldsAction $action): Result {
    return $this->handler->getFields($action);
  }

  public function getCreateForm(RemoteGetCreateFormAction $action): array {
    return $this->handler->getCreateForm($action);
  }

  public function getUpdateForm(RemoteGetUpdateFormAction $action): array {
    return $this->handler->getUpdateForm($action);
  }

  public function validateCreateForm(RemoteValidateCreateFormAction $action): array {
    return $this->handler->validateCreateForm($action);
  }

  public function validateUpdateForm(RemoteValidateUpdateFormAction $action): array {
    return $this->validateUpdateForm($action);
  }

  public function submitCreateForm(RemoteSubmitCreateFormAction $action): array {
    return $this->handler->submitCreateForm($action);
  }

  public function submitUpdateForm(RemoteSubmitUpdateFormAction $action): array {
    return $this->submitUpdateForm($action);
  }

}
