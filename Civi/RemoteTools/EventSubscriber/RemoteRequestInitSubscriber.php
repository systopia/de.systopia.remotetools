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
use Civi\RemoteTools\Api4\Action\RemoteActionInterface;
use Civi\RemoteTools\Contact\RemoteContactIdResolverProviderInterface;
use Civi\RemoteTools\RequestContext\RequestContextInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RemoteRequestInitSubscriber implements EventSubscriberInterface {

  private RemoteContactIdResolverProviderInterface $remoteContactIdResolverProvider;

  private RequestContextInterface $requestContext;

  public function __construct(
    RemoteContactIdResolverProviderInterface $remoteContactIdResolverProvider,
    RequestContextInterface $requestContext
  ) {
    $this->remoteContactIdResolverProvider = $remoteContactIdResolverProvider;
    $this->requestContext = $requestContext;
  }

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    // Highest priority so that the remote contact ID is resolved before
    // the authorize event is actually handled.
    return ['civi.api.authorize' => ['onApiAuthorize', PHP_INT_MAX]];
  }

  /**
   * @param \Civi\API\Event\AuthorizeEvent $event
   *
   * @throws \Civi\RemoteTools\Exception\ResolveContactIdFailedException
   */
  public function onApiAuthorize(AuthorizeEvent $event): void {
    $request = $event->getApiRequest();
    if ($request instanceof RemoteActionInterface) {
      $this->requestContext->setRemote(TRUE);
      $this->requestContext->setRemoteContactId($request->getRemoteContactId());
      $this->requestContext->setResolvedContactId($this->resolveContactId($request));
    }
  }

  /**
   * @throws \Civi\RemoteTools\Exception\ResolveContactIdFailedException
   */
  private function resolveContactId(RemoteActionInterface $request): ?int {
    if (NULL === $request->getRemoteContactId()) {
      return NULL;
    }

    return $this->remoteContactIdResolverProvider->get($request)
      ->getContactId($request->getRemoteContactId());
  }

}
