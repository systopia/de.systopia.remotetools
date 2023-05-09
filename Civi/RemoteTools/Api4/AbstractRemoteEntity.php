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

namespace Civi\RemoteTools\Api4;

use Civi\Api4\Generic\AbstractEntity;
use Civi\RemoteTools\Api4\Action\RemoteCheckAccessAction;
use Civi\RemoteTools\Api4\Action\RemoteDeleteAction;
use Civi\RemoteTools\Api4\Action\RemoteGetAction;
use Civi\RemoteTools\Api4\Action\RemoteGetActions;
use Civi\RemoteTools\Api4\Action\RemoteGetCreateFormAction;
use Civi\RemoteTools\Api4\Action\RemoteGetFieldsAction;
use Civi\RemoteTools\Api4\Action\RemoteGetUpdateFormAction;
use Civi\RemoteTools\Api4\Action\RemoteSubmitCreateFormAction;
use Civi\RemoteTools\Api4\Action\RemoteSubmitUpdateFormAction;
use Civi\RemoteTools\Api4\Action\RemoteValidateCreateFormAction;
use Civi\RemoteTools\Api4\Action\RemoteValidateUpdateFormAction;

class AbstractRemoteEntity extends AbstractEntity {

  public static function checkAccess() {
    return new RemoteCheckAccessAction(static::getEntityName(), __FUNCTION__);
  }

  public static function delete(): RemoteDeleteAction {
    return new RemoteDeleteAction(static::getEntityName(), __FUNCTION__);
  }

  public static function get(): RemoteGetAction {
    return new RemoteGetAction(static::getEntityName(), __FUNCTION__);
  }

  public static function getActions($checkPermissions = TRUE) {
    return new RemoteGetActions(static::getEntityName(), __FUNCTION__);
  }

  /**
   * @inheritDoc
   */
  public static function getFields() {
    return new RemoteGetFieldsAction(static::getEntityName(), __FUNCTION__);
  }

  public static function getCreateForm(): RemoteGetCreateFormAction {
    return new RemoteGetCreateFormAction(static::getEntityName(), __FUNCTION__);
  }

  public static function getUpdateForm(): RemoteGetUpdateFormAction {
    return new RemoteGetUpdateFormAction(static::getEntityName(), __FUNCTION__);
  }

  public static function validateCreateForm(): RemoteValidateCreateFormAction {
    return new RemoteValidateCreateFormAction(static::getEntityName(), __FUNCTION__);
  }

  public static function validateUpdateForm(): RemoteValidateUpdateFormAction {
    return new RemoteValidateUpdateFormAction(static::getEntityName(), __FUNCTION__);
  }

  public static function submitCreateForm(): RemoteSubmitCreateFormAction {
    return new RemoteSubmitCreateFormAction(static::getEntityName(), __FUNCTION__);
  }

  public static function submitUpdateForm(): RemoteSubmitUpdateFormAction {
    return new RemoteSubmitUpdateFormAction(static::getEntityName(), __FUNCTION__);
  }

}
