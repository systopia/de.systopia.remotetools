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

namespace Civi\RemoteTools\JsonSchema\FormSpec\Factory;

use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\Form\FormSpec\AbstractFormField;
use Civi\RemoteTools\Form\FormSpec\Field\FileField;
use Civi\RemoteTools\Form\FormSpec\FieldDataTransformerInterface;
use Civi\RemoteTools\Helper\FileUrlGeneratorInterface;
use Civi\RemoteTools\JsonSchema\FormSpec\RootFieldJsonSchemaFactoryInterface;
use Civi\RemoteTools\JsonSchema\JsonSchema;
use Civi\RemoteTools\JsonSchema\JsonSchemaFile;
use Webmozart\Assert\Assert;

final class FileFieldFactory extends AbstractFieldJsonSchemaFactory {

  private Api4Interface $api4;

  private FileUrlGeneratorInterface $fileUrlGenerator;

  public static function getPriority(): int {
    return IntegerFieldFactory::getPriority() + 1;
  }

  public function __construct(Api4Interface $api4, FileUrlGeneratorInterface $fileUrlGenerator) {
    $this->api4 = $api4;
    $this->fileUrlGenerator = $fileUrlGenerator;
  }

  protected function doCreateSchema(
    AbstractFormField $field,
    RootFieldJsonSchemaFactoryInterface $factory
  ): JsonSchema {
    Assert::isInstanceOf($field, FileField::class);
    /** @var \Civi\RemoteTools\Form\FormSpec\Field\FileField $field */

    $keywords = [];
    if ($field->isReadOnly()) {
      $keywords['readOnly'] = TRUE;
    }

    if ($field->hasDefaultValue()) {
      if (NULL === $field->getDefaultValue()) {
        $keywords['default'] = NULL;
        if ($field->isReadOnly()) {
          $keywords['const'] = NULL;
        }
      }
      else {
        Assert::integer($field->getDefaultValue());
        $keywords['default'] = JsonSchema::fromArray(
          // @phpstan-ignore method.deprecated, method.deprecated
          $this->buildDefaultValue($field->getDefaultValue(), $field->getFilename(), $field->getUrl())
        );
        // If the field is read only, we cannot use the const keyword, because
        // the URL might contain a hash that depends on the time. This is at least
        // true for the /civicrm/file path.
      }
    }

    $field->prependDataTransformer(
      new class implements FieldDataTransformerInterface {

        public function toEntityValue(
          mixed $data,
          AbstractFormField $field,
          ?array $defaultValuesInList = NULL
        ): mixed {
          if (is_array($data) && isset($data['_id'])) {
            if (!in_array($data['_id'], $defaultValuesInList ?? [$field->getDefaultValue()], TRUE)) {
              // Forbid IDs not previously used to prevent usage of "random" files.
              throw new \InvalidArgumentException(
                'The given file ID ' . print_r($data['_id'], TRUE) . ' does not match the previous one'
              );
            }

            if (!isset($data['content'])) {
              // File is not changed. Schema default value is submitted. Transform to file ID as field value.
              return $data['_id'];
            }
          }

          return $data;
        }

      }
    );

    return new JsonSchemaFile($field->getMaxFileSize(), $keywords, $field->isNullable());
  }

  public function convertDefaultValuesInList(
    AbstractFormField $field,
    array $defaultValues,
    RootFieldJsonSchemaFactoryInterface $factory
  ): array {
    foreach ($defaultValues as $index => $fileId) {
      Assert::nullOrInteger($fileId);
      if (NULL !== $fileId) {
        $defaultValues[$index] = $this->buildDefaultValue($fileId);
      }
    }

    return $defaultValues;
  }

  public function supportsField(AbstractFormField $field): bool {
    return $field instanceof FileField;
  }

  /**
   * @return array<string, mixed>
   *
   * @throws \CRM_Core_Exception
   */
  private function buildDefaultValue(int $fileId, ?string $filename = NULL, ?string $url = NULL): array {
    /** @var array<string, int|string> $file */
    $file = $this->api4->execute('File', 'get', [
      'select' => [
        'uri',
        'file_name',
        'mime_type',
      ],
      'where' => [['id', '=', $fileId]],
    ])->single();

    /** @var string $customFileUploadDir */
    $customFileUploadDir = \CRM_Core_Config::singleton()->customFileUploadDir;

    return [
      '_id' => $fileId,
      'filename' => $filename ?? $file['file_name'],
      'filesize' => filesize($customFileUploadDir . $file['uri']),
      'mimeType' => $file['mime_type'],
      'url' => $url ?? $this->fileUrlGenerator->generateUrl($fileId, 2),
    ];
  }

}
