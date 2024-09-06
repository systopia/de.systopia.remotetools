<?php
declare(strict_types = 1);

namespace Civi\RemoteTools\JsonSchema;

use Civi\RemoteTools\Util\ArrayUtil;
use Civi\RemoteTools\Util\FormatUtil;
use CRM_Remotetools_ExtensionUtil as E;

/**
 * Custom schema for a CiviCRM file field.
 *
 * @phpstan-import-type TValue from JsonSchema
 *
 * @phpstan-type validFileSchemaDataT array{
 *    filename: string,
 *    url: string,
 *  }|array{
 *    filename: string,
 *    content: string,
 *  }
 *  Valid data. "url" is set, if the value is unchanged (existing file).
 * "content" is set, if a new file is uploaded.
 */
final class JsonSchemaFile extends JsonSchemaObject {

  /**
   * @phpstan-param array{filename: string, url: string}|null $currentFile
   *   The current file, if any, is necessary to create the correct schema.
   */
  public function __construct(
    ?array $currentFile = NULL,
    ?int $maxFileSize = NULL,
    array $keywords = [],
    bool $nullable = FALSE
  ) {
    $properties = [
      'filename' => new JsonSchemaString(['minLength' => 1, 'maxLength' => 255]),
    ];
    $keywords['required'] = ['filename'];

    $contentKeywords = ['contentEncoding' => 'base64'];
    if (NULL !== $maxFileSize && $maxFileSize > 0) {
      $contentKeywords['$validations'] = [
        JsonSchema::fromArray([
          'keyword' => 'maxLength',
          // The file might need up to 37 % more space through Base64 encoding.
          'value' => (int) ceil($maxFileSize * 1.37),
          'message' => E::ts('The file must not be larger than %1.',
            [1 => FormatUtil::toHumanReadableBytes($maxFileSize)]
          ),
        ]),
      ];
    }

    if (NULL !== $currentFile) {
      $keywords['default'] = JsonSchema::fromArray([
        'filename' => $currentFile['filename'],
        'url' => $currentFile['url'],
      ]);

      [$urlWithoutQuery] = explode('?', $currentFile['url'], 2);

      // Matches the default (current file).
      $currentFileSchema = JsonSchema::fromArray([
        'properties' => [
          'filename' => new JsonSchemaString(['const' => $currentFile['filename']]),
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

      if (TRUE === ($keywords['readOnly'] ?? NULL)) {
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

      if ($nullable) {
        $keywords['if'] = JsonSchema::fromArray(['not' => new JsonSchemaNull()]);
        $keywords['then'] = $valueSchema;
      }
      else {
        /** @var array<string, TValue> $keywords */
        $keywords = ArrayUtil::mergeRecursive($keywords, $valueSchema->getKeywords());
      }
    }
    elseif (TRUE === ($keywords['readOnly'] ?? NULL)) {
      $keywords['readOnly'] = TRUE;
      $keywords['const'] = NULL;
    }
    else {
      $properties['content'] = new JsonSchemaString($contentKeywords);
      $keywords['required'][] = 'content';
    }

    parent::__construct($properties, $keywords);
  }

}
