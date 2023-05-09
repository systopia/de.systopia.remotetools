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

/**
 * Custom implementations should be registered in the service container like
 * this:
 * $container->autowire(MyActionHandlerProvider::class)
 *   ->addTag(MyActionHandlerProvider::SERVICE_TAG);
 *
 * Optionally a priority can be defined with the tag attribute "priority".
 * (Default is 0.)
 */
interface ActionHandlerProviderInterface {

  public const SERVICE_TAG = 'remote_tools.action.handler_provider';

  public function get(AbstractAction $action): ?ActionHandlerInterface;

}
