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
   * @param mixed $data
   * @phpstan-param T $field
   *
   * @return mixed
   */
  public function toEntityValue($data, AbstractFormField $field);

}
