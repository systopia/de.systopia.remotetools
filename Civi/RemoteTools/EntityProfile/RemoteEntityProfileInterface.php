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

/**
 * A profile for a remote entity that is mapped to one APIv4 entity. Custom
 * implementations should set name, entity name, and remote entity name in
 * public class constants (NAME, ENTITY_NAME, REMOTE_ENTITY_NAME) and be
 * registered in the service container like this:
 * $container->autowire(MyRemoteEntityProfile::class)
 *   ->addTag(MyRemoteEntityProfile::SERVICE_TAG);
 *
 * Instead of the constants mentioned above the values can be provided as tag
 * attributes of the service with lower cased constant name as key.
 *
 * Additionally, a custom handler class might be specified with the constant
 * HANDLER_CLASS (or lower cased as tag attribute). The constructor must have an
 * argument named $profile. All other arguments have to be autowireable.
 *
 * This class shall not be implemented directly, but AbstractRemoteEntityProfile,
 * AbstractReadOnlyRemoteEntityProfile, or AbstractRemoteEntityProfileDecorator
 * should be extended instead.
 *
 * Most methods have a parameter named $contactId. This contains the remote
 * contact ID resolved to a CiviCRM contact ID or NULL if no remote contact ID
 * was given.
 *
 * @see \Civi\RemoteTools\Api4\AbstractRemoteEntity
 * @see \Civi\RemoteTools\EntityProfile\AbstractRemoteEntityProfile
 * @see \Civi\RemoteTools\EntityProfile\AbstractReadOnlyRemoteEntityProfile
 * @see \Civi\RemoteTools\EntityProfile\Traits\ConstProfileMetadataTrait
 */
interface RemoteEntityProfileInterface {

  public const SERVICE_TAG = 'remote_tools.entity_profile';

  /**
   * @return string The name of the internal entity.
   *
   * @api
   */
  public function getEntityName(): string;

  /**
   * @return string The name of the profile.
   *
   * @api
   */
  public function getName(): string;

  /**
   * @return string The name of the remote entity.
   *
   * @api
   */
  public function getRemoteEntityName(): string;

  /**
   * @return bool
   *   Check permissions on CiviCRM API calls. The check will be applied to the
   *   API user not to the resolved remote contact. For this reason FALSE is
   *   recommended in most cases, so the API user just needs permission to
   *   access the remote API.
   *
   * @api
   */
  public function isCheckApiPermissions(?int $contactId): bool;

  /**
   * Fields only available as remote field should be handled in
   * convertRemoteFieldComparison(). The comparison cannot be applied on the
   * API result because it contradicts pagination.
   *
   * @phpstan-param array<string, array<string, mixed>> $entityFields
   *   Fields indexed by field name.
   *
   * @phpstan-return array<string, array<string, mixed>>
   *   Fields indexed by field name.
   *
   * @see convertRemoteFieldComparison()
   *
   * @api
   */
  public function getRemoteFields(array $entityFields, ?int $contactId): array;

  /**
   * Convert a comparison of a remote only field to an APIv4 condition. If NULL
   * is returned, no condition will be applied.
   *
   * @param \Civi\RemoteTools\Api4\Query\Comparison $comparison
   *
   * @api
   */
  public function convertRemoteFieldComparison(Comparison $comparison, ?int $contactId): ?ConditionInterface;

  /**
   * @param string $fieldName
   *   The complete field name, e.g. contact_id.display_name.
   * @param string $joinFieldName
   *   The name of the joined field, e.g. contact_id. This method is only
   *   called, if the joined field is part of the remote fields.
   *
   * @return bool TRUE if the implicit join is allowed.
   *
   * @see getRemoteFields()
   *
   * @api
   */
  public function isImplicitJoinAllowed(string $fieldName, string $joinFieldName, ?int $contactId): bool;

  /**
   * @phpstan-param list<string> $select
   *   The proposed select field names. Contains only "allowed" values on get,
   *   e.g. no implicit joins that are not allowed.
   * @phpstan-param 'delete'|'get'|'update' $actionName
   * @phpstan-param list<string> $remoteSelect
   *   The field names from the remote request if action is "get", empty array
   *   otherwise.
   *
   * @phpstan-return list<string>
   *   The field names to select. In most cases just $select or $select with
   *   additional field names, e.g. if required to decide if update/delete is
   *   allowed, or if there's no 1:1 mapping between entity fields and remote
   *   fields. If fields are added it is ensured that the records returned on
   *   "get" only contain remote fields or allowed implicit joins.
   *
   * @see getRemoteFields()
   * @see isImplicitJoinAllowed()
   *
   * @api
   */
  public function getSelectFieldNames(array $select, string $actionName, array $remoteSelect, ?int $contactId): array;

  /**
   * @phpstan-param 'delete'|'get'|'update' $actionName
   *
   * @return \Civi\RemoteTools\Api4\Query\ConditionInterface
   *   Conditions applied to "where".
   *
   * @api
   */
  public function getFilter(string $actionName, ?int $contactId): ?ConditionInterface;

  /**
   * Might be used to restrict access using related entities.
   *
   * @phpstan-param 'delete'|'get'|'update' $actionName
   *
   * @phpstan-return list<\Civi\RemoteTools\Api4\Query\Join>
   *   Joins added to the query.
   *
   * @api
   */
  public function getJoins(string $actionName, ?int $contactId): array;

