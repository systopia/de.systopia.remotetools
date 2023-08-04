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

namespace Civi\RemoteTools\JsonSchema\FormSpec;

use Civi\RemoteTools\Form\FormSpec\FormSpec;
use Civi\RemoteTools\Form\FormSpec\ValidatorInterface;
use Civi\RemoteTools\Form\Validation\ValidationError;
use Civi\RemoteTools\Form\Validation\ValidationResult;
use Civi\RemoteTools\JsonSchema\Validation\ValidatorInterface as JsonSchemaValidatorInterface;

final class JsonSchemaFormSpecValidator implements ValidatorInterface {

  private FormSpec $formSpec;

  private JsonSchemaFactoryInterface $jsonSchemaFactory;

  private JsonSchemaValidatorInterface $jsonSchemaValidator;

  public function __construct(
    FormSpec $formSpec,
    JsonSchemaFactoryInterface $jsonSchemaFactory,
    JsonSchemaValidatorInterface $jsonSchemaValidator
  ) {
    $this->formSpec = $formSpec;
    $this->jsonSchemaFactory = $jsonSchemaFactory;
    $this->jsonSchemaValidator = $jsonSchemaValidator;
  }

  public function validate(array $formData, ?array $currentEntityValues, ?int $contactId): ValidationResult {
    $jsonSchema = $this->jsonSchemaFactory->createJsonSchema($this->formSpec);
    $result = ValidationResult::new();
    $leafErrorMessages = $this->jsonSchemaValidator->validate($jsonSchema, $formData, 20)->getLeafErrorMessages();

    foreach ($leafErrorMessages as $field => $messages) {
      $errors = array_map(
        // Convert JSON pointer to field name.
        fn (string $message) => ValidationError::new(ltrim($field, '/'), $message),
        $messages
      );
      $result->addErrors(...$errors);
    }

    return $result;
  }

}
