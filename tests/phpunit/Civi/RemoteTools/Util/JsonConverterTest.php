<?php
declare(strict_types = 1);

namespace Civi\RemoteTools\Util;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\RemoteTools\Util\JsonConverter
 */
final class JsonConverterTest extends TestCase {

  public function testToArray(): void {
    $object = new \stdClass();
    $object->foo = 'bar';
    $object->bar = 2.0;

    static::assertSame(['foo' => 'bar', 'bar' => 2.0], JsonConverter::toArray($object));
  }

  public function testToStdClass(): void {
    $array = ['foo' => 'bar', 'bar' => 2.0];
    $object = JsonConverter::toStdClass($array);

    static::assertSame('bar', $object->foo);
    static::assertSame(2.0, $object->bar);
  }

  public function testToStdClassEmptyArray(): void {
    $array = [];
    $object = JsonConverter::toStdClass($array);

    static::assertEquals(new \stdClass(), $object);
  }

}
