<?php
declare(strict_types = 1);

namespace Civi\RemoteTools\Form\FormSpec\Util;

use Civi\RemoteTools\Form\FormSpec\FormSpec;

final class ReadOnlyFieldsRemover {

  /**
   * @phpstan-param array<string, mixed> $values
   *
   * @phpstan-return array<string, mixed>
   */
  public static function removeReadOnlyFields(FormSpec $formSpec, array $values): array {
    foreach ($formSpec->getFields() as $field) {
      if ($field->isReadOnly()) {
        unset($values[$field->getName()]);
      }
    }

    return $values;
  }

}
