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

namespace Civi\RemoteTools\Helper;

use Civi\RemoteTools\Api4\Util\FieldUtil;

final class SelectFactory implements SelectFactoryInterface {

  /**
   * @inheritDoc
   */
  public function getSelects(
    array $select,
    array $entityFields,
    array $remoteFields,
    callable $implicitJoinAllowedCallback
  ): array {
    if ([] === $select || in_array('*', $select, TRUE)) {
      $rowCountSelected = in_array('row_count', $select, TRUE);
      $select = array_filter(
        array_keys($remoteFields),
        fn (string $fieldName) => !FieldUtil::isJoinedField($fieldName),
      );
      if ($rowCountSelected) {
        $select[] = 'row_count';
      }
    }

    $entitySelect = [];
    $remoteSelect = [];
    $entityAndRemoteFieldNames = array_intersect(array_keys($entityFields), array_keys($remoteFields));
    $entityAndRemoteFieldNames[] = 'row_count';
    foreach ($select as $fieldName) {
      [$fieldNameWithoutSuffix, $suffix] = FieldUtil::splitOptionListSuffix($fieldName);
      if (in_array($fieldNameWithoutSuffix, $entityAndRemoteFieldNames, TRUE)) {
        if (NULL === $suffix || FieldUtil::isValidSuffix($suffix, $entityFields[$fieldNameWithoutSuffix])) {
          $entitySelect[] = $fieldName;
          $remoteSelect[] = $fieldName;
        }
      }
      elseif (isset($remoteFields[$fieldName])) {
        $remoteSelect[] = $fieldName;
        if (FieldUtil::getJoinedFieldName($fieldName, $entityFields) !== NULL) {
          // Joined field was explicitly added to remote fields.
          $entitySelect[] = $fieldName;
        }
      }
      elseif (!isset($entityFields[$fieldNameWithoutSuffix])) {
        $joinedField = FieldUtil::getJoinedFieldName($fieldName, $entityFields);
        if (NULL !== $joinedField && isset($remoteFields[$joinedField])
          && $implicitJoinAllowedCallback($fieldName, $joinedField)) {
          $entitySelect[] = $fieldName;
          $remoteSelect[] = $fieldName;
        }
      }
    }

    return ['entity' => $entitySelect, 'remote' => $remoteSelect];
  }

}
