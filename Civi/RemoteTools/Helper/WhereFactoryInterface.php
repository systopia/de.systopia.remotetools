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

/**
 * @phpstan-import-type conditionT from \Civi\RemoteTools\Api4\Query\ConditionInterface
 */
interface WhereFactoryInterface {

  /**
   * phpcs:disable Generic.Files.LineLength.TooLong
   *
   * @phpstan-param list<conditionT> $where
   * @phpstan-param array<string, array<string, mixed>> $entityFields
   * @phpstan-param array<string, array<string, mixed>> $remoteFields
   * @phpstan-param callable(string $fieldName, string $joinedFieldName): bool $implicitJoinAllowedCallback
   * @phpstan-param callable(\Civi\RemoteTools\Api4\Query\Comparison $comparison): ?\Civi\RemoteTools\Api4\Query\ConditionInterface $convertRemoteFieldComparisonCallback
   *
   * @phpstan-return list<conditionT>
   *
   * phpcs:enable
   * }
   */
  public function getWhere(
    array $where,
    array $entityFields,
    array $remoteFields,
    callable $implicitJoinAllowedCallback,
    callable $convertRemoteFieldComparisonCallback
  ): array;

}
