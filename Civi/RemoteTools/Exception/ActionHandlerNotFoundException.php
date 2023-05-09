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

namespace Civi\RemoteTools\Exception;

use Civi\Api4\Generic\AbstractAction;
use Civi\RemoteTools\Api4\Action\ProfileAwareActionInterface;

final class ActionHandlerNotFoundException extends \RuntimeException implements ExceptionInterface {

  private AbstractAction $action;

  public function __construct(
          AbstractAction $action,
          string $message = NULL,
          int $code = 0,
          ?\Throwable $previous = NULL
  ) {
    parent::__construct($message ?? $this->buildMessage($action), $code, $previous);
    $this->action = $action;
  }

  public function getAction(): AbstractAction {
    return $this->action;
  }

  private function buildMessage(AbstractAction $action): string {
    $message = sprintf('No action handler found for %s.%s', $action->getEntityName(), $action->getActionName());
    if ($action instanceof ProfileAwareActionInterface) {
      $message .= sprintf(' (profile: %s)', $action->getProfileName());
    }

    return $message;
  }

}
