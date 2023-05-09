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
 * A profile for a remote entity that is mapped to one APIv4 entity. Custom
 * implementations should set name, entity name, and remote entity name in
 * public class constants (NAME, ENTITY_NAME, REMOTE_ENTITY_NAME) and be
 * registered in the service container like this:
 * $container->autowire(MyRemoteEntityProfile::class)
 *   ->addTag(MyRemoteEntityProfile::SERVICE_TAG);
 *
 * Please note: With special where conditions it is possible to find out values
 * of not exposed fields. (Via implicit joins even of referenced entities.)
 *
 * @see \Civi\RemoteTools\Api4\AbstractRemoteEntity
 * @see \Civi\RemoteTools\EntityProfile\AbstractRemoteEntityProfile
 */
interface RemoteEntityProfileInterface {

  public const SERVICE_TAG = 'remote_tools.entity_profile';

  public function getEntityName(): string;

  public function getName(): string;

  public function getRemoteEntityName(): string;

  /**
   * @phpstan-return array<string>
   */
  public function getExtraFieldNames(): array;

  /**
   * @phpstan-param array<string, array<string, mixed>> $entityFields
   *   Fields indexed by field name.
   *
   * @phpstan-return array<string, array<string, mixed>>
   *   Fields indexed by field name.
   */
  public function getRemoteFields(array $entityFields): array;

  public function getFilter(?int $contactId): ?ConditionInterface;

  /**
   * @phpstan-param array<string, mixed> $entityValues
   *
   * @phpstan-return array<string, mixed>
   */
  public function convertToRemoteValues(?int $contactId, array $entityValues): array;

  /**
   * @phpstan-param array<int|string, mixed> $arguments
   * @phpstan-param array<string, array<string, mixed>> $entityFields
   *   Entity fields indexed by name.
   *
   * @see isFormSpecNeedsFieldOptions
   */
  public function getCreateFormSpec(array $arguments, array $entityFields): FormSpec;

  /**
   * @phpstan-param array<string, mixed> $entityValues
   * @phpstan-param array<string, array<string, mixed>> $entityFields
   *   Entity fields indexed by name.
   *
   * @see isFormSpecNeedsFieldOptions
   */
  public function getUpdateFormSpec(array $entityValues, array $entityFields): FormSpec;

  /**
   * @return bool
   *   TRUE if the options for an entity field are required to create form spec.
   *
   * @see getCreateFormSpec
   * @see getUpdateFormSpec
   */
  public function isFormSpecNeedsFieldOptions(): bool;

  /**
   * @phpstan-param array<int|string, mixed> $arguments
   */
  public function isCreateAllowed(?int $contactId, array $arguments): bool;

  /**
   * @phpstan-param array<string, mixed> $entityValues
   */
  public function isDeleteAllowed(?int $contactId, array $entityValues): bool;

  /**
   * @phpstan-param array<string, mixed> $entityValues
   */
  public function isUpdateAllowed(?int $contactId, array $entityValues): bool;

  /**
   * @phpstan-param array<string, mixed> $formData
   */
  public function validateCreateData(array $formData): ValidationResult;

  /**
   * @phpstan-param array<string, mixed> $formData
   * @phpstan-param array<string, mixed> $currentEntityValues
   */
  public function validateUpdateData(
          array $formData,
          array $currentEntityValues
  ): ValidationResult;

  /**
   * @phpstan-param array<string, mixed> $formData
   *
   * @phpstan-return array<string, mixed>
   */
  public function convertCreateDataToEntityValues(array $formData): array;

  /**
   * @phpstan-param array<string, mixed> $formData
   *
   * @phpstan-return array<string, mixed>
   */
  public function convertUpdateDataToEntityValues(array $formData): array;

  /**
   * @phpstan-param array<string, mixed> $entityValues
   *
   * @phpstan-return array<string, mixed>
   */
  public function convertToFormData(array $entityValues): array;

}
