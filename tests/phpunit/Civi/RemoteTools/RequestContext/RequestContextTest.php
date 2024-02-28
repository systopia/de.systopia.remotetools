<?php
/*
 * Copyright (C) 2023 SYSTOPIA GmbH
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

namespace Civi\RemoteTools\RequestContext;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\RemoteTools\RequestContext\RequestContext
 */
final class RequestContextTest extends TestCase {

  /**
   * @var \Civi\RemoteTools\RequestContext\RequestContext
   */
  private RequestContext $requestContext;

  protected function setUp(): void {
    parent::setUp();
    $this->requestContext = new RequestContext();
  }

  protected function tearDown(): void {
    parent::tearDown();
    \CRM_Core_Session::singleton()->reset();
  }

  public function testGet(): void {
    static::assertNull($this->requestContext->get('foo'));
    static::assertSame('bar', $this->requestContext->get('foo', 'bar'));
    $this->requestContext->set('foo', 'baz');
    static::assertSame('baz', $this->requestContext->get('foo'));
    static::assertSame('baz', $this->requestContext->get('foo', 'bar'));
  }

  public function testGetContactId(): void {
    static::assertSame(0, $this->requestContext->getContactId());
    \CRM_Core_Session::singleton()->set('userID', 2);
    static::assertSame(2, $this->requestContext->getContactId());

    $this->requestContext->setRemote(TRUE);
    $e = NULL;
    try {
      $this->requestContext->getContactId();
    }
    catch (\Exception $e) {
      static::assertSame('Resolved contact ID missing', $e->getMessage());
    }
    static::assertNotNull($e);

    $this->requestContext->setResolvedContactId(12);
    static::assertSame(12, $this->requestContext->getContactId());
  }

  public function testGetLoggedInContactId(): void {
    static::assertSame(0, $this->requestContext->getLoggedInContactId());
    \CRM_Core_Session::singleton()->set('userID', 2);
    static::assertSame(2, $this->requestContext->getLoggedInContactId());
  }

  public function testGetRemoteContactId(): void {
    static::assertNull($this->requestContext->getRemoteContactId());
    $this->requestContext->setRemoteContactId('test');
    static::assertSame('test', $this->requestContext->getRemoteContactId());
  }

  public function testGetResolvedContactId(): void {
    static::assertNull($this->requestContext->getResolvedContactId());
    $this->requestContext->setResolvedContactId(12);
    static::assertSame(12, $this->requestContext->getResolvedContactId());
  }

  public function testIsRemote(): void {
    static::assertFalse($this->requestContext->isRemote());
    $this->requestContext->setRemote(TRUE);
    static::assertTrue($this->requestContext->isRemote());
  }

}
