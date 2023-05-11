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

class ReadOnlyRemoteEntityProfile implements RemoteEntityProfileInterface {

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
  public function getRemoteFields(array $entityFields): array {
    return $entityFields;
  }

  /**
   * @inheritDoc
   */
  public function getSelectFieldNames(array $select, string $action, array $remoteSelect, ?int $contactId): array {
    return $select;
  }

  /**
   * @inheritDoc
   */
  public function isImplicitJoinAllowed(string $fieldName, string $joinFieldName, ?int $contactId): bool {
    return FALSE;
  }

  /**
   * @inheritDoc
   */
  public function getFilter(?int $contactId): ?ConditionInterface {
    return NULL;
  }

  /**
   * @inheritDoc
   */
  public function convertToRemoteValues(array $entityValues, ?int $contactId): array {
    return $entityValues;
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
  public function isFormSpecNeedsFieldOptions(): bool {
    return FALSE;
  }

  /**
   * @inheritDoc
   */
  public function isCreateAllowed(array $arguments, ?int $contactId): bool {
    return FALSE;
  }

  /**
   * @inheritDoc
   */
  public function isDeleteAllowed(array $entityValues, ?int $contactId): bool {
    return FALSE;
  }

  /**
   * @inheritDoc
   */
  public function isUpdateAllowed(array $entityValues, ?int $contactId): bool {
    return FALSE;
  }

  /**
   * @inheritDoc
   */
  public function validateCreateData(array $formData, ?int $contactId): ValidationResult {
    throw new \BadMethodCallException(sprintf('Creating entities is not supported'));
  }

  /**
   * @inheritDoc
   */
  public function validateUpdateData(array $formData, array $currentEntityValues, ?int $contactId): ValidationResult {
    throw new \BadMethodCallException(sprintf('Updating entities is not supported'));
  }

  /**
   * @inheritDoc
   */
  public function convertCreateDataToEntityValues(array $formData, ?int $contactId): array {
    throw new \BadMethodCallException(sprintf('Creating entities is not supported'));
  }

  /**
   * @inheritDoc
   */
  public function convertUpdateDataToEntityValues(array $formData, ?int $contactId): array {
    throw new \BadMethodCallException(sprintf('Updating entities is not supported'));
  }

  /**
   * @inheritDoc
   */
  public function convertToFormData(array $entityValues, ?int $contactId): array {
    throw new \BadMethodCallException(sprintf('Creating and updating entities is not supported'));
  }

}
