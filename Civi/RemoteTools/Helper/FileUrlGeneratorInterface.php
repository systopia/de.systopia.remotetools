<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
 *
 *  This program is free software: you can redistribute it and/or modify it under
 *  the terms of the GNU Affero General Public License as published by the Free
 *  Software Foundation, either version 3 of the License, or (at your option) any
 *  later version.
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

namespace Civi\RemoteTools\Helper;

interface FileUrlGeneratorInterface {

  /**
   * Requires CiviCRM >= 6.1.0.
   *
   * @param int $fileId
   * @param int|null $lifetimeHours
   *   The number of hours the link shall be valid. If NULL, the checksum
   *   timeout configured in CiviCRM will be used.
   */
  public function generateUrl(int $fileId, ?int $lifetimeHours): string;

}
