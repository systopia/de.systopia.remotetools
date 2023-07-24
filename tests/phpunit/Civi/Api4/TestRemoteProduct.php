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

namespace Civi\Api4;

use Civi\Remoteactivity\Api4\Permissions;
use Civi\RemoteTools\Api4\AbstractRemoteEntity;

final class TestRemoteProduct extends AbstractRemoteEntity {

  /**
   * @inheritDoc
   *
   * @return array<string, array<string|string[]>>
   */
  public static function permissions(): array {
    return [
      'meta' => ['access TestRemoteProduct'],
      'default' => ['access TestRemoteProduct'],
    ];
  }

}
