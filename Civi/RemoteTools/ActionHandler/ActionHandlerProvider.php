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

namespace Civi\RemoteTools\ActionHandler;

use Civi\Api4\Generic\AbstractAction;
use Civi\RemoteTools\Api4\Action\ProfileAwareActionInterface;
use Civi\RemoteTools\DependencyInjection\Compiler\ActionHandlerPass;
use Psr\Container\ContainerInterface;

final class ActionHandlerProvider implements ActionHandlerProviderInterface {

  private ContainerInterface $handlers;

  public function __construct(ContainerInterface $handlers) {
    $this->handlers = $handlers;
  }

  public function get(AbstractAction $action): ?ActionHandlerInterface {
    $key = $this->buildKey($action);

    // @phpstan-ignore-next-line
    return $this->handlers->has($key) ? $this->handlers->get($key) : NULL;
  }

  private function buildKey(AbstractAction $action): string {
    return ActionHandlerPass::buildHandlerKey(
      $action->getEntityName(),
      $action->getActionName(),
      $action instanceof ProfileAwareActionInterface ? $action->getProfileName() : NULL,
    );
  }

}
