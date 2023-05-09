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
  public function getExtraFieldNames(): array {
    return $this->profile->getExtraFieldNames();
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

  public function getFilter(?int $contactId): ?ConditionInterface {
    return $this->profile->getFilter($contactId);
  }

  /**
   * @inheritDoc
   */
  public function convertToRemoteValues(?int $contactId, array $entityValues): array {
    $remoteValues = $this->profile->convertToRemoteValues($contactId, $entityValues);
    $remoteValues['CAN_delete'] = $this->profile->isDeleteAllowed($contactId, $entityValues);
    $remoteValues['CAN_update'] = $this->profile->isUpdateAllowed($contactId, $entityValues);

    return $remoteValues;
  }

  /**
   * @inheritDoc
   */
  public function getCreateFormSpec(array $arguments, array $entityFields): FormSpec {
    return $this->profile->getCreateFormSpec($arguments, $entityFields);
  }

  /**
   * @inheritDoc
   */
  public function getUpdateFormSpec(array $entityValues, array $entityFields): FormSpec {
    return $this->profile->getUpdateFormSpec($entityValues, $entityFields);
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
  public function isCreateAllowed(?int $contactId, array $arguments): bool {
    return $this->profile->isCreateAllowed($contactId, $arguments);
  }

  /**
   * @inheritDoc
   */
  public function isDeleteAllowed(?int $contactId, array $entityValues): bool {
    return $this->profile->isDeleteAllowed($contactId, $entityValues);
  }

  /**
   * @inheritDoc
   */
  public function isUpdateAllowed(?int $contactId, array $entityValues): bool {
    return $this->profile->isUpdateAllowed($contactId, $entityValues);
  }

  /**
   * @inheritDoc
   */
  public function validateCreateData(array $formData): ValidationResult {
    return $this->profile->validateCreateData($formData);
  }

  /**
   * @inheritDoc
   */
  public function validateUpdateData(array $formData, array $currentEntityValues): ValidationResult {
    return $this->profile->validateUpdateData($formData, $currentEntityValues);
  }

  /**
   * @inheritDoc
   */
  public function convertCreateDataToEntityValues(array $formData): array {
    return $this->profile->convertCreateDataToEntityValues($formData);
  }

  /**
   * @inheritDoc
   */
  public function convertUpdateDataToEntityValues(array $formData): array {
    return $this->profile->convertUpdateDataToEntityValues($formData);
  }

  /**
   * @inheritDoc
   */
  public function convertToFormData(array $entityValues): array {
    return $this->profile->convertToFormData($entityValues);
  }

}
