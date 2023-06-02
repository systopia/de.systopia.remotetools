<?php
declare(strict_types = 1);

namespace Civi\RemoteTools\Form\FormSpec\Field;

use Civi\RemoteTools\Form\FormSpec\AbstractFormField;

/**
 * @template T of scalar
 *
 * @extends AbstractFormField<T>
 *
 * @codeCoverageIgnore
 */
abstract class AbstractOptionField extends AbstractFormField {

  /**
   * @phpstan-var array<string, T>
   *   Maps labels to values.
   */
  private array $options;

  /**
   * @param array<string, T> $options
   *   Maps labels to values.
   */
  public function __construct(string $name, string $label, array $options) {
    parent::__construct($name, $label);
    $this->options = $options;
  }

  public function isNullable(): bool {
    return in_array(NULL, $this->getOptions(), TRUE) || parent::isNullable();
  }

  /**
   * @param T $value
   */
  public function addOption(string $label, $value): self {
    $this->options[$label] = $value;

    return $this;
  }

  /**
   * @phpstan-return array<string, T>
   *   Maps labels to values.
   */
  public function getOptions(): array {
    return $this->options;
  }

  /**
   * @param array<string, T> $options
   *   Maps labels to values.
   *
   * @return $this
   */
  public function setOptions(array $options): self {
    $this->options = $options;

    return $this;
  }

}
