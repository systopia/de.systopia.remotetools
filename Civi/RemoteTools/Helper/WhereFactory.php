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

use Civi\RemoteTools\Api4\Query\Comparison;
use Civi\RemoteTools\Api4\Util\FieldUtil;

final class WhereFactory implements WhereFactoryInterface {

  /**
   * @inheritDoc
   *
   * phpcs:disable Generic.Metrics.CyclomaticComplexity.TooHigh
   */
  public function getWhere(
    // phpcs:enable
    array $where,
    array $entityFields,
    array $remoteFields,
    callable $implicitJoinAllowedCallback,
    callable $convertRemoteFieldComparisonCallback
  ): array {
    $entityAndRemoteFieldNames = array_intersect(array_keys($entityFields), array_keys($remoteFields));
    $newWhere = [];
    foreach ($where as $clause) {
      if (is_string($clause[1])) {
        $fieldName = $clause[0];
        [$fieldNameWithoutSuffix, $suffix] = FieldUtil::splitOptionListSuffix($fieldName);
        if (in_array($fieldNameWithoutSuffix, $entityAndRemoteFieldNames, TRUE)) {
          if (NULL === $suffix || FieldUtil::isValidSuffix($suffix, $entityFields[$fieldNameWithoutSuffix])) {
            $newWhere[] = $clause;
          }
        }
        elseif (isset($remoteFields[$fieldName])) {
          if (FieldUtil::getJoinedFieldName($fieldName, $entityFields) !== NULL) {
            // Joined field was explicitly added to remote fields.
            $newWhere[] = $clause;
          }

          // @phpstan-ignore-next-line
          $convertedCondition = $convertRemoteFieldComparisonCallback(Comparison::new(...$clause));
          if (NULL !== $convertedCondition) {
            $newWhere[] = $convertedCondition->toArray();
          }
        }
        elseif (!isset($entityFields[$fieldNameWithoutSuffix])) {
          $joinedField = FieldUtil::getJoinedFieldName($fieldName, $entityFields);
          if (NULL !== $joinedField && isset($remoteFields[$joinedField])
            && $implicitJoinAllowedCallback($fieldName, $joinedField)) {
            $newWhere[] = $clause;
          }
        }
      }
      elseif (is_array($clause[1])) {
        $subClauses = self::getWhere(
          // @phpstan-ignore-next-line
          $clause[1],
          $entityFields,
          $remoteFields,
          $implicitJoinAllowedCallback,
          $convertRemoteFieldComparisonCallback
        );
        if ([] !== $subClauses) {
          $newWhere[] = [$clause[0], $subClauses];
        }
      }
    }

    return $newWhere;
  }

}
