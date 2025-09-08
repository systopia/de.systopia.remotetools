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
 * @phpstan-import-type fileT from FilePersisterInterface
 * @phpstan-type attachmentT array{file: fileT|int, description: string|null}
 *   "file" contains the new file or the ID of the previous, unchanged file.
 *
 * @see \Civi\RemoteTools\Form\FormSpec\Field\AttachmentsField
 * @see \Civi\RemoteTools\Helper\AttachmentsLoaderInterface
 *
 * @apiService
 */
interface AttachmentsPersisterInterface {

  /**
   * @phpstan-param list<attachmentT> $attachments
   *   Submitted data from AttachmentsField.
   *
   * @throws \CRM_Core_Exception
   */
  public function persistAttachmentsFromForm(
    string $entityName,
    int $entityId,
    array $attachments,
    ?int $contactId
  ): void;

}
