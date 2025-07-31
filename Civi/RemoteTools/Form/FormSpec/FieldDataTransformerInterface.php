<?php
declare(strict_types = 1);

namespace Civi\RemoteTools\Form\FormSpec;

/**
 * @template T of \Civi\RemoteTools\Form\FormSpec\AbstractFormField
 *
 * @api
 */
interface FieldDataTransformerInterface {

  /**
   * @phpstan-param T $field
   */
  public function toEntityValue(mixed $data, AbstractFormField $field): mixed;

}