  /**
   * @phpstan-param array<string, mixed> $entityValues
   * @phpstan-param list<string> $select
   *   Selected field names. Maybe empty on create or update.
   *
   * @phpstan-return array<string, mixed>
   *
   * @api
   */
  public function convertToRemoteValues(array $entityValues, array $select, ?int $contactId): array;

  /**
   * If isCreateGranted() always returns deny, this method won't be called and
   * might throw a \BadMethodCallException.
   *
   * @phpstan-param array<int|string, mixed> $arguments
   * @phpstan-param array<string, array<string, mixed>> $entityFields
   *   Entity fields indexed by name.
   *
   * @see getFieldLoadOptionsForFormSpec()
   * @see isCreateGranted()
   *
   * @api
   */
  public function getCreateFormSpec(array $arguments, array $entityFields, ?int $contactId): FormSpec;

  /**
   * If isUpdateGranted() always returns deny, this method won't be called and
   * might throw a \BadMethodCallException.
   *
   * @phpstan-param array<string, mixed> $entityValues
   *   Should be used for default values in form spec.
   * @phpstan-param array<string, array<string, mixed>> $entityFields
   *   Entity fields indexed by name.
   *
   * @see getFieldLoadOptionsForFormSpec()
   * @see isUpdateGranted()
   *
   * @api
   */
  public function getUpdateFormSpec(array $entityValues, array $entityFields, ?int $contactId): FormSpec;

  /**
   * @phpstan-return bool|list<string>
   *    Value for the 'loadOptions' parameter when fetching entity fields that
   *    are passed to the form spec creation methods. FALSE if no options are
   *    required, TRUE to get options as id-label-pairs, or an array of the
   *    required option properties.
   *
   * @see getCreateFormSpec()
   * @see getUpdateFormSpec()
   *
   * @api
   */
  public function getFieldLoadOptionsForFormSpec(): bool|array;

  /**
   * @phpstan-param array<int|string, mixed> $arguments
   *
   * @api
   */
  public function isCreateGranted(array $arguments, ?int $contactId): GrantResult;

  /**
   * @phpstan-param array<string, mixed> $entityValues
   *
   * @api
   */
  public function isDeleteGranted(array $entityValues, ?int $contactId): GrantResult;

  /**
   * @phpstan-param array<string, mixed>|null $entityValues
   *   If NULL, entity to update was not found. The update won't be granted in
   *   that case, anyway, but the implementation might want to override the
   *   message in that case.
   *
   * @api
   */
  public function isUpdateGranted(?array $entityValues, ?int $contactId): GrantResult;

  /**
   * @phpstan-param array<string, mixed> $newValues
   * @phpstan-param array<string, mixed>|null $oldValues
   *   NULL on create.
   * @phpstan-param array<string, mixed> $formData
   *
   * @return string
   *   The message that is shown when an entity was successfully inserted or
   *   updated.
   *
   * @api
   */
  public function getSaveSuccessMessage(
    array $newValues,
    ?array $oldValues,
    array $formData,
    ?int $contactId
  ): string;

  /**
   * Called before the entity is created.
   *
   * @phpstan-param array<int|string, mixed> $arguments
   * @phpstan-param array<string, mixed> $entityValues
   *    The values going to be inserted. Can be modified.
   * @phpstan-param array<string, array<string, mixed>> $entityFields
   *    Entity fields indexed by name.
   * @param \Civi\RemoteTools\Form\FormSpec\FormSpec $formSpec
   * @param int|null $contactId
   */
  public function onPreCreate(
    array $arguments,
    array &$entityValues,
    array $entityFields,
    FormSpec $formSpec,
    ?int $contactId
  ): void;

  /**
   * Called after the entity was created.
   *
   * @phpstan-param array<int|string, mixed> $arguments
   * @phpstan-param array<string, mixed> $entityValues
   * @phpstan-param array<string, array<string, mixed>> $entityFields
   *    Entity fields indexed by name.
   * @param \Civi\RemoteTools\Form\FormSpec\FormSpec $formSpec
   * @param int|null $contactId
   */
  public function onPostCreate(
    array $arguments,
    array $entityValues,
    array $entityFields,
    FormSpec $formSpec,
    ?int $contactId
  ): void;

  /**
   * Called before the entity is created.
   *
   * @phpstan-param array<string, mixed> $newValues
   *    The values going to be updated. Can be modified.
   * @phpstan-param array<string, mixed> $oldValues
   * @phpstan-param array<string, array<string, mixed>> $entityFields
   *    Entity fields indexed by name.
   * @param \Civi\RemoteTools\Form\FormSpec\FormSpec $formSpec
   * @param int|null $contactId
   */
  public function onPreUpdate(
    array &$newValues,
    array $oldValues,
    array $entityFields,
    FormSpec $formSpec,
    ?int $contactId
  ): void;

  /**
   * Called after the entity was created.
   *
   * @phpstan-param array<string, mixed> $newValues
   * @phpstan-param array<string, mixed> $oldValues
   * @phpstan-param array<string, array<string, mixed>> $entityFields
   *    Entity fields indexed by name.
   * @param \Civi\RemoteTools\Form\FormSpec\FormSpec $formSpec
   * @param int|null $contactId
   */
  public function onPostUpdate(
    array $newValues,
    array $oldValues,
    array $entityFields,
    FormSpec $formSpec,
    ?int $contactId
  ): void;

}
