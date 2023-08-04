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

namespace Civi\RemoteTools\ActionHandler;

use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\EntityProfile\EntityProfileJsonSchemaValidationDecorator;
use Civi\RemoteTools\EntityProfile\Helper\ProfileEntityDeleterInterface;
use Civi\RemoteTools\EntityProfile\Helper\ProfileEntityLoaderInterface;
use Civi\RemoteTools\EntityProfile\RemoteEntityProfileInterface;
use Civi\RemoteTools\Form\FormSpec\FormSpec;
use Civi\RemoteTools\JsonForms\FormSpec\UiSchemaFactoryInterface;
use Civi\RemoteTools\JsonSchema\FormSpec\JsonSchemaFactoryInterface;
use Civi\RemoteTools\JsonSchema\Validation\ValidatorInterface as JsonSchemaValidatorInterface;

class JsonFormsRemoteActionsHandler extends AbstractProfileEntityActionsHandler {

  private JsonSchemaFactoryInterface $jsonSchemaFactory;

  private UiSchemaFactoryInterface $uiSchemaFactory;

  public function __construct(
    Api4Interface $api4,
    ProfileEntityDeleterInterface $entityDeleter,
    ProfileEntityLoaderInterface $entityLoader,
    RemoteEntityProfileInterface $profile,
    JsonSchemaFactoryInterface $jsonSchemaFactory,
    UiSchemaFactoryInterface $uiSchemaFactory,
    JsonSchemaValidatorInterface $jsonSchemaValidator
  ) {
    $profile = new EntityProfileJsonSchemaValidationDecorator($profile, $jsonSchemaFactory, $jsonSchemaValidator);
    parent::__construct($api4, $entityDeleter, $entityLoader, $profile);
    $this->jsonSchemaFactory = $jsonSchemaFactory;
    $this->uiSchemaFactory = $uiSchemaFactory;
  }

  protected function convertToGetFormActionResult(FormSpec $formSpec): array {
    return [
      'jsonSchema' => $this->jsonSchemaFactory->createJsonSchema($formSpec),
      'uiSchema' => $this->uiSchemaFactory->createUiSchema($formSpec),
    ];
  }

}
