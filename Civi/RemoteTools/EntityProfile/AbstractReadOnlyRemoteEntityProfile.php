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

namespace Civi\RemoteTools\EntityProfile;

use Civi\RemoteTools\EntityProfile\Authorization\GrantResult;
use Civi\RemoteTools\Form\FormSpec\FormSpec;

/**
 * Abstract implementation for read only access that assumes that internal and
 * external fields are the same. (getRemoteFields() and convertToRemoteValues()
 * can be reimplemented if necessary.)
 *
 * @codeCoverageIgnore
 *
 * @api
 */
abstract class AbstractReadOnlyRemoteEntityProfile extends AbstractRemoteEntityProfile {

  /**
   * @inheritDoc
   */
  public function getCreateFormSpec(array $arguments, array $entityFields, ?int $contactId): FormSpec {
    throw new \BadMethodCallException('Creating entities is not supported');
  }

  /**
   * @inheritDoc
   */
  public function getUpdateFormSpec(array $entityValues, array $entityFields, ?int $contactId): FormSpec {
    throw new \BadMethodCallException('Updating entities is not supported');
  }

  /**
   * @inheritDoc
   */
  public function isCreateGranted(array $arguments, ?int $contactId): GrantResult {
    return GrantResult::newDenied();
  }

  /**
   * @inheritDoc
   */
  public function isDeleteGranted(array $entityValues, ?int $contactId): GrantResult {
    return GrantResult::newDenied();
  }

  /**
   * @inheritDoc
   */
  public function isUpdateGranted(?array $entityValues, ?int $contactId): GrantResult {
    return GrantResult::newDenied();
  }

}
