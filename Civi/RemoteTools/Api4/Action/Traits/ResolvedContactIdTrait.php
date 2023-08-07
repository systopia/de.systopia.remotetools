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

use Civi\RemoteTools\RequestContext\RequestContextInterface;

/**
 * @see \Civi\RemoteTools\Api4\Action\Traits\RemoteContactIdParameterTrait
 */
trait ResolvedContactIdTrait {

  protected ?RequestContextInterface $_requestContext = NULL;

  public function getResolvedContactId(): int {
    // @phpstan-ignore-next-line
    $this->_requestContext ??= \Civi::service(RequestContextInterface::class);

    // @phpstan-ignore-next-line
    return $this->_requestContext->getResolvedContactId();
  }

}
