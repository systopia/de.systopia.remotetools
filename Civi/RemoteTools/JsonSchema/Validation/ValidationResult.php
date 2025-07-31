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

use Opis\JsonSchema\Errors\ValidationError;
use Systopia\JsonSchema\Errors\ErrorCollector;
use Systopia\JsonSchema\Tags\TaggedDataContainerInterface;
use Systopia\JsonSchema\Translation\ErrorTranslator;
use Systopia\JsonSchema\Translation\NullTranslator;
use Systopia\JsonSchema\Translation\TranslatorInterface;

final class ValidationResult implements ValidationResultInterface {

  /**
   * @var array<string, mixed>
   */
  private array $data;

  private TaggedDataContainerInterface $taggedData;

  private ErrorCollector $errorCollector;

  private ErrorTranslator $errorTranslator;

  /**
   * @param array<string, mixed> $data
   * @param \Systopia\JsonSchema\Errors\ErrorCollector $errorCollector
   */
  public function __construct(
    array $data,
    TaggedDataContainerInterface $taggedData,
    ErrorCollector $errorCollector,
    ?TranslatorInterface $translator = NULL
  ) {
    $this->data = $data;
    $this->taggedData = $taggedData;
    $this->errorCollector = $errorCollector;
    $this->errorTranslator = new ErrorTranslator($translator ?? new NullTranslator());
  }

  /**
   * @return array<string, mixed>
   */
  public function getData(): array {
    return $this->data;
  }

  public function getTaggedData(): TaggedDataContainerInterface {
    return $this->taggedData;
  }

  /**
   * @return array<string, non-empty-list<string>>
   */
  public function getErrorMessages(): array {
    // @phpstan-ignore argument.type
    return $this->mapErrorsToMessages($this->errorCollector->getErrors());
  }

  /**
   * @return array<string, non-empty-list<string>>
   */
  public function getLeafErrorMessages(): array {
    // @phpstan-ignore argument.type
    return $this->mapErrorsToMessages($this->errorCollector->getLeafErrors());
  }

  public function hasErrors(): bool {
    return $this->errorCollector->hasErrors();
  }

  public function isValid(): bool {
    return !$this->errorCollector->hasErrors();
  }

  /**
   * @param array<string, non-empty-list<ValidationError>> $errors
   *
   * @return array<string, non-empty-list<string>>
   */
  private function mapErrorsToMessages(array $errors): array {
    return array_map(
      fn (array $errors): array => array_map(
        fn (ValidationError $error): string => $this->errorTranslator->trans($error),
        $errors
      ), $errors
    );
  }

}
