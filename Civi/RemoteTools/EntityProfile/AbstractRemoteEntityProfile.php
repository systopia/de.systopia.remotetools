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
use CRM_Remotetools_ExtensionUtil as E;

/**
 * Abstract implementation that assumes that internal and external fields are
 * the same. (getRemoteFields() and convertToRemoteValues() can be reimplemented
 * if necessary.) Create, delete, and update are allowed by default. (Delete and
 * update is limited to those entities that are not filtered via getFilter()).
 *
 * @codeCoverageIgnore
 *
 * @api
 */
abstract class AbstractRemoteEntityProfile implements RemoteEntityProfileInterface {

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
  public function getJoins(string $actionName, ?int $contactId): array {
    return [];
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
  public function getFieldLoadOptionsForFormSpec() {
    return FALSE;
  }

  /**
   * @inheritDoc
   */
  public function isCreateGranted(array $arguments, ?int $contactId): GrantResult {
    return GrantResult::newPermitted();
  }

  /**
   * @inheritDoc
   */
  public function isDeleteGranted(array $entityValues, ?int $contactId): GrantResult {
    return GrantResult::newPermitted();
  }

  /**
   * @inheritDoc
   */
  public function isUpdateGranted(?array $entityValues, ?int $contactId): GrantResult {
    return GrantResult::newPermitted();
  }

  /**
   * @inheritDoc
   */
  public function getSaveSuccessMessage(
    array $newValues,
    ?array $oldValues,
    array $formData,
    ?int $contactId
  ): string {
    return E::ts('Saved successfully');
  }

  /**
   * @inheritDoc
   */
  public function onPreCreate(
    array $arguments,
    array &$entityValues,
    array $entityFields,
    FormSpec $formSpec,
    ?int $contactId
  ): void {
  }

  /**
   * @inheritDoc
   */
  public function onPostCreate(
    array $arguments,
    array $entityValues,
    array $entityFields,
    FormSpec $formSpec,
    ?int $contactId
  ): void {
  }

  /**
   * @inheritDoc
   */
  public function onPreUpdate(
    array &$newValues,
    array $oldValues,
    array $entityFields,
    FormSpec $formSpec,
    ?int $contactId
  ): void {
  }

  /**
   * @inheritDoc
   */
  public function onPostUpdate(
    array $newValues,
    array $oldValues,
    array $entityFields,
    FormSpec $formSpec,
    ?int $contactId
  ): void {
  }

}
