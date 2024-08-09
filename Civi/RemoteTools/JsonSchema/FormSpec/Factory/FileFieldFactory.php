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

use Civi\RemoteTools\Form\FormSpec\AbstractFormField;
use Civi\RemoteTools\Form\FormSpec\Field\FileField;
use Civi\RemoteTools\JsonSchema\JsonSchema;
use Civi\RemoteTools\JsonSchema\JsonSchemaNull;
use Civi\RemoteTools\JsonSchema\JsonSchemaObject;
use Civi\RemoteTools\JsonSchema\JsonSchemaString;
use Civi\RemoteTools\Util\ArrayUtil;
use Civi\RemoteTools\Util\FormatUtil;
use CRM_Remotetools_ExtensionUtil as E;
use Webmozart\Assert\Assert;

/**
 * @phpstan-import-type TValue from JsonSchema
 */
final class FileFieldFactory extends AbstractFieldJsonSchemaFactory {

  public static function getPriority(): int {
    return IntegerFieldFactory::getPriority() + 1;
  }

  public function createSchema(AbstractFormField $field): JsonSchema {
    Assert::isInstanceOf($field, FileField::class);
    /** @var \Civi\RemoteTools\Form\FormSpec\Field\FileField $field */

    $properties = [
      'filename' => new JsonSchemaString(['minLength' => 1, 'maxLength' => 255]),
    ];
    $keywords = [
      'required' => ['filename'],
    ];

    $contentKeywords = ['contentEncoding' => 'base64'];
    if (NULL !== $field->getMaxFileSize() && $field->getMaxFileSize() > 0) {
      $contentKeywords['$validations'] = [
        JsonSchema::fromArray([
          'keyword' => 'maxLength',
          // The file might need up to 37 % more space through Base64 encoding.
          'value' => (int) ceil($field->getMaxFileSize() * 1.37),
          'message' => E::ts('The file must not be larger than %1.',
            [1 => FormatUtil::toHumanReadableBytes($field->getMaxFileSize())]
          ),
        ]),
      ];
    }

    if ($field->hasDefaultValue() && NULL !== $field->getFilename() && NULL !== $field->getUrl()) {
      $keywords['default'] = JsonSchema::fromArray([
        'filename' => $field->getFilename(),
        'url' => $field->getUrl(),
      ]);

      [$urlWithoutQuery] = explode('?', $field->getUrl(), 2);

      // Matches the default (current file).
      $currentFileSchema = JsonSchema::fromArray([
        'properties' => [
          'filename' => new JsonSchemaString(['const' => $field->getFilename()]),
          // We don't use the 'const' keyword because the URL might contain a
          // hash that depends on the time. Thus, we exclude the query from
          // the test.
          'url' => new JsonSchemaString(['format' => 'uri', 'pattern' => '^' . $urlWithoutQuery]),
        ],
        'required' => ['url'],
      ]);
      $newFileSchema = JsonSchema::fromArray([
        'properties' => [
          'content' => new JsonSchemaString($contentKeywords),
        ],
        'required' => ['content'],
      ]);

      if ($field->isReadOnly()) {
        $keywords['readOnly'] = TRUE;
        // Allow only the current file.
        $valueSchema = $currentFileSchema;
      }
      else {
        // Allow either the current file or a new file.
        $valueSchema = JsonSchema::fromArray([
          // Test if property 'url' exists.
          'if' => ['required' => ['url']],
          'then' => $currentFileSchema,
          'else' => $newFileSchema,
        ]);
      }

      if ($field->isNullable()) {
        $keywords['if'] = JsonSchema::fromArray(['not' => new JsonSchemaNull()]);
        $keywords['then'] = $valueSchema;
      }
      else {
        /** @var array<string, TValue> $keywords */
        $keywords = ArrayUtil::mergeRecursive($keywords, $valueSchema->getKeywords());
      }
    }
    elseif ($field->isReadOnly()) {
      $keywords['readOnly'] = TRUE;
      $keywords['const'] = NULL;
    }
    else {
      $properties['content'] = new JsonSchemaString($contentKeywords);
      $keywords['required'][] = 'content';
    }

    return new JsonSchemaObject($properties, $keywords, $field->isNullable());
  }

  public function supportsField(AbstractFormField $field): bool {
    return $field instanceof FileField;
  }

}
