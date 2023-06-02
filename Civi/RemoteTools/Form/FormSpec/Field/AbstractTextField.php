<?php
declare(strict_types = 1);

namespace Civi\RemoteTools\Form\FormSpec\Field;

use Civi\RemoteTools\Form\FormSpec\AbstractFormField;

/**
 * @extends AbstractFormField<string>
 *
 * @codeCoverageIgnore
 */
abstract class AbstractTextField extends AbstractFormField {

  private ?int $maxLength = NULL;

  private ?int $minLength = NULL;

  private ?string $pattern = NULL;

  public function getDataType(): string {
    return 'string';
  }

  public function getMaxLength(): ?int {
    return $this->maxLength;
  }

  /**
   * @return $this
   */
  public function setMaxLength(?int $maxLength): self {
    $this->maxLength = $maxLength;

    return $this;
  }

  public function getMinLength(): ?int {
    return $this->minLength;
  }

  /**
   * @return $this
   */
  public function setMinLength(?int $minLength): self {
    $this->minLength = $minLength;

    return $this;
  }

  public function getPattern(): ?string {
    return $this->pattern;
  }

  /**
   * @return $this
   */
  public function setPattern(?string $pattern): self {
    $this->pattern = $pattern;

    return $this;
  }

}
