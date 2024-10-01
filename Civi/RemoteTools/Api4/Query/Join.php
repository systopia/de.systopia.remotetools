<?php
/*
 * Copyright (C) 2022 SYSTOPIA GmbH
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

namespace Civi\RemoteTools\Api4\Query;

use Webmozart\Assert\Assert;

/**
 * @phpstan-import-type conditionT from \Civi\RemoteTools\Api4\Query\ConditionInterface
 *
 * @phpstan-type joinTypeT 'INNER'|'LEFT'|'EXCLUDE'
 *
 * @phpstan-type joinT list<string|conditionT>
 *   The above type hint is required because of the workaround used. Otherwise,
 *   the type hint would be:
 *   array{string, joinTypeT, conditionT}|array{string, joinTypeT, string, 3?: conditionT}
 *   The second option is when using a bridge.
 *
 * @see self::generateConditionsWorkaround()
 *
 * @api
 */
final class Join {

  private string $entityName;

  private string $alias;

  /**
   * @phpstan-var joinTypeT
   */
  private string $type;

  private ?string $bridge;

  private ?ConditionInterface $condition;

  /**
   * @phpstan-param joinTypeT $type
   *
   * If a value in a condition is not a field name, it must be enclosed by '"',
   * e.g. '"my_string"'.
   */
  public static function new(string $entityName, string $alias, string $type, ConditionInterface $condition): self {
    return new self($entityName, $alias, $type, NULL, $condition);
  }

  /**
   * @phpstan-param joinTypeT $type
   *
   *  If a value in a condition is not a field name, it must be enclosed by '"',
   *  e.g. '"my_string"'.
   */
  public static function newWithBridge(string $entityName, string $alias, string $type, string $bridge,
    ?ConditionInterface $condition = NULL
  ): self {
    return new self($entityName, $alias, $type, $bridge, $condition);
  }

  /**
   * @phpstan-param joinTypeT $type
   *
   *  If a value in a condition is not a field name, it must be enclosed by '"',
   *  e.g. '"my_string"'.
   */
  public function __construct(string $entityName, string $alias, string $type, ?string $bridge,
    ?ConditionInterface $condition
  ) {
    Assert::notSame([$bridge, $condition], [NULL, NULL], 'At least bridge or condition must not be NULL');

    $this->entityName = $entityName;
    $this->alias = $alias;
    $this->type = $type;
    $this->bridge = $bridge;
    $this->condition = $condition;
  }

  public function getEntityName(): string {
    return $this->entityName;
  }

  public function getAlias(): string {
    return $this->alias;
  }

  /**
   * @phpstan-return joinTypeT
   */
  public function getType(): string {
    return $this->type;
  }

  public function getBridge(): ?string {
    return $this->bridge;
  }

  public function getCondition(): ?ConditionInterface {
    return $this->condition;
  }

  /**
   * @phpstan-return joinT
   */
  public function toArray(): array {
    $join = [$this->entityName . ' AS ' . $this->alias, $this->type];
    if (NULL !== $this->bridge) {
      $join[] = $this->bridge;
      if (NULL !== $this->condition) {
        $join = array_merge($join, self::generateConditionsWorkaround($this->condition));
      }
    }
    else {
      // @phpstan-ignore argument.type
      $join = array_merge($join, self::generateConditionsWorkaround($this->condition));
    }

    return $join;
  }

  /**
   * This is a workaround and (in some cases) necessary because of
   * https://lab.civicrm.org/dev/core/-/issues/5500
   *
   * @phpstan-return list<conditionT>
   */
  private static function generateConditionsWorkaround(ConditionInterface $condition): array {
    if ($condition instanceof CompositeCondition && 'AND' === $condition->getOperator()) {
      return array_map(fn (ConditionInterface $subCondition) => $subCondition->toArray(), $condition->getConditions());
    }

    return [$condition->toArray()];
  }

}
