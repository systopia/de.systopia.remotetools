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
use Civi\RemoteTools\Exception\ResolveContactIdFailedException;
use Civi\RemoteTools\RequestContext\RequestContextInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @phpstan-type api3RemoteRequestT array{
 *   id: int,
 *   entity: string,
 *   action: string,
 *   params: array{remote_contact_id: ?string, ...},
 * }
 */
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
    elseif ($this->isApi3RemoteRequest($request)) {
      $this->requestContext->setRemote(TRUE);
      $this->requestContext->setRemoteContactId($request['params']['remote_contact_id']);
      try {
        $this->requestContext->setResolvedContactId($this->resolveContactIdForApi3($request));
      }
      catch (ResolveContactIdFailedException) {
        // @ignoreException Don't change existing behavior for APIv3 requests
      }
    }
  }

  /**
   * @phpstan-assert-if-true api3RemoteRequestT $request
   *   Requires the entity name to start with "Remote" AND the parameter
   *   "remote_contact_id" to exist. Just the entity name prefix might not be
   *   sufficient in any case.
   */
  private function isApi3RemoteRequest(mixed $request): bool {
    return is_array($request) && is_string($request['entity']) && str_starts_with($request['entity'], 'Remote')
      && is_array($request['params']) && array_key_exists('remote_contact_id', $request['params'])
      && is_string($request['params']['remote_contact_id'] ?? '')
      && is_string($request['action']) && is_int($request['id']);
  }

  /**
   * @throws \Civi\RemoteTools\Exception\ResolveContactIdFailedException
   */
  private function resolveContactId(RemoteActionInterface $request): ?int {
    if (NULL === $request->getRemoteContactId() || '' === $request->getRemoteContactId()) {
      return NULL;
    }

    return $this->remoteContactIdResolverProvider->get($request)
      ->getContactId($request->getRemoteContactId());
  }

  /**
   * @phpstan-param api3RemoteRequestT $request
   *
   * @throws \Civi\RemoteTools\Exception\ResolveContactIdFailedException
   */
  private function resolveContactIdForApi3(array $request): ?int {
    $remoteContactId = $request['params']['remote_contact_id'];
    if (NULL === $remoteContactId || '' === $remoteContactId) {
      return NULL;
    }

    return $this->remoteContactIdResolverProvider->getByApi3Request($request)
      ->getContactId($remoteContactId);
  }

}
