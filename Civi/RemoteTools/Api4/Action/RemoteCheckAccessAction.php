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

use Civi\Api4\Generic\CheckAccessAction;
use Civi\Api4\Generic\Result;
use Civi\RemoteTools\Api4\Action\Traits\ActionHandlerRunTrait;
use Civi\RemoteTools\Api4\Action\Traits\ProfileParameterTrait;
use Civi\RemoteTools\Api4\Action\Traits\RemoteContactIdParameterOptionalTrait;
use Civi\RemoteTools\Api4\Action\Traits\ResolvedContactIdTrait;
use Civi\RemoteTools\Exception\ActionHandlerNotFoundException;

final class RemoteCheckAccessAction extends CheckAccessAction implements ProfileAwareRemoteActionInterface {

  use ActionHandlerRunTrait;

  use ProfileParameterTrait;

  use RemoteContactIdParameterOptionalTrait;

  use ResolvedContactIdTrait;

  public function _run(Result $result): void {
    parent::_run($result);
    if (($result->first()['granted'] ?? NULL) === TRUE) {
      try {
        $this->doRun($result);
      }
      // @phpstan-ignore-next-line
      catch (ActionHandlerNotFoundException $e) {
        // @ignoreException Allow access if there's no action handler.
      }
    }
  }

}
