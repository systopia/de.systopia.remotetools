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

namespace Civi\RemoteTools\JsonSchema\Validation;

use Civi\RemoteTools\JsonSchema\JsonSchema;
use Civi\RemoteTools\Util\JsonConverter;
use Opis\JsonSchema\Validator as OpisValidator;
use Systopia\JsonSchema\Errors\ErrorCollector;
use Systopia\JsonSchema\Tags\TaggedDataContainer;
use Systopia\JsonSchema\Translation\TranslatorInterface;

final class Validator implements ValidatorInterface {

  private TranslatorInterface $translator;

  private OpisValidator $validator;

  public function __construct(TranslatorInterface $translator, OpisValidator $validator) {
    $this->translator = $translator;
    $this->validator = $validator;
  }

  /**
   * @inheritDoc
   * @throws \JsonException
   */
  public function validate(JsonSchema $jsonSchema, array $data, int $maxErrors = 1): ValidationResultInterface {
    $validationData = JsonConverter::toStdClass($data);
    $errorCollector = new ErrorCollector();
    $taggedDataContainer = new TaggedDataContainer();
    $prevMaxErrors = $this->validator->getMaxErrors();
    try {
      $this->validator->setMaxErrors($maxErrors);
      $this->validator->validate($validationData, $jsonSchema->toStdClass(), [
        'errorCollector' => $errorCollector,
        'taggedDataContainer' => $taggedDataContainer,
      ]);
    }
    finally {
      $this->validator->setMaxErrors($prevMaxErrors);
    }

    return new ValidationResult(
      JsonConverter::toArray($validationData),
      $taggedDataContainer,
      $errorCollector,
      $this->translator
    );
  }

}
