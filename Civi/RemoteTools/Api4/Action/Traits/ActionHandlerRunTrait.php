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

use Civi\Api4\Generic\Result;
use Civi\RemoteTools\Api4\Util\ResultUtil;

trait ActionHandlerRunTrait {

  use ActionHandlerTrait;

  public function _run(Result $result): void {
    $this->doRun($result);
  }

  protected function doRun(Result $result): void {
    $handlerResult = $this->getHandlerResult();
    if ($handlerResult instanceof Result) {
      ResultUtil::copy($handlerResult, $result);
    }
    else {
      $result->exchangeArray($handlerResult);
    }
  }

}
