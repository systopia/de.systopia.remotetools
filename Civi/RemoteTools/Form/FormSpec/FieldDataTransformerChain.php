<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
 *
 *  This program is free software: you can redistribute it and/or modify it under
 *  the terms of the GNU Affero General Public License as published by the Free
 *  Software Foundation, either version 3 of the License, or (at your option) any
 *  later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types = 1);

namespace Civi\RemoteTools\Form\FormSpec;

final class FieldDataTransformerChain implements FieldDataTransformerInterface {

  /**
   * @var iterable<FieldDataTransformerInterface>
   */
  private iterable $transformers;

  /**
   * @param iterable<FieldDataTransformerInterface> $transformers
   */
  public function __construct(iterable $transformers) {
    $this->transformers = $transformers;
  }

  public function appendTransformer(FieldDataTransformerInterface $transformer): void {
    $this->transformers = [...$this->transformers, $transformer];
  }

  public function prependTransformer(FieldDataTransformerInterface $transformer): void {
    $this->transformers = [$transformer, ...$this->transformers];
  }

  public function toEntityValue(mixed $data, AbstractFormField $field, ?array $defaultValuesInList = NULL): mixed {
    foreach ($this->transformers as $transformer) {
      $data = $transformer->toEntityValue($data, $field, $defaultValuesInList);
    }

    return $data;
  }

}
