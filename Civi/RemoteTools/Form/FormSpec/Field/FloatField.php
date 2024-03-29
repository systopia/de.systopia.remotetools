<?php
declare(strict_types = 1);

namespace Civi\RemoteTools\Form\FormSpec\Field;

/**
 * @extends AbstractNumberField<float>
 *
 * @codeCoverageIgnore
 *
 * @api
 */
class FloatField extends AbstractNumberField {

  private ?int $precision = NULL;

  public function getDataType(): string {
    return 'number';
  }

  public function getInputType(): string {
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
