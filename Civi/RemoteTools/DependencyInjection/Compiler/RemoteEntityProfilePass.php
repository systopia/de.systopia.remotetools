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

namespace Civi\RemoteTools\DependencyInjection\Compiler;

use Civi\RemoteTools\ActionHandler\ActionHandlerInterface;
use Civi\RemoteTools\ActionHandler\JsonFormsRemoteActionsHandler;
use Civi\RemoteTools\DependencyInjection\Compiler\Traits\DecorateServiceTrait;
use Civi\RemoteTools\EntityProfile\EntityProfileFileDecorator;
use Civi\RemoteTools\EntityProfile\RemoteEntityProfileInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Webmozart\Assert\Assert;

/**
 * @codeCoverageIgnore
 */
final class RemoteEntityProfilePass implements CompilerPassInterface {

  use DecorateServiceTrait;

  private const DEFAULT_HANDLER_CLASS = JsonFormsRemoteActionsHandler::class;

  public static function buildHandlerServiceId(string $entityName, string $profileName): string {
    return sprintf('remote_tools.action.handler:%s@%s', $entityName, $profileName);
  }

  /**
   * @inheritDoc
   */
  public function process(ContainerBuilder $container): void {
    $profileIds = [];
    foreach ($container->findTaggedServiceIds(RemoteEntityProfileInterface::SERVICE_TAG) as $id => $tags) {
      foreach ($tags as $attributes) {
        $remoteEntityName = $this->getAttributeOrConst($container, $id, 'remote_entity_name', $attributes);
        $profileName = $this->getAttributeOrConst($container, $id, 'name', $attributes);

        $profileId = $remoteEntityName . '@' . $profileName;
        Assert::keyNotExists(
          $profileIds,
          $profileId,
          sprintf('Duplicate profile with remote entity "%s" and name "%s"', $remoteEntityName, $profileName)
        );
        $profileIds[] = $profileId;

        $handlerClass = $this->getAttributeOrConst(
          $container,
          $id,
          'handler_class',
          $attributes,
          self::DEFAULT_HANDLER_CLASS
        );
        $container->autowire(self::buildHandlerServiceId($remoteEntityName, $profileName), $handlerClass)
          ->setArgument('$profile', new Reference($id))
          ->addTag(ActionHandlerInterface::SERVICE_TAG, [
            'entity_name' => $remoteEntityName,
            'profile_name' => $profileName,
            'priority' => -1000,
          ]);

        $this->decorateService($container, $id, EntityProfileFileDecorator::class, $profileId);
      }
    }
  }

  /**
   * @phpstan-param array<int|string, scalar> $attributes
   */
  private function getAttributeOrConst(
    ContainerBuilder $container,
    string $id,
    string $key,
    array $attributes,
    string $default = NULL
  ): string {
    if (isset($attributes[$key])) {
      Assert::string($attributes[$key], sprintf(
        'Attribute "%s" in tag "%s" of service "%s" expected to be string, got %s',
        $key,
        RemoteEntityProfileInterface::SERVICE_TAG,
        $id,
        gettype($attributes[$key])
      ));

      return $attributes[$key];
    }

    $constantName = $this->getServiceClass($container, $id) . '::' . strtoupper($key);
    if (defined($constantName)) {
      // @phpstan-ignore-next-line
      return constant($constantName);
    }

    if (NULL !== $default) {
      return $default;
    }

    throw new \RuntimeException(sprintf(
      'Neither attribute "%s" in tag "%s" of service "%s" nor constant "%s" exists',
      $key,
      RemoteEntityProfileInterface::SERVICE_TAG,
      $id,
      $constantName
    ));
  }

  /**
   * @phpstan-return class-string
   */
  private function getServiceClass(ContainerBuilder $container, string $id): string {
    $definition = $container->getDefinition($id);

    /** @phpstan-var class-string $class */
    $class = $definition->getClass() ?? $id;

    return $class;
  }

}
