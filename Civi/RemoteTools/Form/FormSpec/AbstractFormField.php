<?php
declare(strict_types = 1);

namespace Civi\RemoteTools\Form\FormSpec;

/**
 * @template T of scalar
 *
 * @codeCoverageIgnore
 */
abstract class AbstractFormField implements FormElementInterface {

  private string $name;

  private string $label;

  private string $description = '';

  private bool $required = FALSE;

  private ?bool $nullable = NULL;

  private bool $hasDefaultValue = FALSE;

  /**
   * @var mixed
   * @phpstan-var T|null
   */
  private $defaultValue = NULL;

  public function __construct(string $name, string $label) {
    $this->name = $name;
    $this->label = $label;
  }

  public function getType(): string {
    return 'field';
  }

  public function getName(): string {
    return $this->name;
  }

  /**
   * @return $this
   */
  public function setName(string $name): self {
    $this->name = $name;

    return $this;
  }

  public function getLabel(): string {
    return $this->label;
  }

  /**
   * @return $this
   */
  public function setLabel(string $label): self {
    $this->label = $label;

    return $this;
  }

  public function getDescription(): string {
    return $this->description;
  }

  /**
   * @return $this
   */
  public function setDescription(string $description): self {
    $this->description = $description;

    return $this;
  }

  public function isRequired(): bool {
    return $this->required;
  }

  /**
   * @return $this
   */
  public function setRequired(bool $required): self {
    $this->required = $required;

    return $this;
  }

  public function isNullable(): bool {
    return NULL === $this->nullable ? !$this->isRequired() : $this->nullable;
  }

  /**
   * @param bool|null $nullable
   *   If NULL, isNullable() will return TRUE, if the field is not required.
   *
   * @return $this
   *
   * @see isRequired()
   */
  public function setNullable(?bool $nullable): self {
    $this->nullable = $nullable;

    return $this;
  }

  /**
   * @return bool
   *   TRUE if a default value is set which might be NULL.
   */
  public function hasDefaultValue(): bool {
    return $this->hasDefaultValue;
  }

  /**
   * @return $this
   */
  public function setHasDefaultValue(bool $hasDefaultValue): self {
    $this->hasDefaultValue = $hasDefaultValue;

    return $this;
  }

  /**
   * @return mixed
   * @phpstan-return T|null
   */
  public function getDefaultValue() {
    return $this->defaultValue;
  }

  /**
   * Additionally sets "has default value" to TRUE.
   *
   * @param mixed $defaultValue
   * @phpstan-param T|null $defaultValue
   *
   * @return $this
   *
   * @see hasDefaultValue()
   */
  public function setDefaultValue($defaultValue): self {
    $this->hasDefaultValue = TRUE;
    $this->defaultValue = $defaultValue;

    return $this;
  }

  abstract public function getDataType(): string;

  abstract public function getFieldType(): string;

}
