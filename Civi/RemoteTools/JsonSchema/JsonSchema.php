<?php
/*
 * Copyright (C) 2022 SYSTOPIA GmbH
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

namespace Civi\RemoteTools\JsonSchema;

use Civi\RemoteTools\Util\ArrayUtil;
use Webmozart\Assert\Assert;

/**
 * @phpstan-type TValue scalar|self|null|list<scalar|self|null>
 *
 * @implements \ArrayAccess<string, TValue>
 */
class JsonSchema implements \ArrayAccess, \IteratorAggregate, \JsonSerializable {

  /**
   * @phpstan-var array<int|string, TValue>
   *   The array keys are not strictly increasing starting at 0, i.e. in JSON it
   *   is encoded as object, not as array. (Empty array is allowed, though.)
   */
  protected array $keywords;

  /**
   * @param list<mixed> $array
   *
   * @return list<scalar|self|null>
   */
  public static function convertToJsonSchemaArray(array $array): array {
    return \array_values(\array_map(function ($value) {
      if (\is_array($value)) {
        if ([] !== $value && ArrayUtil::isJsonArray($value)) {
          throw new \InvalidArgumentException('Expected an empty array, or an array that encodes to a JSON object');
        }

        return self::fromArray($value);
      }

      static::assertAllowedValue($value);

      /** @var scalar|self|null $value */
      return $value;
    }, $array));
  }

  /**
   * @param array<int|string, mixed> $array Array containing scalars, NULL, or
   *   JsonSchema objects, and arrays containing values of these three types.
   *   The array keys must not be strictly increasing starting at 0, i.e. in
   *   JSON it is encoded as object, not as array.
   *
   * @return self
   */
  public static function fromArray(array $array): self {
    foreach ($array as $key => $value) {
      if (\is_array($value)) {
        if (ArrayUtil::isJsonArray($value)) {
          $array[$key] = self::convertToJsonSchemaArray($value);
        }
        else {
          $array[$key] = self::fromArray($value);
        }
      }
      else {
        static::assertAllowedValue($value);
      }
    }

    /** @phpstan-var array<int|string, TValue> $array */
    return new self($array);
  }

  /**
   * @phpstan-assert TValue $value
   */
  protected static function assertAllowedValue(mixed $value): void {
    if (!static::isAllowedValue($value)) {
      throw new \InvalidArgumentException(
        \sprintf(
          'Expected scalar, %s, NULL, an empty array, or an array that encodes to a JSON ' .
          'object containing those three types, got "%s"',
          self::class,
          \is_object($value) ? \get_class($value) : \gettype($value),
        )
      );
    }
  }

  /**
   * @phpstan-assert-if-true TValue $value
   */
  protected static function isAllowedValue(mixed $value): bool {
    if (!\is_array($value)) {
      $value = [$value];
    }

    $expectedKey = 0;
    foreach ($value as $k => $v) {
      if ($k !== $expectedKey || (!\is_scalar($v) && !$v instanceof self && NULL !== $v)) {
        return FALSE;
      }

      ++$expectedKey;
    }

    return TRUE;
  }

  /**
   * @param array<int|string, TValue> $keywords
   *   The array keys must not be strictly increasing starting at 0, i.e. in
   *   JSON it is encoded as object, not as array. (Empty array is allowed,
   *   though.)
   */
  public function __construct(array $keywords) {
    if ([] !== $keywords && ArrayUtil::isJsonArray($keywords)) {
      throw new \InvalidArgumentException('Array keys must not be strictly increasing starting at 0');
    }

    $this->keywords = $keywords;
  }

  public function __clone() {
    $this->keywords = array_map(function ($value) {
      if (is_object($value)) {
        return clone $value;
      }
      elseif (is_array($value)) {
        return array_values(array_map(fn ($value) => is_object($value) ? clone $value : $value, $value));
      }

      return $value;
    }, $this->keywords);
  }

  /**
   * @phpstan-param TValue $value
   *
   * @return $this
   */
  public function addKeyword(string $keyword, bool|float|int|string|self|null|array $value): static {
    if ($this->hasKeyword($keyword)) {
      throw new \InvalidArgumentException(\sprintf('Keyword "%s" already exists', $keyword));
    }

    $this->keywords[$keyword] = $value;

    return $this;
  }

  /**
   * @phpstan-return array<int|string, TValue>
   *   The array keys are not strictly increasing starting at 0, i.e. in JSON it
   *   is encoded as object, not as array. (Or the array is empty.)
   */
  public function getKeywords(): array {
    return $this->keywords;
  }

