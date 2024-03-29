<?php
declare(strict_types = 1);

namespace Civi\RemoteTools\Form\FormSpec\Field;

use Civi\RemoteTools\Form\FormSpec\AbstractFormField;

/**
 * @extends AbstractFormField<bool>
 *
 * @codeCoverageIgnore
 *
 * @api
 */
final class CheckboxField extends AbstractFormField {

  public function getDataType(): string {
    return 'boolean';
  }

  public function getInputType(): string {
    return 'checkbox';
  }

}
