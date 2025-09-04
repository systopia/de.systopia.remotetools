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

/**
 * @codeCoverageIgnore
 */
final class FileUrlGenerator implements FileUrlGeneratorInterface {

  public function generateUrl(int $fileId, ?int $lifetimeHours): string {
    // @phpstan-ignore function.alreadyNarrowedType
    if (!method_exists(\Civi::class, 'url')) {
      throw new \BadMethodCallException('UrlGenerator can not be used with this version of CiviCRM');
    }

    $hash = \CRM_Core_BAO_File::generateFileHash(NULL, $fileId, \CRM_Utils_Time::time(), $lifetimeHours);

    return \Civi::url("frontend://civicrm/file?reset=1&id=$fileId&fcs=$hash", 'a')->__toString();
  }

}
