<?php
declare(strict_types = 1);

namespace Civi\RemoteTools\Form\FormSpec\Field;

/**
 * @codeCoverageIgnore
 */
final class MultilineTextField extends AbstractTextField {

  public function getInputType(): string {
    return 'multilineText';
  }

}
