<?php
declare(strict_types = 1);

namespace Civi\RemoteTools\Form\FormSpec\Field;

/**
 * @extends AbstractNumberField<int>
 *
 * @codeCoverageIgnore
 */
final class IntegerField extends AbstractNumberField {

  private ?int $maximum = NULL;

  private ?int $minimum = NULL;

  public function getDataType(): string {
    return 'integer';
  }

  public function getInputType(): string {
    return 'integer';
  }

  public function getMaximum(): ?int {
    return $this->maximum;
  }

  public function setMaximum(?int $maximum): self {
    $this->maximum = $maximum;

    return $this;
  }

  public function getMinimum(): ?int {
    return $this->minimum;
  }

  public function setMinimum(?int $minimum): self {
    $this->minimum = $minimum;

    return $this;
  }

}
