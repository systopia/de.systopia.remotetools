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
  public function toEntityValue(mixed $data, AbstractFormField $field, ?array $defaultValuesInList = NULL): mixed {
    return $data;
  }

}
