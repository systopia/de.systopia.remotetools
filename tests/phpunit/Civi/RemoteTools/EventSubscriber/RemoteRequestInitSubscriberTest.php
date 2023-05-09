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

namespace Civi\RemoteTools\EventSubscriber;

use Civi\API\Event\AuthorizeEvent;
use Civi\RemoteTools\Api4\Action\AbstractRemoteAction;
use Civi\RemoteTools\Contact\RemoteContactIdResolverInterface;
use Civi\RemoteTools\Contact\RemoteContactIdResolverProviderInterface;
use Civi\RemoteTools\RequestContext\RequestContext;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\RemoteTools\EventSubscriber\RemoteRequestInitSubscriber
 */
final class RemoteRequestInitSubscriberTest extends TestCase {

  /**
   * @var \Civi\API\Event\AuthorizeEvent&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $eventMock;

  /**
   * @var \Civi\RemoteTools\Contact\RemoteContactIdResolverInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $remoteContactIdResolverMock;

  private RequestContext $requestContext;

  /**
   * @var \Civi\RemoteTools\Api4\Action\AbstractRemoteAction&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $requestMock;

  private RemoteRequestInitSubscriber $subscriber;

  protected function setUp(): void {
    parent::setUp();

    $this->eventMock = $this->createMock(AuthorizeEvent::class);
    $this->requestMock = $this->createPartialMock(AbstractRemoteAction::class, []);
    $this->eventMock->method('getApiRequest')->willReturn($this->requestMock);

    $this->remoteContactIdResolverMock = $this->createMock(RemoteContactIdResolverInterface::class);
    $this->requestContext = new RequestContext();

    $remoteContactIdResolverProviderMock = $this->createMock(RemoteContactIdResolverProviderInterface::class);
    $remoteContactIdResolverProviderMock->method('get')->willReturn($this->remoteContactIdResolverMock);

    $this->subscriber = new RemoteRequestInitSubscriber(
      $remoteContactIdResolverProviderMock,
      $this->requestContext,
    );
  }

  public function testGetSubscribedEvents(): void {
    $expectedSubscriptions = [
      'civi.api.authorize' => ['onApiAuthorize', PHP_INT_MAX],
    ];

    static::assertEquals($expectedSubscriptions, $this->subscriber::getSubscribedEvents());

    foreach ($expectedSubscriptions as [$method, $priority]) {
      static::assertTrue(method_exists(get_class($this->subscriber), $method));
    }
  }

  public function testRemoteContactIdNull(): void {
    $this->subscriber->onApiAuthorize($this->eventMock);

    static::assertTrue($this->requestContext->isRemote());
    static::assertNull($this->requestContext->getRemoteContactId());
    static::assertNull($this->requestContext->getResolvedContactId());
  }

  public function testRemoteContactIdResolved(): void {
    $this->requestMock->setRemoteContactId('test');

    $this->remoteContactIdResolverMock->expects(static::once())->method('getContactId')
      ->with('test')
      ->willReturn(123);

    $this->subscriber->onApiAuthorize($this->eventMock);

    static::assertTrue($this->requestContext->isRemote());
    static::assertSame('test', $this->requestContext->getRemoteContactId());
    static::assertSame(123, $this->requestContext->getResolvedContactId());
    static::assertSame(123, $this->requestContext->getContactId());
  }

}
