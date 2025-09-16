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
   * phpcs:disable Drupal.Commenting.FunctionComment.ParamNameNoMatch, Squiz.PHP.CommentedOutCode.Found
   * @param list<mixed>|null $defaultValuesInList
   *   The default values for this field when used in a FieldListField. This
   *   parameter will be required in implementations from version 2.0.0 on.
   *
   * @phpstan-ignore parameter.notFound
   */
  public function toEntityValue(mixed $data, AbstractFormField $field/*, ?array $defaultValuesInList = NULL*/): mixed;

}
