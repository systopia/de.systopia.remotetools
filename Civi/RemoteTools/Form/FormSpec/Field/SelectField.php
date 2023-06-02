<?php
declare(strict_types = 1);

namespace Civi\RemoteTools\Form\FormSpec\Field;

/**
 * @extends AbstractOptionField<scalar>
 *
 * @codeCoverageIgnore
 */
class SelectField extends AbstractOptionField {

  public function getDataType(): string {
    return 'scalar';
  }

  public function getFieldType(): string {
    return 'select';
  }

}
