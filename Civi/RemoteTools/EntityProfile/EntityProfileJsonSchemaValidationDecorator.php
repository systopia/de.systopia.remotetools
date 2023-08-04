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

namespace Civi\RemoteTools\EntityProfile;

use Civi\RemoteTools\Form\FormSpec\FormSpec;
use Civi\RemoteTools\JsonSchema\FormSpec\JsonSchemaFactoryInterface;
use Civi\RemoteTools\JsonSchema\FormSpec\JsonSchemaFormSpecValidator;
use Civi\RemoteTools\JsonSchema\Validation\ValidatorInterface as JsonSchemaValidatorInterface;

final class EntityProfileJsonSchemaValidationDecorator extends AbstractRemoteEntityProfileDecorator {

  private JsonSchemaFactoryInterface $jsonSchemaFactory;

  private JsonSchemaValidatorInterface $jsonSchemaValidator;

  public function __construct(
    RemoteEntityProfileInterface $profile,
    JsonSchemaFactoryInterface $jsonSchemaFactory,
    JsonSchemaValidatorInterface $jsonSchemaValidator
  ) {
    parent::__construct($profile);
    $this->jsonSchemaFactory = $jsonSchemaFactory;
    $this->jsonSchemaValidator = $jsonSchemaValidator;
  }

  public function getCreateFormSpec(array $arguments, array $entityFields, ?int $contactId): FormSpec {
    $formSpec = parent::getCreateFormSpec($arguments, $entityFields, $contactId);
    $formSpec->prependValidator(
      new JsonSchemaFormSpecValidator($formSpec, $this->jsonSchemaFactory, $this->jsonSchemaValidator)
    );

    return $formSpec;
  }

  public function getUpdateFormSpec(array $entityValues, array $entityFields, ?int $contactId): FormSpec {
    $formSpec = parent::getUpdateFormSpec($entityValues, $entityFields, $contactId);
    $formSpec->prependValidator(
      new JsonSchemaFormSpecValidator($formSpec, $this->jsonSchemaFactory, $this->jsonSchemaValidator)
    );

    return $formSpec;
  }

}
