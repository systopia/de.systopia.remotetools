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

namespace Civi\RemoteTools\EntityProfile\Authorization;

/**
 * @codeCoverageIgnore
 *
 * @api
 */
final class GrantResult {

  public bool $granted;

  public bool $showForm;

  public ?string $message;

  private function __construct(bool $granted, ?string $message, ?bool $showForm = NULL) {
    $this->granted = $granted;
    $this->message = $message;
    $this->showForm = $showForm ?? $granted;
  }

  public static function newPermitted(): self {
    return new self(TRUE, NULL);
  }

  /**
   * @param string|null $message
   *   If no message is specified, a generic message might be used.
   */
  public static function newDenied(?string $message = NULL): self {
    return new self(FALSE, $message);
  }

  /**
   * Performing the actual action will be denied, but the form will still be
   * delivered. This can be used to provide a "form" with only custom markup
   * (no fields).
   *
   * @param string|null $message
   *   If no message is specified, a generic message might be used.
   */
  public static function newDeniedWithForm(?string $message = NULL): self {
    return new self(FALSE, $message, TRUE);
  }

}
