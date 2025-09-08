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

namespace Civi\RemoteTools\EntityProfile;

use Civi\RemoteTools\Form\FormSpec\FormSpec;
use Civi\RemoteTools\Helper\FilePersisterInterface;

final class EntityProfileFileDecorator extends AbstractRemoteEntityProfileDecorator {

  private FilePersisterInterface $filePersister;

  public function __construct(RemoteEntityProfileInterface $profile, FilePersisterInterface $filePersister) {
    parent::__construct($profile);
    $this->filePersister = $filePersister;
  }

  public function onPreCreate(
    array $arguments,
    array &$entityValues,
    array $entityFields,
    FormSpec $formSpec,
    ?int $contactId
  ): void {
    parent::onPreCreate(
      $arguments,
      $entityValues,
      $entityFields,
      $formSpec,
      $contactId
    );
    $this->handleFiles($entityValues, NULL, $entityFields, $contactId);
  }

  public function onPreUpdate(
    array &$newValues,
    array $oldValues,
    array $entityFields,
    FormSpec $formSpec,
    ?int $contactId
  ): void {
    parent::onPreUpdate(
      $newValues,
      $oldValues,
      $entityFields,
      $formSpec,
      $contactId
    );

    $this->handleFiles($newValues, $oldValues, $entityFields, $contactId);
  }

  /**
   * @phpstan-param array<string, mixed> $entityValues
   * @phpstan-param array<string, mixed>|null $oldValues
   * @phpstan-param array<string, array<string, mixed>> $entityFields
   *     Entity fields indexed by name.
   *
   * @throws \CRM_Core_Exception
   */
  private function handleFiles(array &$entityValues, ?array $oldValues, array $entityFields, ?int $contactId): void {
    foreach ($entityValues as $name => &$value) {
      if (!isset($entityFields[$name]) || !$this->isFileField($entityFields[$name])) {
        continue;
      }

      if ($this->containsPreviousFile($value)) {
        if (NULL !== $oldValues && array_key_exists($name, $oldValues)) {
          $entityValues[$name] = $oldValues[$name];
        }
        else {
          unset($entityValues[$name]);
        }
      }
      // If it is a file field and $value doesn't neither contains a previous
      // file nor a new file, it is either NULL or has been handled by the
      // decorated profile.
      // (The validation prevents invalid values at this point.)
      elseif ($this->containsNewFile($value)) {
        $value = $this->filePersister->persistFileFromForm($value, NULL, $contactId);
      }
    }
  }

  /**
   * @phpstan-param array<string, mixed> $field
   */
  private function isFileField(array $field): bool {
    return 'Integer' === $field['data_type']
      && 'File' === $field['input_type']
      && 'File' === ($field['fk_entity'] ?? NULL);
  }

  /**
   * @phpstan-assert-if-true array{filename: string, content: string} $value
   */
  private function containsNewFile(mixed $value): bool {
    return is_array($value) && is_string($value['filename'] ?? NULL) && is_string($value['content'] ?? NULL);
  }

  /**
   * @phpstan-assert-if-true array{filename: string, url: string} $value
   */
  private function containsPreviousFile(mixed $value): bool {
    // When \Civi\RemoteTools\JsonSchema\FormSpec\Factory\FileFieldFactory gets
    // used the value is the file ID when the file wasn't changed.
    return is_int($value)
      || (is_array($value) && is_string($value['filename'] ?? NULL) && is_string($value['url'] ?? NULL));
  }

}
