<?php
declare(strict_types = 1);

namespace Civi\RemoteTools\Form\FormSpec;

/**
 * @implements FieldValidatorInterface<AbstractFormField>
 */
final class NullFieldValidator implements FieldValidatorInterface {

  private static self $instance;

  public static function getInstance(): self {
    return self::$instance ??= new self();
  }

  /**
   * @inheritDoc
   */
  public function validate($value, AbstractFormField $field): array {
    return [];
  }

}
