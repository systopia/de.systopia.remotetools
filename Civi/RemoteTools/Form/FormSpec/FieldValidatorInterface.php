<?php
declare(strict_types = 1);

namespace Civi\RemoteTools\Form\FormSpec;

/**
 * @template T of AbstractFormField
 *
 * @api
 */
interface FieldValidatorInterface {

  /**
   * @phpstan-param T $field
   *
   * @phpstan-return list<string>
   *   Validation error messages.
   */
  public function validate(mixed $value, AbstractFormField $field): array;

}
