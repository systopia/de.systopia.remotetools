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

use Webmozart\Assert\Assert;

/**
 * @phpstan-type TValue scalar|self|null|list<scalar|self|null>
 *
 * @implements \ArrayAccess<string, TValue>
 */
class JsonSchema implements \ArrayAccess, \JsonSerializable {

  /**
   * @var array<string, TValue>
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
        if (!\is_string(key($value))) {
          throw new \InvalidArgumentException('Expected associative array got non-associative array');
        }

        return self::fromArray($value);
      }

      static::assertAllowedValue($value);

      /** @var scalar|self|null $value */
      return $value;
    }, $array));
  }

  /**
   * @param array<string, mixed> $array Array containing scalars, NULL, or
   *   JsonSchema objects, and arrays containing values of these three types.
   *
   * @return self
   */
  public static function fromArray(array $array): self {
    foreach ($array as $key => $value) {
      if (\is_array($value)) {
        if (\is_string(key($value))) {
          $array[$key] = self::fromArray($value);
        }
        else {
          $array[$key] = self::convertToJsonSchemaArray($value);
        }
      }
      else {
        static::assertAllowedValue($value);
      }
    }

    /** @var array<string, TValue> $array */
    return new self($array);
  }

  /**
   * @param mixed $value
   *
   * @return void
   */
  protected static function assertAllowedValue($value): void {
    if (!static::isAllowedValue($value)) {
      throw new \InvalidArgumentException(
        \sprintf(
          'Expected scalar, %s, NULL, or non-associative array containing those three types, got "%s"',
          self::class,
          \is_object($value) ? \get_class($value) : \gettype($value),
        )
      );
    }
  }

  /**
   * @param mixed $value
   *
   * @return bool
   *   True if value is scalar|self|null|array<int, scalar|self|null>.
   */
  protected static function isAllowedValue($value): bool {
    if (!\is_array($value)) {
      $value = [$value];
    }

    foreach ($value as $k => $v) {
      if (!\is_int($k) || (!\is_scalar($v) && !$v instanceof self && NULL !== $v)) {
        return FALSE;
      }
    }

    return TRUE;
  }

  /**
   * @param array<string, TValue> $keywords
   */
  public function __construct(array $keywords) {
    $this->keywords = $keywords;
  }

  /**
   * @param string $keyword
   * @param TValue $value
   *
   * @return $this
   */
  public function addKeyword(string $keyword, $value): self {
    if ($this->hasKeyword($keyword)) {
      throw new \InvalidArgumentException(\sprintf('Keyword "%s" already exists', $keyword));
    }

    $this->keywords[$keyword] = $value;

    return $this;
  }

  /**
   * @return array<string, TValue>
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
  public function getKeywordValue(string $keyword) {
    if (!$this->hasKeyword($keyword)) {
      throw new \InvalidArgumentException(\sprintf('No such keyword "%s"', $keyword));
    }

    return $this->keywords[$keyword];
  }

  /**
   * @param mixed $default
   *
   * @return mixed
   */
  public function getKeywordValueOrDefault(string $keyword, $default) {
    return $this->hasKeyword($keyword) ? $this->keywords[$keyword] : $default;
  }

  /**
   * @phpstan-param string|array<string> $path
   *
   * @return TValue
   */
  public function getKeywordValueAt($path) {
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
   * @phpstan-param string|array<string> $path
   * @param mixed $default
   *
   * @return mixed
   */
  public function getKeywordValueAtOrDefault($path, $default) {
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
   * @return array<string, mixed> Values are of type array|scalar|null with leaves of type array{}|scalar|null.
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
  #[\ReturnTypeWillChange]
  public function jsonSerialize() {
    return $this->toArray();
  }

  /**
   * @inheritDoc
   */
  public function offsetExists($keyword): bool {
    return $this->hasKeyword($keyword);
  }

  /**
   * @inheritDoc
   */
  #[\ReturnTypeWillChange]
  public function offsetGet($keyword) {
    return $this->keywords[$keyword] ?? NULL;
  }

  /**
   * @inheritDoc
   *
   * @param scalar|self|null|list<mixed>|array<string, mixed> $value
   *   Array values can be scalars, NULL, or JsonSchema objects, and arrays
   *   containing values of these three types.
   */
  public function offsetSet($keyword, $value): void {
    if (!is_string($keyword)) {
      throw new \InvalidArgumentException(sprintf('Offset must be of type string, got %s', gettype($keyword)));
    }

    if (\is_array($value)) {
      if (\is_string(key($value))) {
        // @phpstan-ignore-next-line
        $value = self::fromArray($value);
      }
      else {
        // @phpstan-ignore-next-line
        $value = self::convertToJsonSchemaArray($value);
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
  public function offsetUnset($keyword): void {
    unset($this->keywords[$keyword]);
  }

}
