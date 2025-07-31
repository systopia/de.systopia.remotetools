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

namespace Civi\RemoteTools\Form\FormSpec\DataTransformer;

use Civi\RemoteTools\Form\FormSpec\Field\HtmlField;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\RemoteTools\Form\FormSpec\DataTransformer\HtmlDataTransformer
 */
final class HtmlDataTransformerTest extends TestCase {

  public function testToEntityValue(): void {
    $transformer = new HtmlDataTransformer();

    $field = (new HtmlField('test', 'Test'))
      ->addAllowedElement('allowed1', ['attr'])
      ->addAllowedElement('allowed2', NULL)
      ->addBlockedElement('h1')
      ->addDroppedElement('h2');

    static::assertSame(
      '<allowed1 attr="test">bar</allowed1>',
      $transformer->toEntityValue('<allowed1 attr="test" alt="foo">bar</allowed1>', $field)
    );

    static::assertSame(
      '<allowed2 alt="foo">bar</allowed2>',
      $transformer->toEntityValue('<allowed2 attr="test" alt="foo">bar</allowed2>', $field)
    );

    static::assertSame(
      'foo',
      $transformer->toEntityValue('<h1>foo</h1>', $field)
    );

    static::assertSame(
      '',
      $transformer->toEntityValue('<h2>foo</h2>', $field)
    );

    static::assertSame(
      '<h3>foo</h3> ',
      $transformer->toEntityValue('<h3>foo</h3> <script>abc</script>', $field)
    );
  }

}
