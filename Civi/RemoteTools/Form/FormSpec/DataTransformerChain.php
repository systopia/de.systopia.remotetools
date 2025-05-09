<?php
declare(strict_types = 1);

namespace Civi\RemoteTools\Form\FormSpec;

final class DataTransformerChain implements DataTransformerInterface {

  /**
   * @phpstan-var iterable<DataTransformerInterface>
   */
  private iterable $transformers;

  /**
   * @phpstan-param iterable<DataTransformerInterface> $transformers
   */
  public function __construct(iterable $transformers) {
    $this->transformers = $transformers;
  }

  public function appendTransformer(DataTransformerInterface $transformer): void {
    $this->transformers = [...$this->transformers, $transformer];
  }

  /**
   * @inheritDoc
   */
  public function toEntityValues(array $formData, ?array $currentEntityValues, ?int $contactId): array {
    foreach ($this->transformers as $transformer) {
      $formData = $transformer->toEntityValues($formData, $currentEntityValues, $contactId);
    }

    return $formData;
  }

}