  public function hasKeyword(string $keyword): bool {
    return array_key_exists($keyword, $this->keywords);
  }

  /**
   * @param string $keyword
   *
   * @return TValue
   */
  public function getKeywordValue(string $keyword): bool|float|int|string|self|null|array {
    if (!$this->hasKeyword($keyword)) {
      throw new \InvalidArgumentException(\sprintf('No such keyword "%s"', $keyword));
    }

    return $this->keywords[$keyword];
  }

  public function getKeywordValueOrDefault(string $keyword, mixed $default): mixed {
    return $this->hasKeyword($keyword) ? $this->keywords[$keyword] : $default;
  }

  /**
   * @phpstan-param string|list<string> $path
   *
   * @return TValue
   */
  public function getKeywordValueAt(string|array $path): bool|float|int|string|self|null|array {
    if (is_string($path)) {
      $path = explode('/', ltrim($path, '/'));
    }
    else {
      Assert::isArray($path);
    }

    $keywordValue = $this;
    foreach ($path as $pathElement) {
      if (!$keywordValue instanceof JsonSchema || !$keywordValue->hasKeyword($pathElement)) {
        throw new \InvalidArgumentException(\sprintf('No keyword at "%s"', implode('/', $path)));
      }

      $keywordValue = $keywordValue->getKeywordValue($pathElement);
    }

    return $keywordValue;
  }

  /**
   * @phpstan-param string|list<string> $path
   */
  public function getKeywordValueAtOrDefault(string|array $path, mixed $default): mixed {
    if (is_string($path)) {
      $path = explode('/', ltrim($path, '/'));
    }
    else {
      Assert::isArray($path);
    }

    $keywordValue = $this;
    foreach ($path as $pathElement) {
      if (!$keywordValue instanceof JsonSchema || !$keywordValue->hasKeyword($pathElement)) {
        return $default;
      }

      $keywordValue = $keywordValue->getKeywordValue($pathElement);
    }

    return $keywordValue;
  }

  /**
   * @phpstan-return array<int|string, mixed>
   *   Values are of type array|scalar|null with leaves of type
   *   array{}|scalar|null. If keys are only integers they are not strictly
   *   increasing starting at 0.
   */
  public function toArray(): array {
    return \array_map(function ($value) {
      if ($value instanceof self) {
        return $value->toArray();
      }
      elseif (\is_array($value)) {
        return \array_values(\array_map(fn ($value) => $value instanceof self ? $value->toArray() : $value, $value));
      }

      return $value;
    }, $this->keywords);
  }

  /**
   * @return \stdClass
   *   Properties are of type \stdClass|array|scalar|null with leaf properties
   *   of type array{}|scalar|null.
   */
  public function toStdClass(): \stdClass {
    return (object) \array_map(function ($value) {
      if ($value instanceof self) {
        return $value->toStdClass();
      }
      elseif (\is_array($value)) {
        return \array_values(\array_map(fn ($value) => $value instanceof self ? $value->toStdClass() : $value, $value));
      }

      return $value;
    }, $this->keywords);
  }

  /**
   * @inheritDoc
   */
  public function getIterator(): \Traversable {
    return new \ArrayIterator($this->keywords);
  }

  /**
   * @inheritDoc
   */
  public function jsonSerialize(): \stdClass {
    return (object) $this->toArray();
  }

  /**
   * @inheritDoc
   */
  public function offsetExists(mixed $keyword): bool {
    return $this->hasKeyword($keyword);
  }

  /**
   * @inheritDoc
   *
   * @phpstan-return TValue
   */
  public function offsetGet(mixed $keyword): bool|float|int|string|self|null|array {
    return $this->keywords[$keyword] ?? NULL;
  }

  /**
   * @inheritDoc
   *
   * @param scalar|self|null|list<mixed>|array<string, mixed> $value
   *   Array values can be scalars, NULL, or JsonSchema objects, and arrays
   *   containing values of these three types.
   */
  public function offsetSet(mixed $keyword, mixed $value): void {
    if (!is_string($keyword)) {
      throw new \InvalidArgumentException(sprintf('Offset must be of type string, got %s', gettype($keyword)));
    }

    if (\is_array($value)) {
      if (ArrayUtil::isJsonArray($value)) {
        $value = self::convertToJsonSchemaArray($value);
      }
      else {
        $value = self::fromArray($value);
      }
    }
    else {
      static::assertAllowedValue($value);
    }

    $this->keywords[$keyword] = $value;
  }

  /**
   * @inheritDoc
   */
  public function offsetUnset(mixed $keyword): void {
    unset($this->keywords[$keyword]);
  }

}
