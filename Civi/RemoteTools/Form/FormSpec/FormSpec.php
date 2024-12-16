<?php
/*
 * Copyright (C) 2024 SYSTOPIA GmbH
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as published by
 *  the Free Software Foundation in version 3.
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

/**
 * @extends AbstractFormElementContainer<FormElementInterface>
 *
 * @api
 */
final class FormSpec extends AbstractFormElementContainer {

  private ?DataTransformerInterface $dataTransformer = NULL;

  /**
   * @phpstan-var array<ValidatorInterface>
   */
  private array $validators = [];

  public function getDataTransformer(): DataTransformerInterface {
    // @phpstan-ignore-next-line
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
