<?php
declare(strict_types = 1);

namespace Civi\RemoteTools\Form\FormSpec;

/**
 * @api
 */
interface FormElementInterface {

  public function getType(): string;

}
