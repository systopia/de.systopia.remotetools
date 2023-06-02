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

use Civi\RemoteTools\DependencyInjection\Util\ServiceRegistrator;
use Civi\RemoteTools\JsonForms\FormSpec\ConcreteElementUiSchemaFactoryInterface;
use Civi\RemoteTools\JsonForms\FormSpec\ElementUiSchemaFactory;
use Civi\RemoteTools\JsonForms\FormSpec\ElementUiSchemaFactoryInterface;
use Civi\RemoteTools\JsonForms\FormSpec\UiSchemaFactory;
use Civi\RemoteTools\JsonForms\FormSpec\UiSchemaFactoryInterface;
use Civi\RemoteTools\JsonSchema\FormSpec\FieldJsonSchemaFactoryInterface;
use Civi\RemoteTools\JsonSchema\FormSpec\JsonSchemaFactory;
use Civi\RemoteTools\JsonSchema\FormSpec\JsonSchemaFactoryInterface;
use Civi\RemoteTools\JsonSchema\Validation\OpisValidatorFactory;
use Civi\RemoteTools\JsonSchema\Validation\Validator;
use Civi\RemoteTools\JsonSchema\Validation\ValidatorInterface;
use Opis\JsonSchema\Validator as OpisValidator;
use Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument;

$container->register(OpisValidator::class)->setFactory([OpisValidatorFactory::class, 'getValidator']);
$container->autowire(ValidatorInterface::class, Validator::class);

$container->autowire(JsonSchemaFactoryInterface::class, JsonSchemaFactory::class)
  ->addArgument(new TaggedIteratorArgument(FieldJsonSchemaFactoryInterface::SERVICE_TAG));

ServiceRegistrator::autowireAllImplementing(
  $container,
  __DIR__ . '/../Civi/RemoteTools/JsonSchema/FormSpec/Factory',
  'Civi\\RemoteTools\\JsonSchema\\FormSpec\\Factory',
  FieldJsonSchemaFactoryInterface::class,
  [FieldJsonSchemaFactoryInterface::SERVICE_TAG => []],
);

$container->autowire(UiSchemaFactoryInterface::class, UiSchemaFactory::class);
$container->autowire(ElementUiSchemaFactoryInterface::class, ElementUiSchemaFactory::class)
  ->addArgument(new TaggedIteratorArgument(ConcreteElementUiSchemaFactoryInterface::SERVICE_TAG));

ServiceRegistrator::autowireAllImplementing(
  $container,
  __DIR__ . '/../Civi/RemoteTools/JsonForms/FormSpec/Factory',
  'Civi\\RemoteTools\\JsonForms\\FormSpec\\Factory',
  ConcreteElementUiSchemaFactoryInterface::class,
  [ConcreteElementUiSchemaFactoryInterface::SERVICE_TAG => []],
);
