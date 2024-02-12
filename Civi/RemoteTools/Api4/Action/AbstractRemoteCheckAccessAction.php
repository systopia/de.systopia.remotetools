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

namespace Civi\RemoteTools\Api4\Action;

use Civi\API\Exception\UnauthorizedException;
use Civi\API\Request;
use Civi\Api4\Generic\AbstractAction;
use Civi\Api4\Generic\CheckAccessAction;
use Civi\Api4\Generic\Result;
use Civi\RemoteTools\Api4\Action\Traits\ActionHandlerRunTrait;
use Civi\RemoteTools\Api4\Action\Traits\RemoteContactIdParameterOptionalTrait;
use Civi\RemoteTools\Api4\Action\Traits\ResolvedContactIdOptionalTrait;
use Civi\RemoteTools\Exception\ActionHandlerNotFoundException;

/**
 * @api
 */
abstract class AbstractRemoteCheckAccessAction extends CheckAccessAction implements RemoteActionInterface {

  use ActionHandlerRunTrait;

  use RemoteContactIdParameterOptionalTrait;

  use ResolvedContactIdOptionalTrait;

  public function _run(Result $result): void {
    $authorized = $this->isActionAuthorized();
    $result->exchangeArray([['access' => $authorized]]);

    if ($authorized) {
      try {
        $this->doRun($result);
      }
      // @phpstan-ignore-next-line
      catch (ActionHandlerNotFoundException $e) {
        // @ignoreException Allow access if there's no action handler.
      }
    }
  }

  protected function isActionAuthorized(): bool {
    // Prevent circular checks
    if ($this->action === 'checkAccess') {
      return TRUE;
    }

    $apiAction = $this->createApiActionToCheck();
    /** @var \Civi\API\Kernel $kernel */
    $kernel = \Civi::service('civi_api_kernel');
    try {
      // @phpstan-ignore-next-line resolve() has AbstractAction missing in type hint.
      [$actionObjectProvider] = $kernel->resolve($apiAction);
      // @phpstan-ignore-next-line authorize() has AbstractAction missing in type hint.
      $kernel->authorize($actionObjectProvider, $apiAction);
    }
    catch (UnauthorizedException $e) {
      return FALSE;
    }

    return TRUE;
  }

  protected function createApiActionToCheck(): AbstractAction {
    /** @var \Civi\Api4\Generic\AbstractAction $apiAction */
    $apiAction = Request::create($this->getEntityName(), $this->action, ['version' => 4]);

    if (NULL !== $this->remoteContactId && $apiAction->paramExists('remoteContactId')) {
      // @phpstan-ignore-next-line
      $apiAction->setRemoteContactId($this->remoteContactId);
    }

    return $apiAction;
  }

}
