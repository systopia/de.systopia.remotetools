<?php
declare(strict_types = 1);

namespace Civi\RemoteTools\Form\FormSpec\Field;

/**
 * @extends AbstractOptionField<int|string>
 *
 * @codeCoverageIgnore
 *
 * @api
 */
class SelectField extends AbstractOptionField {

  public function getDataType(): string {
    return 'scalar';
  }

  public function getInputType(): string {
    return 'select';
  }

}
