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

// phpcs:disable Drupal.Commenting.DocComment.ContentAfterOpen
/** @var \Symfony\Component\DependencyInjection\ContainerBuilder $container */

use Civi\RemoteTools\ActionHandler\ActionHandlerInterface;
use Civi\RemoteTools\ActionHandler\ActionHandlerProvider;
use Civi\RemoteTools\ActionHandler\ActionHandlerProviderCollection;
use Civi\RemoteTools\ActionHandler\ActionHandlerProviderInterface;
use Civi\RemoteTools\ActionHandler\DefaultActionHandler;
use Civi\RemoteTools\Api3\Api3;
use Civi\RemoteTools\Api3\Api3Interface;
use Civi\RemoteTools\Api4\Api4;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\Contact\IdentityTrackerRemoteContactIdResolver;
use Civi\RemoteTools\Contact\RemoteContactIdResolverInterface;
use Civi\RemoteTools\Contact\RemoteContactIdResolverProvider;
use Civi\RemoteTools\Contact\RemoteContactIdResolverProviderInterface;
use Civi\RemoteTools\DependencyInjection\Compiler\ActionHandlerPass;
use Civi\RemoteTools\DependencyInjection\Compiler\RemoteEntityProfilePass;
use Civi\RemoteTools\EventSubscriber\RemoteRequestInitSubscriber;
use Civi\RemoteTools\RequestContext\RequestContext;
use Civi\RemoteTools\RequestContext\RequestContextInterface;
use Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;

$container->addCompilerPass(new RemoteEntityProfilePass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, -1);
$container->addCompilerPass(new ActionHandlerPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, -2);

$container->register(Api4Interface::class, Api4::class);
$container->register(Api3Interface::class, Api3::class);

$container->autowire(RequestContextInterface::class, RequestContext::class)
  ->setPublic(TRUE);

$container->autowire(ActionHandlerProviderInterface::class, ActionHandlerProviderCollection::class)
  ->addArgument(new TaggedIteratorArgument(ActionHandlerProviderInterface::SERVICE_TAG));

$container->autowire(ActionHandlerProvider::class)
  ->addTag(ActionHandlerProvider::SERVICE_TAG);
$container->autowire(ActionHandlerInterface::class, DefaultActionHandler::class)
  ->setPublic(TRUE);

$container->autowire(RemoteContactIdResolverInterface::class, IdentityTrackerRemoteContactIdResolver::class);
$container->autowire(RemoteContactIdResolverProviderInterface::class, RemoteContactIdResolverProvider::class);

$container->autowire(RemoteRequestInitSubscriber::class)
  ->addTag('kernel.event_subscriber');
