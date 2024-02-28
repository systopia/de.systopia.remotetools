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

namespace Civi\RemoteTools\Exception;

use Civi\RemoteTools\Api4\Action\AbstractProfileAwareRemoteAction;
use Civi\RemoteTools\Api4\Action\AbstractRemoteAction;
use Civi\RemoteTools\PHPUnit\Traits\CreateMockTrait;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\RemoteTools\Exception\ActionHandlerNotFoundException
 */
final class ActionHandlerNotFoundExceptionTest extends TestCase {

  use CreateMockTrait;

  public function test(): void {
    $actionMock = $this->createPartialApi4ActionMock(AbstractRemoteAction::class, 'RemoteTest', 'get');

    $exception = new ActionHandlerNotFoundException($actionMock, NULL, 1);
    static::assertSame($actionMock, $exception->getAction());
    static::assertSame(1, $exception->getCode());
    static::assertSame('No action handler found for RemoteTest.get', $exception->getMessage());
  }

  public function testProfileAwareAction(): void {
    $actionMock = $this->createPartialApi4ActionMock(
      AbstractProfileAwareRemoteAction::class,
      'RemoteTest',
      'get',
    );
    $actionMock->setProfile('TestProfile');

    $exception = new ActionHandlerNotFoundException($actionMock);
    static::assertSame($actionMock, $exception->getAction());
    static::assertSame(0, $exception->getCode());
    static::assertSame('No action handler found for RemoteTest.get (profile: TestProfile)', $exception->getMessage());
  }

  public function testWithCustomMessage(): void {
    $actionMock = $this->createPartialApi4ActionMock(AbstractRemoteAction::class, 'RemoteTest', 'get');

    $exception = new ActionHandlerNotFoundException($actionMock, 'My message');
    static::assertSame($actionMock, $exception->getAction());
    static::assertSame('My message', $exception->getMessage());
  }

}
