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

  public function __construct(
    ?int $maxFileSize = NULL,
    array $keywords = [],
    bool $nullable = FALSE
  ) {
    $properties = ($keywords['properties'] ?? []) + [
      'filename' => new JsonSchemaString(['minLength' => 1, 'maxLength' => 255]),
    ];
    $keywords['required'] = ['filename'];

    // Matches a current file, i.e. 'url' is set.
    $currentFileSchema = JsonSchema::fromArray([
      'properties' => [
        'url' => new JsonSchemaString(['format' => 'uri']),
      ],
      'required' => ['url'],
    ]);

    if (TRUE === ($keywords['readOnly'] ?? NULL)) {
      // Allow only a current file.
      $valueSchema = $currentFileSchema;
    }
    else {
      // Allow either a current file or a new file.
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

      $newFileSchema = JsonSchema::fromArray([
        'properties' => [
          'content' => new JsonSchemaString($contentKeywords),
        ],
        'required' => ['content'],
      ]);
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

    parent::__construct($properties, $keywords, $nullable);
  }

}
