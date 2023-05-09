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

/**
 * Implementations have to provide methods named after the action name which
 * they want to handle. The methods' argument is the action object. The return
 * value has to be a JSON serializable array or an instance of Result.
 *
 * Custom action handlers should be registered in the service container like
 * this:
 * $container->autowire(MyActionHandler::class)
 *   ->addTag(MyActionHandler::SERVICE_TAG, ['entity_name' => 'MyRemoteEntity', 'profile_name' => 'my_profile']);
 *
 * The attribute "profile_name" is optional and has to be left out, if the
 * corresponding action classes are not profile aware. The tag can be added
 * multiple times with different combinations of "entity_name" and
 * "profile_name".
 *
 * @see \Civi\Api4\Generic\Result
 * @see \Civi\RemoteTools\DependencyInjection\Compiler\ActionHandlerPass
 * @see \Civi\RemoteTools\Api4\Action\ProfileAwareActionInterface
 */
interface ActionHandlerInterface {

  public const SERVICE_TAG = 'remote_tools.action.handler';

}
