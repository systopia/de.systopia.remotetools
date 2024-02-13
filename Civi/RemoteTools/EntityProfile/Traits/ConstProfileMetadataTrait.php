<?php
/*
 * Copyright (C) 2024 SYSTOPIA GmbH
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

namespace Civi\RemoteTools\EntityProfile\Traits;

/**
 * Trait that can be used in implementations of RemoteEntityProfileInterface
 * that define the metadata in constants.
 *
 * @see \Civi\RemoteTools\EntityProfile\RemoteEntityProfileInterface
 *
 * @api
 */
trait ConstProfileMetadataTrait {

  public function getEntityName(): string {
    return static::ENTITY_NAME;
  }

  public function getName(): string {
    return static::NAME;
  }

  public function getRemoteEntityName(): string {
    return static::REMOTE_ENTITY_NAME;
  }

}
