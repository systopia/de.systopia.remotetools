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
use CRM_Remotetools_ExtensionUtil as E;

/**
 * Adds the fields CAN_delete and CAN_update.
 */
final class EntityProfilePermissionDecorator implements RemoteEntityProfileInterface {

  private RemoteEntityProfileInterface $profile;

  public function __construct(RemoteEntityProfileInterface $profile) {
    $this->profile = $profile;
  }

  public function getEntityName(): string {
    return $this->profile->getEntityName();
  }

  public function getName(): string {
    return $this->profile->getName();
  }

  public function getRemoteEntityName(): string {
    return $this->profile->getRemoteEntityName();
  }

  /**
   * @inheritDoc
   */
  public function getRemoteFields(array $entityFields): array {
    $remoteFields = $this->profile->getRemoteFields($entityFields);
    $remoteFields['CAN_delete'] = [
      'type' => 'Extra',
      'entity' => $this->profile->getRemoteEntityName(),
      'readonly' => TRUE,
      'name' => 'CAN_delete',
      'title' => E::ts('Deletion Allowed'),
      'description' => E::ts('Is remote contact allowed to delete the record.'),
      'data_type' => 'Boolean',
      'label' => E::ts('Deletion Allowed'),
    ];
    $remoteFields['CAN_update'] = [
      'type' => 'Extra',
      'entity' => $this->profile->getRemoteEntityName(),
      'readonly' => TRUE,
      'name' => 'CAN_update',
      'title' => E::ts('Update Allowed'),
      'description' => E::ts('Is remote contact allowed to update the record.'),
      'data_type' => 'Boolean',
      'label' => E::ts('Update Allowed'),
    ];

    return $remoteFields;
  }

  public function isImplicitJoinAllowed(string $fieldName, string $joinFieldName, ?int $contactId): bool {
    return $this->profile->isImplicitJoinAllowed($fieldName, $joinFieldName, $contactId);
  }

  /**
   * @inheritDoc
   */
  public function getSelectFieldNames(
    array $select,
    string $action,
    array $remoteSelect,
    ?int $contactId
  ): array {
    return $this->profile->getSelectFieldNames($select, $action, $remoteSelect, $contactId);
  }

  public function getFilter(?int $contactId): ?ConditionInterface {
    return $this->profile->getFilter($contactId);
  }

  /**
   * @inheritDoc
   */
  public function convertToRemoteValues(array $entityValues, ?int $contactId): array {
    $remoteValues = $this->profile->convertToRemoteValues($entityValues, $contactId);
    $remoteValues['CAN_delete'] = $this->profile->isDeleteAllowed($entityValues, $contactId);
    $remoteValues['CAN_update'] = $this->profile->isUpdateAllowed($entityValues, $contactId);

    return $remoteValues;
  }

  /**
   * @inheritDoc
   */
  public function getCreateFormSpec(array $arguments, array $entityFields, ?int $contactId): FormSpec {
    return $this->profile->getCreateFormSpec($arguments, $entityFields, $contactId);
  }

  /**
   * @inheritDoc
   */
  public function getUpdateFormSpec(array $entityValues, array $entityFields, ?int $contactId): FormSpec {
    return $this->profile->getUpdateFormSpec($entityValues, $entityFields, $contactId);
  }

  /**
   * @inheritDoc
   */
  public function isFormSpecNeedsFieldOptions(): bool {
    return $this->profile->isFormSpecNeedsFieldOptions();
  }

  /**
   * @inheritDoc
   */
  public function isCreateAllowed(array $arguments, ?int $contactId): bool {
    return $this->profile->isCreateAllowed($arguments, $contactId);
  }

  /**
   * @inheritDoc
   */
  public function isDeleteAllowed(array $entityValues, ?int $contactId): bool {
    return $this->profile->isDeleteAllowed($entityValues, $contactId);
  }

  /**
   * @inheritDoc
   */
  public function isUpdateAllowed(array $entityValues, ?int $contactId): bool {
    return $this->profile->isUpdateAllowed($entityValues, $contactId);
  }

  /**
   * @inheritDoc
   */
  public function validateCreateData(array $formData, ?int $contactId): ValidationResult {
    return $this->profile->validateCreateData($formData, $contactId);
  }

  /**
   * @inheritDoc
   */
  public function validateUpdateData(array $formData, array $currentEntityValues, ?int $contactId): ValidationResult {
    return $this->profile->validateUpdateData($formData, $currentEntityValues, $contactId);
  }

  /**
   * @inheritDoc
   */
  public function convertCreateDataToEntityValues(array $formData, ?int $contactId): array {
    return $this->profile->convertCreateDataToEntityValues($formData, $contactId);
  }

  /**
   * @inheritDoc
   */
  public function convertUpdateDataToEntityValues(array $formData, ?int $contactId): array {
    return $this->profile->convertUpdateDataToEntityValues($formData, $contactId);
  }

  /**
   * @inheritDoc
   */
  public function convertToFormData(array $entityValues, ?int $contactId): array {
    return $this->profile->convertToFormData($entityValues, $contactId);
  }

}
