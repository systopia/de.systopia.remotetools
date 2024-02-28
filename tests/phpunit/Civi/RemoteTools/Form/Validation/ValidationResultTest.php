<?php
declare(strict_types = 1);

namespace Civi\RemoteTools\Form\Validation;

use Civi\RemoteTools\Exception\ValidationFailedException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\RemoteTools\Form\Validation\ValidationResult
 */
final class ValidationResultTest extends TestCase {

  public function test(): void {
    $result = new ValidationResult();
    static::assertTrue($result->isValid());
    static::assertFalse($result->hasErrors());
    static::assertFalse($result->hasErrorsFor('test1'));

    static::assertSame([], $result->getErrors());
    static::assertSame([], $result->getErrorsFor('test1'));
    static::assertSame([], $result->getErrorsFlat());
    static::assertSame([], $result->getErrorMessages());

    $error1 = ValidationError::new('test1', 'Foo1');
    $result->addError($error1);

    static::assertFalse($result->isValid());
    static::assertTrue($result->hasErrors());
    static::assertTrue($result->hasErrorsFor('test1'));
    static::assertFalse($result->hasErrorsFor('test2'));

    static::assertSame(['test1' => [$error1]], $result->getErrors());
    static::assertSame([$error1], $result->getErrorsFor('test1'));
    static::assertSame([], $result->getErrorsFor('test2'));
    static::assertSame([$error1], $result->getErrorsFlat());
    static::assertSame(['test1' => [$error1->message]], $result->getErrorMessages());

    $error2 = ValidationError::new('test2', 'Foo2');
    $result->addErrors($error2);

    static::assertSame(['test1' => [$error1], 'test2' => [$error2]], $result->getErrors());
    static::assertSame([$error1, $error2], $result->getErrorsFlat());
    static::assertSame([
      'test1' => [$error1->message],
      'test2' => [$error2->message],
    ], $result->getErrorMessages());

    $error1X = ValidationError::new('test1', 'Foo1X');
    $result->addError($error1X);

    static::assertSame(['test1' => [$error1, $error1X], 'test2' => [$error2]], $result->getErrors());
    static::assertSame([$error1, $error1X], $result->getErrorsFor('test1'));
    static::assertSame([$error1, $error1X, $error2], $result->getErrorsFlat());
    static::assertSame([
      'test1' => [$error1->message, $error1X->message],
      'test2' => [$error2->message],
    ], $result->getErrorMessages());
  }

  public function testMerge(): void {
    $error1 = ValidationError::new('test1', 'Foo1');
    $result1 = ValidationResult::new($error1);

    $error1X = ValidationError::new('test1', 'Foo1X');
    $error2 = ValidationError::new('test2', 'Foo2');
    $result2 = ValidationResult::new($error1X, $error2);

    $result1->merge($result2);
    static::assertSame(['test1' => [$error1, $error1X], 'test2' => [$error2]], $result1->getErrors());
  }

  public function testToException(): void {
    $result = ValidationResult::new(
      ValidationError::new('test1', 'Foo1'),
      ValidationError::new('test2', 'Foo2'),
    );

    static::assertEquals(
      new ValidationFailedException('Validation failed: Foo1, Foo2'),
      $result->toException()
    );
  }

}
