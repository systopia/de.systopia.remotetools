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
use Civi\RemoteTools\Exception\ActionHandlerNotFoundException;
use Webmozart\Assert\Assert;

final class DefaultActionHandler implements ActionHandlerInterface {

  private ActionHandlerProviderInterface $actionHandlerProvider;

  public function __construct(ActionHandlerProviderInterface $actionHandlerProvider) {
    $this->actionHandlerProvider = $actionHandlerProvider;
  }

  /**
   * @phpstan-param array{\Civi\Api4\Generic\AbstractAction} $arguments
   *
   * @phpstan-return array<int|string, mixed>|\Civi\Api4\Generic\Result
   *
   * @throws \Civi\RemoteTools\Exception\ActionHandlerNotFoundException
   */
  public function __call(string $name, array $arguments) {
    $action = $arguments[0] ?? NULL;
    Assert::isInstanceOf($action, AbstractAction::class);

    $handler = $this->actionHandlerProvider->get($action);
    if (NULL === $handler) {
      throw new ActionHandlerNotFoundException($action);
    }

    // @phpstan-ignore-next-line
    return call_user_func([$handler, $action->getActionName()], $action);
  }

}
