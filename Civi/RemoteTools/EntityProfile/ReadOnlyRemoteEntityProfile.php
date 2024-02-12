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

use Civi\RemoteTools\Api4\Query\ConditionInterface;
use Civi\RemoteTools\EntityProfile\Authorization\GrantResult;
use Civi\RemoteTools\Form\FormSpec\FormSpec;

/**
 * @codeCoverageIgnore
 *
 * @api
 */
class ReadOnlyRemoteEntityProfile extends AbstractRemoteEntityProfile {

  private string $entityName;

  private string $name;

  private string $remoteEntityName;

  public function __construct(
    string $name,
    string $entityName,
    string $remoteEntityName
  ) {
    $this->name = $name;
    $this->entityName = $entityName;
    $this->remoteEntityName = $remoteEntityName;
  }

  /**
   * @inheritDoc
   */
  public function getEntityName(): string {
    return $this->entityName;
  }

  /**
   * @inheritDoc
   */
  public function getName(): string {
    return $this->name;
  }

  /**
   * @inheritDoc
   */
  public function getRemoteEntityName(): string {
    return $this->remoteEntityName;
  }

  /**
   * @inheritDoc
   */
  public function getFilter(string $actionName, ?int $contactId): ?ConditionInterface {
    return NULL;
  }

  /**
   * @inheritDoc
   */
  public function getCreateFormSpec(array $arguments, array $entityFields, ?int $contactId): FormSpec {
    throw new \BadMethodCallException(sprintf('Creating entities is not supported'));
  }

  /**
   * @inheritDoc
   */
  public function getUpdateFormSpec(array $entityValues, array $entityFields, ?int $contactId): FormSpec {
    throw new \BadMethodCallException(sprintf('Updating entities is not supported'));
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
