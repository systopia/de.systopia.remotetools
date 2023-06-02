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

namespace Civi\RemoteTools\Form\Validation;

use Civi\RemoteTools\Exception\ValidationFailedException;
use CRM_Remotetools_ExtensionUtil as E;

class ValidationResult {

  /**
   * @phpstan-var array<string, non-empty-array<ValidationError>>
   */
  private array $errors = [];

  public static function new(ValidationError ...$errors): self {
    return new self(...$errors);
  }

  public function __construct(ValidationError ...$errors) {
    $this->addErrors(...$errors);
  }

  public function addErrors(ValidationError ...$errors): self {
    foreach ($errors as $error) {
      $this->addError($error);
    }

    return $this;
  }

  public function addError(ValidationError $error): self {
    if (isset($this->errors[$error->field])) {
      $this->errors[$error->field][] = $error;
    }
    else {
      $this->errors[$error->field] = [$error];
    }

    return $this;
  }

  /**
   * @phpstan-return array<string, non-empty-array<ValidationError>>
   *   Field names mapped to errors.
   */
  public function getErrors(): array {
    return $this->errors;
  }

  /**
   * @phpstan-return array<ValidationError>
   */
  public function getErrorsFlat(): array {
    $errors = [];
    foreach ($this->errors as $fieldErrors) {
      foreach ($fieldErrors as $error) {
        $errors[] = $error;
      }
    }

    return $errors;
  }

  public function hasErrors(): bool {
    return [] !== $this->errors;
  }

  /**
   * @phpstan-return array<string, non-empty-array<string>>
   *   Field names mapped to error messages.
   */
  public function getErrorMessages(): array {
    return array_map(
      fn (array $fieldErrors) => array_map(
        fn (ValidationError $fieldError) => $fieldError->message,
        $fieldErrors
      ),
      $this->errors
    );
  }

  /**
   * @phpstan-return array<ValidationError>
   */
  public function getErrorsFor(string $field): array {
    return $this->errors[$field] ?? [];
  }

  public function hasErrorsFor(string $field): bool {
    return isset($this->errors[$field]);
  }

  public function isValid(): bool {
    return [] === $this->errors;
  }

  public function merge(self $result): self {
    $this->addErrors(...$result->getErrorsFlat());

    return $this;
  }

  public function toException(): ValidationFailedException {
    if ($this->isValid()) {
      throw new \RuntimeException('No errors in validation result');
    }

    $errorMessages = implode(', ', array_map(
      fn (ValidationError $error) => $error->message,
      $this->getErrorsFlat()
    ));

    return new ValidationFailedException(E::ts('Validation failed: %1', [1 => $errorMessages]));
  }

}
