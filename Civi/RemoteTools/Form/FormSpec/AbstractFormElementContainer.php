<?php
declare(strict_types = 1);

namespace Civi\RemoteTools\Form\FormSpec;

abstract class AbstractFormElementContainer {

  private string $title;

  /**
   * @phpstan-var array<FormElementInterface>
   */
  private array $elements = [];

  /**
   * @phpstan-param array<FormElementInterface> $elements
   */
  public function __construct(string $title, array $elements = []) {
    $this->title = $title;
    $this->elements = $elements;
  }

  public function getTitle(): string {
    return $this->title;
  }

  /**
   * @return $this
   */
  public function setTitle(string $title): self {
    $this->title = $title;

    return $this;
  }

  /**
   * @return $this
   */
  public function addElement(FormElementInterface $element): self {
    $this->elements[] = $element;

    return $this;
  }

  /**
   * @phpstan-return array<FormElementInterface>
   */
  public function getElements(): array {
    return $this->elements;
  }

  /**
   * @return $this
   */
  public function insertElement(FormElementInterface $element, int $index): self {
    array_splice($this->elements, $index, 0, [$element]);

    return $this;
  }

  /**
   * @phpstan-param array<FormElementInterface> $elements
   */
  public function setElements(array $elements): self {
    $this->elements = $elements;

    return $this;
  }

  /**
   * @phpstan-return array<string, AbstractFormField>
   *   Field names mapped to fields.
   */
  public function getFields(): array {
    $fields = [];
    foreach ($this->elements as $element) {
      if ($element instanceof AbstractFormField) {
        $fields[$element->getName()] = $element;
      }
      elseif ($element instanceof FormElementContainer) {
        $containerFields = $element->getFields();
        $nonUniqueFields = array_keys(array_intersect_key($fields, $containerFields));
        if ([] !== $nonUniqueFields) {
          throw new \RuntimeException(sprintf(
            'Form spec contains fields more than once: %s',
            implode(', ', $nonUniqueFields),
          ));
        }

        $fields = array_merge($fields, $containerFields);
      }
    }

    return $fields;
  }

}
