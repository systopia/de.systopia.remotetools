<?php
declare(strict_types = 1);

namespace Civi\RemoteTools\Form\FormSpec\Field;

use Civi\RemoteTools\Form\FormSpec\AbstractFormField;

/**
 * @template T of int|string
 *
 * @extends AbstractFormField<T>
 *
 * @codeCoverageIgnore
 */
abstract class AbstractOptionField extends AbstractFormField {

  /**
   * @phpstan-var array<T, string>
   *   Maps values to labels.
   */
  private array $options;

  /**
   * @param array<T, string> $options
   *   Maps values to labels.
   */
  public function __construct(string $name, string $label, array $options) {
    parent::__construct($name, $label);
    $this->options = $options;
  }

  /**
   * @param T $value
   */
  public function addOption($value, string $label): self {
    $this->options[$value] = $label;

    return $this;
  }

  /**
   * @phpstan-return array<T, string>
   *   Maps values to labels.
   */
  public function getOptions(): array {
    return $this->options;
  }

  /**
   * @param array<T, string> $options
   *   Maps values to labels.
   *
   * @return $this
   */
  public function setOptions(array $options): self {
    $this->options = $options;

    return $this;
  }

}
