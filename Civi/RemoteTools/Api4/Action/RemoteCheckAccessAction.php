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

use Civi\Api4\Generic\AbstractAction;
use Civi\RemoteTools\Api4\Action\Traits\ProfileParameterTrait;

/**
 * @api
 *
 * phpcs:disable Generic.Files.LineLength.TooLong
 */
final class RemoteCheckAccessAction extends AbstractRemoteCheckAccessAction implements ProfileAwareRemoteActionInterface {
// phpcs:enable
  use ProfileParameterTrait;

  protected function createApiActionToCheck(): AbstractAction {
    $apiAction = parent::createApiActionToCheck();

    if ($apiAction->paramExists('profile')) {
      // @phpstan-ignore-next-line
      $apiAction->setProfile($this->profile);
    }

    return $apiAction;
  }

}
