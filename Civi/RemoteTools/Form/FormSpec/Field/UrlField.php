<?php
declare(strict_types = 1);

namespace Civi\RemoteTools\Form\FormSpec\Field;

use Civi\RemoteTools\Form\FormSpec\AbstractFormField;

/**
 * @extends AbstractFormField<string>
 *
 * @codeCoverageIgnore
 */
final class UrlField extends AbstractFormField {

  public function getDataType(): string {
    return 'string';
  }

  public function getFieldType(): string {
    return 'url';
  }

}