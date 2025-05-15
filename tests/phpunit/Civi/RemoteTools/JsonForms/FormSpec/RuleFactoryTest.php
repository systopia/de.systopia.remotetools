<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
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

namespace Civi\RemoteTools\JsonForms\FormSpec;

use Civi\RemoteTools\Form\FormSpec\Rule\FormRule;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\RemoteTools\JsonForms\FormSpec\RuleFactory
 * @covers \Civi\RemoteTools\Form\FormSpec\Rule\FormRule
 */
final class RuleFactoryTest extends TestCase {

  public function testEquals(): void {
    $ruleSchema = RuleFactory::createJsonFormsRule(new FormRule('SHOW', ['foo' => ['=', 'bar']]));

    static::assertEquals([
      'effect' => 'SHOW',
      'condition' => [
        'scope' => '#',
        'schema' => [
          'properties' => [
            'foo' => ['const' => 'bar'],
          ],
        ],
      ],
    ], $ruleSchema->toArray());
  }

  public function testNotEquals(): void {
    $ruleSchema = RuleFactory::createJsonFormsRule(new FormRule('HIDE', ['foo' => ['!=', 'bar']]));

    static::assertEquals([
      'effect' => 'HIDE',
      'condition' => [
        'scope' => '#',
        'schema' => [
          'properties' => [
            'foo' => ['not' => ['const' => 'bar']],
          ],
        ],
      ],
    ], $ruleSchema->toArray());
  }

  public function testIn(): void {
    $ruleSchema = RuleFactory::createJsonFormsRule(new FormRule('ENABLE', ['foo' => ['IN', ['bar', 'baz']]]));

    static::assertEquals([
      'effect' => 'ENABLE',
      'condition' => [
        'scope' => '#',
        'schema' => [
          'properties' => [
            'foo' => ['enum' => ['bar', 'baz']],
          ],
        ],
      ],
    ], $ruleSchema->toArray());
  }

  public function testNotIn(): void {
    $ruleSchema = RuleFactory::createJsonFormsRule(new FormRule('DISABLE', ['foo' => ['NOT IN', ['bar', 'baz']]]));

    static::assertEquals([
      'effect' => 'DISABLE',
      'condition' => [
        'scope' => '#',
        'schema' => [
          'properties' => [
            'foo' => ['not' => ['enum' => ['bar', 'baz']]],
          ],
        ],
      ],
    ], $ruleSchema->toArray());
  }

  public function testContains(): void {
    $ruleSchema = RuleFactory::createJsonFormsRule(new FormRule('SHOW', ['foo' => ['CONTAINS', ['bar', 'baz']]]));

    static::assertEquals([
      'effect' => 'SHOW',
      'condition' => [
        'scope' => '#',
        'schema' => [
          'properties' => [
            'foo' => ['contains' => ['enum' => ['bar', 'baz']]],
          ],
        ],
      ],
    ], $ruleSchema->toArray());
  }

  public function testNotContains(): void {
    $ruleSchema = RuleFactory::createJsonFormsRule(new FormRule('SHOW', ['foo' => ['NOT CONTAINS', ['bar', 'baz']]]));

    static::assertEquals([
      'effect' => 'SHOW',
      'condition' => [
        'scope' => '#',
        'schema' => [
          'properties' => [
            'foo' => ['not' => ['contains' => ['enum' => ['bar', 'baz']]]],
          ],
        ],
      ],
    ], $ruleSchema->toArray());
  }

}
