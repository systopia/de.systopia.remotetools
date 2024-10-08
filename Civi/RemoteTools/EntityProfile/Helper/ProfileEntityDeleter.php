<?php
/*
 * Copyright (C) 2024 SYSTOPIA GmbH
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

namespace Civi\RemoteTools\EntityProfile\Helper;

use Civi\RemoteTools\Api4\Action\RemoteDeleteAction;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\Api4\Query\Comparison;
use Civi\RemoteTools\Api4\Query\Join;
use Civi\RemoteTools\EntityProfile\RemoteEntityProfileInterface;
use Civi\RemoteTools\Helper\WhereFactoryInterface;
use Webmozart\Assert\Assert;

/**
 * @phpstan-type comparisonT array{string, string, 2?: scalar|array<scalar>}
 * Actually this should be: array{string, array<int, comparisonT|compositeConditionT>},
 * so that is not possible.
 * @phpstan-type compositeConditionT array{string, array<int, array<int, mixed>>}
 */
final class ProfileEntityDeleter implements ProfileEntityDeleterInterface {

  private Api4Interface $api4;

  private WhereFactoryInterface $whereFactory;

  public function __construct(
    Api4Interface $api4,
    WhereFactoryInterface $whereFactory
  ) {
    $this->api4 = $api4;
    $this->whereFactory = $whereFactory;
  }

  public function delete(RemoteEntityProfileInterface $profile, RemoteDeleteAction $action): array {
    Assert::eq($action->getOffset(), 0, 'Offset is not allowed in delete action');

    /** @phpstan-var array<string, array<string, mixed>> $entityFields */
    $entityFields = $this->api4->execute($profile->getEntityName(), 'getFields', [
      'checkPermissions' => $profile->isCheckApiPermissions($action->getResolvedContactId()),
    ])->indexBy('name')->getArrayCopy();
    $remoteFields = $profile->getRemoteFields($entityFields, $action->getResolvedContactId());

    $join = array_map(
      fn (Join $join) => $join->toArray(),
      $profile->getJoins('delete', $action->getResolvedContactId())
    );
    $where = $this->createWhereForDelete($profile, $action, $entityFields, $remoteFields);

    $getResult = $this->api4->execute($profile->getEntityName(), 'get', [
      'select' => $profile->getSelectFieldNames(['*'], 'delete', [], $action->getResolvedContactId()),
      'join' => $join,
      'where' => $where,
      'checkPermissions' => $profile->isCheckApiPermissions($action->getResolvedContactId()),
    ]);

    $deleteCount = 0;
    $result = [];
    /** @phpstan-var array<string, mixed>&array{id: int} $entityValues */
    foreach ($getResult as $entityValues) {
      $grantResult = $profile->isDeleteGranted($entityValues, $action->getResolvedContactId());
      if ($grantResult->granted) {
        $result = array_merge(
          $result,
          $this->api4->deleteEntity(
            $profile->getEntityName(),
            $entityValues['id'],
            ['checkPermissions' => $profile->isCheckApiPermissions($action->getResolvedContactId())]
          )->getArrayCopy(),
        );
        ++$deleteCount;
        if ($deleteCount === $action->getLimit()) {
          break;
        }
      }
    }

    /** @phpstan-var array<array<string, mixed>> $result */
    return $result;
  }

  /**
   * @phpstan-param array<array<string, mixed>> $entityFields
   * @phpstan-param array<array<string, mixed>> $remoteFields
   *
   * @phpstan-return array<comparisonT|compositeConditionT>
   */
  private function createWhereForDelete(
    RemoteEntityProfileInterface $profile,
    RemoteDeleteAction $action,
    array $entityFields,
    array $remoteFields
  ): array {
    $implicitJoinAllowedCallback = fn(string $fieldName, string $joinField)
    => $profile->isImplicitJoinAllowed($fieldName, $joinField, $action->getResolvedContactId());
    $convertRemoteComparisonCallback = fn(Comparison $comparison)
    => $profile->convertRemoteFieldComparison($comparison, $action->getResolvedContactId());

    $where = $this->whereFactory->getWhere(
      $action->getWhere(),
      $entityFields,
      $remoteFields,
      $implicitJoinAllowedCallback,
      $convertRemoteComparisonCallback,
    );

    $filter = $profile->getFilter('delete', $action->getResolvedContactId());
    if (NULL !== $filter) {
      $where[] = $filter->toArray();
    }

    return $where;
  }

}
