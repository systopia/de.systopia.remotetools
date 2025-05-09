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
   * @param mixed $value
   * @phpstan-param T $field
   *
   * @phpstan-return list<string>
   *   Validation error messages.
   */
  public function validate($value, AbstractFormField $field): array;

}
