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
use Civi\RemoteTools\Form\FormSpec\FormSpec;
use Civi\RemoteTools\Form\Validation\ValidationResult;

abstract class AbstractRemoteEntityProfileDecorator implements RemoteEntityProfileInterface {

  protected RemoteEntityProfileInterface $profile;

  public function __construct(RemoteEntityProfileInterface $profile) {
    $this->profile = $profile;
  }

  /**
   * @inheritDoc
   */
  public function getEntityName(): string {
    return $this->profile->getEntityName();
  }

  /**
   * @inheritDoc
   */
  public function getName(): string {
    return $this->profile->getName();
  }

  /**
   * @inheritDoc
   */
  public function getRemoteEntityName(): string {
    return $this->profile->getRemoteEntityName();
  }

  /**
   * @inheritDoc
   */
  public function isCheckApiPermissions(?int $contactId): bool {
    return $this->profile->isCheckApiPermissions($contactId);
  }

  /**
   * @inheritDoc
   */
  public function getRemoteFields(array $entityFields, ?int $contactId): array {
    return $this->profile->getRemoteFields($entityFields, $contactId);
  }

  /**
   * @inheritDoc
   */
  public function isImplicitJoinAllowed(string $fieldName, string $joinFieldName, ?int $contactId): bool {
    return $this->profile->isImplicitJoinAllowed($fieldName, $joinFieldName, $contactId);
  }

  /**
   * @inheritDoc
   */
  public function getSelectFieldNames(
    array $select,
    string $actionName,
    array $remoteSelect,
    ?int $contactId
  ): array {
    return $this->profile->getSelectFieldNames($select, $actionName, $remoteSelect, $contactId);
  }

  /**
   * @inheritDoc
   */
  public function getFilter(string $actionName, ?int $contactId): ?ConditionInterface {
    return $this->profile->getFilter($actionName, $contactId);
  }

  /**
   * @inheritDoc
   */
  public function convertToRemoteValues(array $entityValues, array $select, ?int $contactId): array {
    return $this->profile->convertToRemoteValues($entityValues, $select, $contactId);
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
  public function validateCreateData(array $formData, array $arguments, ?int $contactId): ValidationResult {
    return $this->profile->validateCreateData($formData, $arguments, $contactId);
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
  public function convertCreateDataToEntityValues(array $formData, array $arguments, ?int $contactId): array {
    return $this->profile->convertCreateDataToEntityValues($formData, $arguments, $contactId);
  }

  /**
   * @inheritDoc
   */
  public function convertUpdateDataToEntityValues(array $formData, array $currentEntityValues, ?int $contactId): array {
    return $this->profile->convertUpdateDataToEntityValues($formData, $currentEntityValues, $contactId);
  }

  /**
   * @inheritDoc
   */
  public function convertToFormData(array $entityValues, ?int $contactId): array {
    return $this->profile->convertToFormData($entityValues, $contactId);
  }

}
