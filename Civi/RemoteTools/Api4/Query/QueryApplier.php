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

namespace Civi\RemoteTools\Api4\Query;

use Civi\Api4\Generic\Result;
use Civi\Api4\Generic\Traits\ArrayQueryActionTrait;

/**
 * Allows to apply the query properties from an action to an array.
 */
final class QueryApplier {

  use ArrayQueryActionTrait;

  private int $limit = 0;

  private int $offset = 0;

  /**
   * @phpstan-var array<string>
   */
  private array $orderBy = [];

  /**
   * @phpstan-var array<array{string, string|mixed[], 2?: mixed}>
   */
  private array $where = [];

  /**
   * @phpstan-var array<string>
   */
  private array $select = [];

  public static function new(): self {
    return new static();
  }

  /**
   * @phpstan-param array<array<string, mixed>> $values
   */
  public function apply(array $values): Result {
    $result = new Result();
    if (in_array('*', $this->select, TRUE)) {
      // ArrayQueryActionTrait does not handle "*" as select field name.
      $this->select = array_merge($this->select, $this->getStandardFieldNames($values));
    }
    $this->queryArray($values, $result);

    return $result;
  }

  public function getLimit(): int {
    return $this->limit;
  }

  public function setLimit(int $limit): self {
    $this->limit = $limit;

    return $this;
  }

  public function getOffset(): int {
    return $this->offset;
  }

  public function setOffset(int $offset): self {
    $this->offset = $offset;

    return $this;
  }

  /**
   * @phpstan-return array<string>
   */
  public function getOrderBy(): array {
    return $this->orderBy;
  }

  /**
   * @phpstan-param array<string> $orderBy
   */
  public function setOrderBy(array $orderBy): self {
    $this->orderBy = $orderBy;

    return $this;
  }

  /**
   * @phpstan-return array<string>
   */
  public function getSelect(): array {
    return $this->select;
  }

  /**
   * @phpstan-return array<array{string, string|mixed[], 2?: mixed}>
   */
  public function getWhere(): array {
    return $this->where;
  }

  /**
   * @phpstan-param array<array{string, string|mixed[], 2?: mixed}> $where
   */
  public function setWhere(array $where): self {
    $this->where = $where;

    return $this;
  }

  /**
   * @phpstan-param array<string> $select
   */
  public function setSelect(array $select): self {
    $this->select = $select;

    return $this;
  }

  /**
   * @phpstan-param array<array<string, mixed>> $values
   *
   * @phpstan-return array<string>
   *   All field names that are not names of joined fields or pseudo constants.
   *   This method assumes that all entries in $values have the same array keys.
   */
  private function getStandardFieldNames(array $values): array {
    return array_filter(
      // @phpstan-ignore-next-line
      array_keys(reset($values) ?: []),
      fn (string $fieldName) => !str_contains('.', $fieldName) && !str_contains(':', $fieldName)
    );
  }

}
