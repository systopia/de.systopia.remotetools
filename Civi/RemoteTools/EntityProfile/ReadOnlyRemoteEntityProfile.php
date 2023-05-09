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

  public function getEntityName(): string {
    return $this->entityName;
  }

  public function getName(): string {
    return $this->name;
  }

  public function getRemoteEntityName(): string {
    return $this->remoteEntityName;
  }

  /**
   * @inheritDoc
   */
  public function getExtraFieldNames(): array {
    return ['custom.*'];
  }

  /**
   * @inheritDoc
   */
  public function getRemoteFields(array $entityFields): array {
    return $entityFields;
  }

  public function getFilter(?int $contactId): ?ConditionInterface {
    return NULL;
  }

  /**
   * @inheritDoc
   */
  public function convertToRemoteValues(?int $contactId, array $entityValues): array {
    return $entityValues;
  }

  /**
   * @inheritDoc
   */
  public function getCreateFormSpec(array $arguments, array $entityFields): FormSpec {
    throw new \BadMethodCallException(sprintf('Creating entities is not supported'));
  }

  /**
   * @inheritDoc
   */
  public function getUpdateFormSpec(array $entityValues, array $entityFields): FormSpec {
    throw new \BadMethodCallException(sprintf('Updating entities is not supported'));
  }

  public function isFormSpecNeedsFieldOptions(): bool {
    return FALSE;
  }

  /**
   * @inheritDoc
   */
  public function isCreateAllowed(?int $contactId, array $arguments): bool {
    return FALSE;
  }

  /**
   * @inheritDoc
   */
  public function isDeleteAllowed(?int $contactId, array $entityValues): bool {
    return FALSE;
  }

  /**
   * @inheritDoc
   */
  public function isUpdateAllowed(?int $contactId, array $entityValues): bool {
    return FALSE;
  }

  /**
   * @inheritDoc
   */
  public function validateCreateData(array $formData): ValidationResult {
    throw new \BadMethodCallException(sprintf('Creating entities is not supported'));
  }

  /**
   * @inheritDoc
   */
  public function validateUpdateData(array $formData, array $currentEntityValues): ValidationResult {
    throw new \BadMethodCallException(sprintf('Updating entities is not supported'));
  }

  /**
   * @inheritDoc
   */
  public function convertCreateDataToEntityValues(array $formData): array {
    throw new \BadMethodCallException(sprintf('Creating entities is not supported'));
  }

  /**
   * @inheritDoc
   */
  public function convertUpdateDataToEntityValues(array $formData): array {
    throw new \BadMethodCallException(sprintf('Updating entities is not supported'));
  }

  /**
   * @inheritDoc
   */
  public function convertToFormData(array $entityValues): array {
    throw new \BadMethodCallException(sprintf('Creating and updating entities is not supported'));
  }

}
