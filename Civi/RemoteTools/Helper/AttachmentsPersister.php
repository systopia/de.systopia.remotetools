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

use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\Api4\Query\Comparison;
use Civi\RemoteTools\Api4\Query\CompositeCondition;

final class AttachmentsPersister implements AttachmentsPersisterInterface {

  private Api4Interface $api4;

  private DaoEntityInfoProvider $daoEntityInfoProvider;

  private FilePersisterInterface $filePersister;

  public function __construct(
    Api4Interface $api4,
    DaoEntityInfoProvider $daoEntityInfoProvider,
    FilePersisterInterface $filePersister
  ) {
    $this->api4 = $api4;
    $this->daoEntityInfoProvider = $daoEntityInfoProvider;
    $this->filePersister = $filePersister;
  }

  /**
   * @inheritDoc
   */
  public function persistAttachmentsFromForm(
    string $entityName,
    int $entityId,
    array $attachments,
    ?int $contactId
  ): void {
    /** @var list<int> $previousFileIds */
    $previousFileIds = $this->api4->getEntities('EntityFile', CompositeCondition::fromFieldValuePairs([
      'entity_table' => $this->daoEntityInfoProvider->getTableName($entityName),
      'entity_id' => $entityId,
    ]))->column('file_id');
    $newFileIds = [];

    foreach ($attachments as $attachment) {
      if (is_int($attachment['file'])) {
        $this->api4->updateEntity('File', $attachment['file'], ['description' => $attachment['description']]);
        $newFileIds[] = $attachment['file'];
      }
      else {
        $fileId = $this->filePersister->persistFileFromForm(
          $attachment['file'],
          $attachment['description'],
          $contactId
        );
        $this->api4->createEntity('EntityFile', [
          'entity_table' => $this->daoEntityInfoProvider->getTableName($entityName),
          'entity_id' => $entityId,
          'file_id' => $fileId,
        ])->single();
        $newFileIds[] = $fileId;
      }
    }

    /** @var list<int> $deletedFileIds */
    $deletedFileIds = array_diff($previousFileIds, $newFileIds);
    if ([] !== $deletedFileIds) {
      $this->api4->deleteEntities('EntityFile', Comparison::new('file_id', 'IN', $deletedFileIds));
      $this->api4->deleteEntities('File', Comparison::new('id', 'IN', $deletedFileIds));
    }
  }

}
