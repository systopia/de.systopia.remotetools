<?php
declare(strict_types = 1);

namespace Civi\RemoteTools\Form\FormSpec;

final class IdentityFieldDataTransformer implements FieldDataTransformerInterface {

  private static self $instance;

  public static function getInstance(): self {
    return self::$instance ??= new self();
  }

  /**
   * @inheritDoc
   */
  public function toEntityValue($data, AbstractFormField $field) {
    return $data;
  }

}
