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

use Civi\Core\Transaction\Manager as TransactionManager;
use Civi\RemoteTools\Api4\Api4Interface;
use Symfony\Component\Mime\MimeTypeGuesserInterface;

final class FilePersister implements FilePersisterInterface {

  private Api4Interface $api4;

  private \CRM_Core_Config $config;

  private MimeTypeGuesserInterface $mimeTypeGuesser;

  private TransactionManager $transactionManager;

  public function __construct(
    Api4Interface $api4,
    \CRM_Core_Config $config,
    MimeTypeGuesserInterface $mimeTypeGuesser,
    TransactionManager $transactionManager
  ) {
    $this->api4 = $api4;
    $this->config = $config;
    $this->mimeTypeGuesser = $mimeTypeGuesser;
    $this->transactionManager = $transactionManager;
  }

  public function persistFile(string $filename, string $content, ?string $description, ?int $contactId): int {
    $safeFilename = \CRM_Utils_File::makeFileName($filename, TRUE);
    /** @var string $customFileUploadDir */
    // Since CiviCRM 6.1 this property is type hinted, so this can be reduced to a single line sometime in the future.
    $customFileUploadDir = $this->config->customFileUploadDir;
    $filePath = $customFileUploadDir . $safeFilename;

    $this->transactionManager->getBaseFrame()->addCallback(
      \CRM_Core_Transaction::PHASE_PRE_ROLLBACK,
      fn() => !file_exists($filePath) || @unlink($filePath)
    );

    file_put_contents($filePath, $content);
    $fileValues = $this->api4->createEntity('File', [
      'uri' => $safeFilename,
      'mime_type' => $this->mimeTypeGuesser->guessMimeType($filePath) ?? 'application/octet-stream',
      'created_id' => $contactId,
      'upload_date' => date('Y-m-d H:i:s'),
      'description' => $description,
    ])->single();

    return $fileValues['id'];
  }

  public function persistFileFromForm(array $file, ?string $description, ?int $contactId): int {
    $content = base64_decode($file['content'], TRUE);
    if (FALSE === $content) {
      throw new \InvalidArgumentException('Invalid file content');
    }

    return $this->persistFile($file['filename'], $content, $description, $contactId);
  }

}
