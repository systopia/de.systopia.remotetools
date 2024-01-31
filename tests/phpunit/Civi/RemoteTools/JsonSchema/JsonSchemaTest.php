<?php
declare(strict_types = 1);

namespace Civi\RemoteTools\JsonSchema;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\RemoteTools\JsonSchema\JsonSchema
 */
final class JsonSchemaTest extends TestCase {

  public function testAddKeyword(): void {
    $schema = new JsonSchema([]);
    $schema->addKeyword('foo', 'bar');
    static::assertSame('bar', $schema->getKeywordValue('foo'));
    static::assertSame(['foo' => 'bar'], $schema->getKeywords());
    static::assertTrue($schema->hasKeyword('foo'));
    static::assertFalse($schema->hasKeyword('bar'));

    static::expectException(\InvalidArgumentException::class);
    static::expectExceptionMessage('Keyword "foo" already exists');
    $schema->addKeyword('foo', 'bar2');
  }

  public function testGetKeywordValueAt(): void {
    $schemaB = new JsonSchema([
      'c' => 'd',
    ]);

    $schema = new JsonSchema([
      'a' => new JsonSchema([
        'b' => $schemaB,
      ]),
    ]);

    static::assertSame('d', $schema->getKeywordValueAt('a/b/c'));
    static::assertSame('d', $schema->getKeywordValueAt('/a/b/c'));
    static::assertSame('d', $schema->getKeywordValueAt(['a', 'b', 'c']));
    static::assertSame($schemaB, $schema->getKeywordValueAt('a/b'));

    static::expectException(\InvalidArgumentException::class);
    static::expectExceptionMessage('No keyword at "a/c"');
    $schema->getKeywordValueAt('/a/c');
  }

  public function testGetKeywordValueAtOrDefault(): void {
    $schemaB = new JsonSchema([
      'c' => 'd',
    ]);

    $schema = new JsonSchema([
      'a' => new JsonSchema([
        'b' => $schemaB,
      ]),
    ]);

    static::assertSame('d', $schema->getKeywordValueAtOrDefault('a/b/c', 'X'));
    static::assertSame('d', $schema->getKeywordValueAtOrDefault('/a/b/c', 'X'));
    static::assertSame('d', $schema->getKeywordValueAtOrDefault(['a', 'b', 'c'], 'X'));
    static::assertSame($schemaB, $schema->getKeywordValueAtOrDefault('a/b', 'X'));

    static::assertSame('X', $schema->getKeywordValueAtOrDefault('/a/c', 'X'));
  }

  public function testGetMissingKeyword(): void {
    $schema = new JsonSchema([]);
    static::expectException(\InvalidArgumentException::class);
    static::expectExceptionMessage('No such keyword "foo"');
    $schema->getKeywordValue('foo');
  }

  public function testFromArray(): void {
    $array = [
      'foo' => [
        'bar' => 'baz',
      ],
      'fuu' => [2, NULL, 'test', TRUE],
    ];
    $schema = JsonSchema::fromArray($array);
    $keywords = $schema->getKeywords();
    static::assertSame(['foo', 'fuu'], array_keys($keywords));
    static::assertInstanceOf(JsonSchema::class, $keywords['foo']);
    static::assertSame(['bar' => 'baz'], $keywords['foo']->getKeywords());
    static::assertSame([2, NULL, 'test', TRUE], $keywords['fuu']);
  }

  public function testFromArrayInvalid01(): void {
    static::expectException(\InvalidArgumentException::class);
    static::expectExceptionMessage('Expected associative array got non-associative array');
    JsonSchema::fromArray(['foo' => [['invalid']]]);
  }

  public function testFromArrayInvalid02(): void {
    static::expectException(\InvalidArgumentException::class);
    static::expectExceptionMessage(sprintf(
      'Expected scalar, %s, NULL, or non-associative array containing those three types, got "stdClass"',
      JsonSchema::class
    ));
    JsonSchema::fromArray(['foo' => new \stdClass()]);
  }

  public function testToArray(): void {
    $schema = new JsonSchema([
      'foo' => new JsonSchema(['bar' => 'baz']),
      'fuu' => [1, 2, new JsonSchema(['keyword' => 'value']), TRUE],
      'f00' => NULL,
    ]);
    $expected = [
      'foo' => ['bar' => 'baz'],
      'fuu' => [1, 2, ['keyword' => 'value'], TRUE],
      'f00' => NULL,
    ];
    static::assertSame($expected, $schema->toArray());
  }

  public function testToStdClass(): void {
    $schema = new JsonSchema([
      'foo' => new JsonSchema(['bar' => 'baz']),
      'fuu' => [1, 2, new JsonSchema(['keyword' => 'value']), TRUE],
      'f00' => NULL,
    ]);
    $expected = (object) [
      'foo' => (object) ['bar' => 'baz'],
      'fuu' => [1, 2, (object) ['keyword' => 'value'], TRUE],
      'f00' => NULL,
    ];
    static::assertEquals($expected, $schema->toStdClass());
  }

  public function testJsonSerialize(): void {
    $schema = new JsonSchema([
      'foo' => new JsonSchema(['bar' => 'baz']),
      'fuu' => [1, 2, new JsonSchema(['keyword' => 'value'])],
      'f00' => NULL,
    ]);
    $expected = json_encode([
      'foo' => ['bar' => 'baz'],
      'fuu' => [1, 2, ['keyword' => 'value']],
      'f00' => NULL,
    ]);
    static::assertSame($expected, json_encode($schema));
  }

  public function testConvertToJsonSchemaArray(): void {
    $array = ['foo', 2, ['bar' => 'baz'], FALSE];
    $schemaArray = JsonSchema::convertToJsonSchemaArray($array);
    $expected = ['foo', 2, new JsonSchema(['bar' => 'baz']), FALSE];
    static::assertEquals($expected, $schemaArray);
  }

  public function testConvertToJsonSchemaArrayInvalid(): void {
    static::expectException(\InvalidArgumentException::class);
    static::expectExceptionMessage('Expected associative array got non-associative array');
    JsonSchema::convertToJsonSchemaArray([['invalid']]);
  }

  public function testArrayAccess(): void {
    $schema = new JsonSchema([]);

    static::assertArrayNotHasKey('test', $schema);
    static::assertNull($schema['test']);

    $schema['test'] = 'x';
    static::assertArrayHasKey('test', $schema);
    static::assertSame('x', $schema['test']);

    $schema['test'] = ['x', 'y'];
    static::assertSame(['x', 'y'], $schema['test']);

    $test = ['x' => 'y', 'y' => new JsonSchema([])];
    $schema['test'] = $test;
    static::assertEquals(JsonSchema::fromArray($test), $schema['test']);

    $schema['test'] = NULL;
    static::assertArrayHasKey('test', $schema);
    static::assertNull($schema['test']);
  }

}
