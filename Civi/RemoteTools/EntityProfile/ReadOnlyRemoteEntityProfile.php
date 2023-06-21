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

use Civi\RemoteTools\Api4\Query\Comparison;
use Civi\RemoteTools\Api4\Query\ConditionInterface;
use Civi\RemoteTools\EntityProfile\Authorization\GrantResult;
use Civi\RemoteTools\Form\FormSpec\FormSpec;
use Civi\RemoteTools\Form\Validation\ValidationResult;

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
  public function isCheckApiPermissions(?int $contactId): bool {
    return FALSE;
  }

  /**
   * @inheritDoc
   */
  public function getRemoteFields(array $entityFields, ?int $contactId): array {
    return $entityFields;
  }

  /**
   * @inheritDoc
   */
  public function getSelectFieldNames(array $select, string $actionName, array $remoteSelect, ?int $contactId): array {
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
  public function getFilter(string $actionName, ?int $contactId): ?ConditionInterface {
    return NULL;
  }

  /**
   * @inheritDoc
   */
  public function convertRemoteFieldComparison(Comparison $comparison, ?int $contactId): ?ConditionInterface {
    return NULL;
  }

  /**
   * @inheritDoc
   */
  public function convertToRemoteValues(array $entityValues, array $select, ?int $contactId): array {
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

  /**
   * @inheritDoc
   */
  public function validateCreateData(
    array $formData,
    array $arguments,
    array $entityFields,
    ?int $contactId
  ): ValidationResult {
    throw new \BadMethodCallException(sprintf('Creating entities is not supported'));
  }

  /**
   * @inheritDoc
   */
  public function validateUpdateData(
    array $formData,
    array $currentEntityValues,
    array $entityFields,
    ?int $contactId
  ): ValidationResult {
    throw new \BadMethodCallException(sprintf('Updating entities is not supported'));
  }

  /**
   * @inheritDoc
   */
  public function convertCreateDataToEntityValues(array $formData, array $arguments, ?int $contactId): array {
    throw new \BadMethodCallException(sprintf('Creating entities is not supported'));
  }

  /**
   * @inheritDoc
   */
  public function convertUpdateDataToEntityValues(array $formData, array $currentEntityValues, ?int $contactId): array {
    throw new \BadMethodCallException(sprintf('Updating entities is not supported'));
  }

  /**
   * @inheritDoc
   */
  public function convertToFormData(array $entityValues, ?int $contactId): array {
    throw new \BadMethodCallException(sprintf('Creating and updating entities is not supported'));
  }

  /**
   * @inheritDoc
   */
  public function getSaveSuccessMessage(
    array $newValues,
    array $oldValues,
    string $action,
    array $formData,
    ?int $contactId
  ): string {
    throw new \BadMethodCallException(sprintf('Creating and updating entities is not supported'));
  }

}
