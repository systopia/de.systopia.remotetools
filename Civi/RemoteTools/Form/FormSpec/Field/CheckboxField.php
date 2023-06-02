<?php
declare(strict_types = 1);

namespace Civi\RemoteTools\Form\FormSpec\Field;

use Civi\RemoteTools\Form\FormSpec\AbstractFormField;

/**
 * @extends AbstractFormField<bool>
 *
 * @codeCoverageIgnore
 */
final class CheckboxField extends AbstractFormField {

  public function getDataType(): string {
    return 'boolean';
  }

  public function getFieldType(): string {
    return 'checkbox';
  }

}
