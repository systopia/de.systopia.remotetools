<?php
declare(strict_types = 1);

namespace Civi\RemoteTools\Form\FormSpec;

use Civi\RemoteTools\Form\Validation\ValidationError;
use Civi\RemoteTools\Form\Validation\ValidationResult;

final class FormSpecValidator implements ValidatorInterface {

  private FormSpec $formSpec;

  public function __construct(FormSpec $formSpec) {
    $this->formSpec = $formSpec;
  }

  /**
   * @inheritDoc
   */
  public function validate(array $formData, ?array $currentEntityValues, ?int $contactId): ValidationResult {
    $result = new ValidationResult();
    $fields = $this->formSpec->getFields();
    foreach ($formData as $fieldName => $value) {
      $field = $fields[$fieldName] ?? NULL;
      if (NULL !== $field) {
        foreach ($field->getValidator()->validate($value, $field) as $message) {
          $result->addError(ValidationError::new($fieldName, $message));
        }
      }
    }

    return $result;
  }

}
