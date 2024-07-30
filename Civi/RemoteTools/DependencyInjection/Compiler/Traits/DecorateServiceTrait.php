<?php
declare(strict_types = 1);

namespace Civi\RemoteTools\DependencyInjection\Compiler\Traits;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

trait DecorateServiceTrait {

  /**
   * @phpstan-param class-string $decoratorClass
   * @param int|string $argumentKey
   */
  protected function decorateService(
    ContainerBuilder $container,
    string $id,
    string $decoratorClass,
    string $serviceIdPostfix,
    $argumentKey = 0
  ): void {
    $decoratorId = $decoratorClass . ':' . $serviceIdPostfix;
    $container->autowire($decoratorId, $decoratorClass)
      ->setDecoratedService($id)
      ->setArgument($argumentKey, new Reference($decoratorId . '.inner'));
  }

}
