<?php
declare(strict_types = 1);

namespace Civi\RemoteTools\Form\FormSpec\Field;

use Civi\RemoteTools\Form\FormSpec\AbstractFormField;

/**
 * @template T of int|float
 *
 * @extends AbstractFormField<T>
 *
 * @codeCoverageIgnore
 */
abstract class AbstractNumberField extends AbstractFormField {

  private ?int $maximum = NULL;

  private ?int $minimum = NULL;

  public function getMaximum(): ?int {
    return $this->maximum;
  }

  /**
   * @return $this
   */
  public function setMaximum(?int $maximum): self {
    $this->maximum = $maximum;

    return $this;
  }

  public function getMinimum(): ?int {
    return $this->minimum;
  }

  /**
   * @return $this
   */
  public function setMinimum(?int $minimum): self {
    $this->minimum = $minimum;

    return $this;
  }

}
