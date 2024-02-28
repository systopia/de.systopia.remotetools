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

namespace Civi\RemoteTools\Api4;

use Civi\API\Request;
use Civi\Api4\Generic\AbstractAction;
use Civi\Api4\Generic\Result;
use Civi\RemoteTools\Api4\Query\ConditionInterface;
use Webmozart\Assert\Assert;

/**
 * @codeCoverageIgnore
 */
final class Api4 implements Api4Interface {

  private static self $instance;

  public static function getInstance(): self {
    return self::$instance ?? new self();
  }

  public function __construct() {
    self::$instance = $this;
  }

  public function countEntities(string $entityName, ConditionInterface $where, array $options): int {
    return $this->execute($entityName, 'get', [
      'checkPermissions' => $options['checkPermissions'] ?? TRUE,
      'select' => ['row_count'],
      'where' => [$where->toArray()],
    ])->countMatched();
  }

  /**
   * @inheritDoc
   */
  public function createAction(string $entityName, string $action, array $params = []): AbstractAction {
    if (isset($params['version'])) {
      Assert::same($params['version'], 4);
    }
    else {
      $params['version'] = 4;
    }

    $params = array_map(fn ($value) => $value instanceof ApiParameterInterface ? $value->toParam() : $value, $params);

    return Request::create($entityName, $action, $params);
  }

  /**
   * @inheritDoc
   */
  public function createEntity(string $entityName, array $values, array $options = []): Result {
    return $this->execute($entityName, 'create', [
      'checkPermissions' => $options['checkPermissions'] ?? TRUE,
      'values' => $values,
    ]);
  }

  /**
   * @inheritDoc
   */
  public function createGetAction(string $entityName): AbstractAction {
    // @phpstan-ignore-next-line Does not return AbstractGetAction.
    return $this->createAction($entityName, 'get');
  }

  public function deleteEntity(string $entityName, int $id, array $options = []): Result {
    return $this->execute($entityName, 'delete', [
      'checkPermissions' => $options['checkPermissions'] ?? TRUE,
      'where' => [['id', '=', $id]],
    ]);
  }

  /**
   * @inheritDoc
   */
  public function executeAction(AbstractAction $action): Result {
    return $action->execute();
  }

  /**
   * @inheritDoc
   */
  public function execute(string $entityName, string $actionName, array $params = []): Result {
    return $this->createAction($entityName, $actionName, $params)->execute();
  }

  /**
   * @inheritDoc
   */
  public function getEntities(
    string $entityName,
    ?ConditionInterface $where = NULL,
    array $orderBy = [],
    int $limit = 0,
    int $offset = 0,
    array $extraParams = []
  ): Result {
    return $this->execute($entityName, 'get', [
      'where' => NULL === $where ? [] : [$where->toArray()],
      'orderBy' => $orderBy,
      'limit' => $limit,
      'offset' => $offset,
    ] + $extraParams);
  }

  public function updateEntity(string $entityName, int $id, array $values, array $options = []): Result {
    return $this->execute($entityName, 'update', [
      'checkPermissions' => $options['checkPermissions'] ?? TRUE,
      'where' => [['id', '=', $id]],
      'values' => $values,
    ]);
  }

}
