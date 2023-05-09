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

/**
 * Abstract implementation that assumes that internal and external fields are
 * the same. Create, delete, and update are allowed by default. All custom
 * fields are available.
 */
abstract class AbstractRemoteEntityProfile implements RemoteEntityProfileInterface {

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

  public function isFormSpecNeedsFieldOptions(): bool {
    return FALSE;
  }

  public function isCreateAllowed(?int $contactId, array $arguments): bool {
    return TRUE;
  }

  /**
   * @inheritDoc
   */
  public function isDeleteAllowed(?int $contactId, array $entityValues): bool {
    return TRUE;
  }

  /**
   * @inheritDoc
   */
  public function isUpdateAllowed(?int $contactId, array $entityValues): bool {
    return TRUE;
  }

  /**
   * @inheritDoc
   */
  public function validateCreateData(array $formData): ValidationResult {
    return new ValidationResult();
  }

  /**
   * @inheritDoc
   */
  public function validateUpdateData(
          array $formData,
          array $currentEntityValues
  ): ValidationResult {
    return new ValidationResult();
  }

  /**
   * @inheritDoc
   */
  public function convertCreateDataToEntityValues(array $formData): array {
    return $formData;
  }

  /**
   * @inheritDoc
   */
  public function convertUpdateDataToEntityValues(array $formData): array {
    return $formData;
  }

  /**
   * @inheritDoc
   */
  public function convertToFormData(array $entityValues): array {
    return $entityValues;
  }

}
