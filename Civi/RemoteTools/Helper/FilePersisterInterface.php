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

namespace Civi\RemoteTools\Helper;

/**
 * @apiService
 */
interface FilePersisterInterface {

  /**
   * Requires an open database transaction.
   *
   * @returns int ID of File entity.
   *
   * @throws \CRM_Core_Exception
   */
  public function persistFile(string $filename, string $content, ?string $description, ?int $contactId): int;

  /**
   * Requires an open database transaction.
   *
   * @phpstan-param array{filename: string, content: string} $file
   *   The property 'content' contains the Base64 encoded file.
   *
   * @returns int ID of File entity.
   *
   * @throws \CRM_Core_Exception
   */
  public function persistFileFromForm(array $file, ?string $description, ?int $contactId): int;

}
