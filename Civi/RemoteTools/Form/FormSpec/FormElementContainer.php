<?php
declare(strict_types = 1);

namespace Civi\RemoteTools\Form\FormSpec;

/**
 * @codeCoverageIgnore
 */
class FormElementContainer extends AbstractFormElementContainer implements FormElementInterface {

  public function getType(): string {
    return 'container';
  }

}
