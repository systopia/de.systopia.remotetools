<?php
declare(strict_types = 1);

namespace Civi\RemoteTools\JsonSchema\FormSpec;

use Civi\RemoteTools\JsonSchema\JsonSchema;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\RemoteTools\JsonSchema\FormSpec\LimitValidationSchemaFactory
 */
final class LimitValidationSchemaFactoryTest extends TestCase {

  private const EXPECTED_RULE = [
    'keyword' => ['const' => 'required'],
    'validate' => TRUE,
  ];

  public function testNull(): void {
    static::assertNull(LimitValidationSchemaFactory::createSchema(NULL));
  }

  public function testFalse(): void {
    $schema = LimitValidationSchemaFactory::createSchema(FALSE);
    static::assertNotNull($schema);
    static::assertEquals([
      'condition' => FALSE,
    ], $schema->toArray());
  }

  public function testTrue(): void {
    $schema = LimitValidationSchemaFactory::createSchema(TRUE);
    static::assertNotNull($schema);
    static::assertEquals([
      'condition' => TRUE,
      'rules' => [self::EXPECTED_RULE],
    ], $schema->toArray());
  }

  public function testFieldNameValuePairs(): void {
    $schema = LimitValidationSchemaFactory::createSchema(['foo' => 'bar']);
    static::assertLimitValidationPropertiesSchema([
      'foo' => ['const' => 'bar'],
    ], $schema);
  }

  public function testFieldNameValuesPairs(): void {
    $schema = LimitValidationSchemaFactory::createSchema(['foo' => ['bar', 'baz']]);
    static::assertLimitValidationPropertiesSchema([
      'foo' => ['enum' => ['bar', 'baz']],
    ], $schema);
  }

  public function testConditionList(): void {
    $schema = LimitValidationSchemaFactory::createSchema([
      ['foo', '=', 'bar'],
    ]);
    static::assertLimitValidationPropertiesSchema([
      'foo' => ['const' => 'bar'],
    ], $schema);

    $schema = LimitValidationSchemaFactory::createSchema([
      ['foo', '!=', 'bar'],
    ]);
    static::assertLimitValidationPropertiesSchema([
      'foo' => ['not' => ['const' => 'bar']],
    ], $schema);

    $schema = LimitValidationSchemaFactory::createSchema([
      ['foo', '>', 1],
    ]);
    static::assertLimitValidationPropertiesSchema([
      'foo' => ['exclusiveMinimum' => 1],
    ], $schema);

    $schema = LimitValidationSchemaFactory::createSchema([
      ['foo', '>=', 1],
    ]);
    static::assertLimitValidationPropertiesSchema([
      'foo' => ['minimum' => 1],
    ], $schema);

    $schema = LimitValidationSchemaFactory::createSchema([
      ['foo', '<', 1],
    ]);
    static::assertLimitValidationPropertiesSchema([
      'foo' => ['exclusiveMaximum' => 1],
    ], $schema);

    $schema = LimitValidationSchemaFactory::createSchema([
      ['foo', '<=', 1],
    ]);
    static::assertLimitValidationPropertiesSchema([
      'foo' => ['maximum' => 1],
    ], $schema);

    $schema = LimitValidationSchemaFactory::createSchema([
      ['foo', '=~', 'abc'],
    ]);
    static::assertLimitValidationPropertiesSchema([
      'foo' => ['pattern' => 'abc'],
    ], $schema);

    $schema = LimitValidationSchemaFactory::createSchema([
      ['foo', 'IN', ['bar', 'baz']],
    ]);
    static::assertLimitValidationPropertiesSchema([
      'foo' => ['enum' => ['bar', 'baz']],
    ], $schema);

    $schema = LimitValidationSchemaFactory::createSchema([
      ['foo', 'NOT IN', ['bar', 'baz']],
    ]);
    static::assertLimitValidationPropertiesSchema([
      'foo' => ['not' => ['enum' => ['bar', 'baz']]],
    ], $schema);
  }

  public function testExpression(): void {
    $schema = LimitValidationSchemaFactory::createSchema('@{foo} + @{bar} > 10');
    static::assertNotNull($schema);
    static::assertEquals([
      'condition' => [
        'evaluate' => [
          'expression' => 'foo + bar > 10',
          'variables' => [
            'foo' => ['$data' => '/foo'],
            'bar' => ['$data' => '/bar'],
          ],
        ],
      ],
      'rules' => [self::EXPECTED_RULE],
    ], $schema->toArray());
  }

  /**
   * @phpstan-param array<string, array<string, mixed>> $properties
   */
  private static function assertLimitValidationPropertiesSchema(array $properties, ?JsonSchema $schema): void {
    static::assertNotNull($schema);
    static::assertEquals([
      'condition' => [
        'properties' => $properties,
      ],
      'rules' => [self::EXPECTED_RULE],
    ], $schema->toArray());
  }

}
