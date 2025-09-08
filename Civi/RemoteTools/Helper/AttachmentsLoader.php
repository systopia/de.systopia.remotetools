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

use Civi\RemoteTools\Api4\Api4Interface;

final class AttachmentsLoader implements AttachmentsLoaderInterface {

  private Api4Interface $api4;

  private DaoEntityInfoProvider $daoEntityInfoProvider;

  public function __construct(
    Api4Interface $api4,
    DaoEntityInfoProvider $daoEntityInfoProvider
  ) {
    $this->api4 = $api4;
    $this->daoEntityInfoProvider = $daoEntityInfoProvider;
  }

  /**
   * @inheritDoc
   */
  public function getAttachments(string $entityName, int $entityId): array {
    $files = $this->api4->execute('File', 'get', [
      'select' => ['id', 'file_name', 'description'],
      'join' => [
        [
          'EntityFile AS entity_file',
          'INNER',
          ['entity_file.file_id', '=', 'id'],
          ['entity_file.entity_table', '=', '"' . $this->daoEntityInfoProvider->getTableName($entityName) . '"'],
          ['entity_file.entity_id', '=', $entityId],
        ],
      ],
    ])->getArrayCopy();

    $attachments = [];
    /** @phpstan-var array{id: int, description: string|null} $file */
    foreach ($files as $file) {
      $attachments[] = ['file' => $file['id'], 'description' => $file['description']];
    }

    return $attachments;
  }

}
