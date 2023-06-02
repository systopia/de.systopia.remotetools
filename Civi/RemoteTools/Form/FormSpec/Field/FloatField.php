<?php
declare(strict_types = 1);

namespace Civi\RemoteTools\Form\FormSpec\Field;

/**
 * @extends AbstractNumberField<float>
 *
 * @codeCoverageIgnore
 */
class FloatField extends AbstractNumberField {

  private ?int $precision = NULL;

  public function getDataType(): string {
    return 'number';
  }

  public function getFieldType(): string {
    return 'float';
  }

  public function getPrecision(): ?int {
    return $this->precision;
  }

  public function setPrecision(?int $precision): self {
    $this->precision = $precision;

    return $this;
  }

}
