<?php
/*
 * Copyright (C) 2023 SYSTOPIA GmbH
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

use Civi\RemoteTools\Form\Validation\ValidationResult;

interface ValidatorInterface {

  /**
   * @phpstan-param array<string, mixed> $formData JSON serializable.
   * @phpstan-param array<string, mixed>|null $currentEntityValues
   *   JSON serializable. Current entity values on update, NULL on create.
   */
  public function validate(array $formData, ?array $currentEntityValues, ?int $contactId): ValidationResult;

}