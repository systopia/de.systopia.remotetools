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

use Civi\Api4\Generic\AbstractAction;
use Civi\RemoteTools\ActionHandler\ActionHandlerInterface;
use Civi\RemoteTools\ActionHandler\ActionHandlerProvider;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Webmozart\Assert\Assert;

final class ActionHandlerPass implements CompilerPassInterface {

  public static function buildHandlerKey(string $entityName, string $actionName, ?string $profileName): string {
    if (NULL === $profileName) {
      return $entityName . '.' . $actionName;
    }

    return $entityName . '.' . $actionName . '@' . $profileName;
  }

  /**
   * @inheritDoc
   */
  public function process(ContainerBuilder $container): void {
    $handlerServices = [];
    $handlerPriorities = [];
    foreach ($container->findTaggedServiceIds(ActionHandlerInterface::SERVICE_TAG) as $id => $tags) {
      foreach ($tags as $attributes) {
        $entityName = $this->getAttribute($id, 'entity_name', $attributes);
        $profileName = $attributes['profile_name'] ?? NULL;
        $priority = $attributes['priority'] ?? 0;
        $serviceClass = $this->getServiceClass($container, $id);
        foreach ($this->getActionMethodNames($serviceClass) as $actionName) {
          $handlerKey = self::buildHandlerKey($entityName, $actionName, $profileName);
          if (isset($handlerPriorities[$handlerKey])) {
            if ($handlerPriorities[$handlerKey] === $priority) {
              // @todo: Only fail if there is a duplicate with the highest priority.
              throw new \RuntimeException(
                sprintf(
                  'Duplicate action handler (entity name: %s, action name: %s, profile name: %s',
                  $entityName,
                  $actionName,
                  $profileName
                )
              );
            }

            if ($handlerPriorities[$handlerKey] > $priority) {
              continue;
            }
          }

          $handlerPriorities[$handlerKey] = $priority;
          $handlerServices[$handlerKey] = new Reference($id);
        }
      }
    }

    $container->getDefinition(ActionHandlerProvider::class)
      ->addArgument(ServiceLocatorTagPass::register($container, $handlerServices));
  }

  /**
   * @phpstan-param array<int|string, scalar> $attributes
   */
  private function getAttribute(string $id, string $key, array $attributes): string {
    Assert::keyExists(
      $attributes,
      $key,
      sprintf('Attribute "%s" in tag "%s" of service "%s" is missing', $key, ActionHandlerInterface::SERVICE_TAG, $id)
    );
    Assert::string($attributes[$key], sprintf(
      'Attribute "%s" in tag "%s" of service "%s" expected to be string, got %s',
      $key, ActionHandlerInterface::SERVICE_TAG,
      $id,
      gettype($attributes[$key])
    ));

    return $attributes[$key];
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

  /**
   * @phpstan-param class-string $class
   *
   * @phpstan-return iterable<string>
   */
  private function getActionMethodNames(string $class): iterable {
    $reflClass = new \ReflectionClass($class);
    foreach ($reflClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $reflMethod) {
      $parameters = $reflMethod->getParameters();
      if (0 === count($parameters) || count($parameters) > 1 && !$parameters[1]->isOptional()) {
        continue;
      }

      $parameterType = $parameters[0]->getType();
      if (NULL !== $parameterType && TRUE !== $this->isClassAllowedByType(AbstractAction::class, $parameterType)) {
        continue;
      }

      yield $reflMethod->getName();
    }
  }

  /**
   * @phpstan-param class-string $class
   */
  private function isClassAllowedByType(string $class, \ReflectionType $reflType): ?bool {
    if ($reflType instanceof \ReflectionNamedType) {
      return is_a($reflType->getName(), $class, TRUE);
    }

    if ($reflType instanceof \ReflectionUnionType) {
      foreach ($reflType->getTypes() as $reflTypeInUnion) {
        if (TRUE === $this->isClassAllowedByType($class, $reflTypeInUnion)) {
          return TRUE;
        }
      }

      return FALSE;
    }

    if ($reflType instanceof \ReflectionIntersectionType) {
      foreach ($reflType->getTypes() as $reflTypeInIntersection) {
        if (TRUE === $this->isClassAllowedByType($class, $reflTypeInIntersection)) {
          return TRUE;
        }
      }

      return FALSE;
    }

    return NULL;
  }

}
