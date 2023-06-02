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

use Civi\RemoteTools\Api4\Util\SelectUtil;
use CRM_Remotetools_ExtensionUtil as E;

/**
 * Adds the fields CAN_delete and CAN_update.
 */
final class EntityProfilePermissionDecorator extends AbstractRemoteEntityProfileDecorator {

  /**
   * @inheritDoc
   */
  public function getRemoteFields(array $entityFields, ?int $contactId): array {
    $remoteFields = parent::getRemoteFields($entityFields, $contactId);
    $remoteFields['CAN_delete'] = [
      'type' => 'Extra',
      'entity' => $this->profile->getRemoteEntityName(),
      'readonly' => TRUE,
      'name' => 'CAN_delete',
      'title' => E::ts('Is Deletion Allowed'),
      'description' => E::ts('Is remote contact allowed to delete the record.'),
      'data_type' => 'Boolean',
      'label' => E::ts('Is Deletion Allowed'),
    ];
    $remoteFields['CAN_update'] = [
      'type' => 'Extra',
      'entity' => $this->profile->getRemoteEntityName(),
      'readonly' => TRUE,
      'name' => 'CAN_update',
      'title' => E::ts('Is Update Allowed'),
      'description' => E::ts('Is remote contact allowed to update the record.'),
      'data_type' => 'Boolean',
      'label' => E::ts('Is Update Allowed'),
    ];

    return $remoteFields;
  }

  /**
   * @inheritDoc
   */
  public function convertToRemoteValues(array $entityValues, array $select, ?int $contactId): array {
    $remoteValues = parent::convertToRemoteValues($entityValues, $select, $contactId);
    if (SelectUtil::isFieldSelected('CAN_delete', $select)) {
      $remoteValues['CAN_delete'] = $this->profile->isDeleteAllowed($entityValues, $contactId);
    }
    if (SelectUtil::isFieldSelected('CAN_update', $select)) {
      $remoteValues['CAN_update'] = $this->profile->isUpdateAllowed($entityValues, $contactId);
    }

    return $remoteValues;
  }

}
