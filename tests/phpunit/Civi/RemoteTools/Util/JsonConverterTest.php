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

    static::assertSame(['foo' => 'bar'], JsonConverter::toArray($object));
  }

  public function testToStdClass(): void {
    $array = ['foo' => 'bar'];
    $object = JsonConverter::toStdClass($array);

    static::assertSame('bar', $object->foo);
  }

}
