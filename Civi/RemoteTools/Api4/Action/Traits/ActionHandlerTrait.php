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

namespace Civi\RemoteTools\Api4\Action\Traits;

use Civi\RemoteTools\ActionHandler\ActionHandlerInterface;
use Civi\RemoteTools\Exception\ActionHandlerNotFoundException;

trait ActionHandlerTrait {

  protected ActionHandlerInterface $_actionHandler;

  /**
   * @var bool
   *   If TRUE no exception will be thrown in getHandlerResult() if no action
   *   handler is found and an empty array returned instead.
   */
  protected bool $_ignoreMissingActionHandler = FALSE;

  public function __construct(
          string $entityName,
          string $actionName,
          ActionHandlerInterface $actionHandler = NULL
  ) {
    parent::__construct($entityName, $actionName);
    $this->initActionHandler($actionHandler);
  }

  protected function initActionHandler(?ActionHandlerInterface $actionHandler): void {
    // @phpstan-ignore-next-line
    $this->_actionHandler = $actionHandler ?? \Civi::service(ActionHandlerInterface::class);
  }

  /**
   * @phpstan-return array<int|string, mixed>|\Civi\Api4\Generic\Result
   *
   * @throws \Civi\RemoteTools\Exception\ActionHandlerNotFoundException
   *   If no exception handler is found and attribute
   *   _ignoreMissingActionHandler is FALSE.
   */
  protected function getHandlerResult() {
    if (!isset($this->_actionHandler)) {
      // @phpstan-ignore-next-line
      $this->_actionHandler = \Civi::service(ActionHandlerInterface::class);
    }

    if (!$this->_ignoreMissingActionHandler) {
      // @phpstan-ignore-next-line
      return $this->_actionHandler->{$this->getActionName()}($this);
    }

    try {
      // @phpstan-ignore-next-line
      return $this->_actionHandler->{$this->getActionName()}($this);
    }
    catch (ActionHandlerNotFoundException $e) {
      return [];
    }
  }

}
