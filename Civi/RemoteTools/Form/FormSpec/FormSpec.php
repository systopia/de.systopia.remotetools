<?php
declare(strict_types = 1);

namespace Civi\RemoteTools\Form\FormSpec;

final class FormSpec extends AbstractFormElementContainer {

  private ?DataTransformerInterface $dataTransformer = NULL;

  /**
   * @phpstan-var array<ValidatorInterface>
   */
  private array $validators = [];

  public function getDataTransformer(): DataTransformerInterface {
    return $this->dataTransformer ??= new IdentityDataTransformer();
  }

  public function setDataTransformer(DataTransformerInterface $dataTransformer): self {
    $this->dataTransformer = $dataTransformer;

    return $this;
  }

  public function appendValidator(ValidatorInterface $validator): self {
    $this->validators[] = $validator;

    return $this;
  }

  /**
   * @phpstan-return array<ValidatorInterface>
   */
  public function getValidators(): array {
    return $this->validators;
  }

  public function prependValidator(ValidatorInterface $validator): self {
    array_unshift($this->validators, $validator);

    return $this;
  }

}
