<?php
declare(strict_types = 1);

namespace Civi\RemoteTools\Form\FormSpec\Field;

/**
 * @codeCoverageIgnore
 */
final class MoneyField extends FloatField {

  private string $currency;

  public function __construct(string $name, string $label, string $currency) {
    parent::__construct($name, $label);
    $this->setPrecision(2);
    $this->currency = $currency;
  }

  public function getFieldType(): string {
    return 'money';
  }

  public function getCurrency(): string {
    return $this->currency;
  }

  /**
   * @return $this
   */
  public function setCurrency(string $currency): self {
    $this->currency = $currency;

    return $this;
  }

}
