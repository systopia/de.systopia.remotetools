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

namespace Civi\RemoteTools\RequestContext;

interface RequestContextInterface {

  /**
   * @param mixed $default
   *
   * @return mixed
   */
  public function get(string $key, $default = NULL);

  /**
   * @param mixed $value
   */
  public function set(string $key, $value): void;

  /**
   * @return int
   *   The resolved contact ID for remote sessions, the logged-in user's contact
   *   ID, or 0 on CLI.
   *
   * @throws \Exception If remote session, but resolved contact ID not set.
   */
  public function getContactId(): int;

  /**
   * @return int
   *   Contact ID of the logged-in user's contact ID, or 0 on CLI.
   */
  public function getLoggedInContactId(): int;

  public function getRemoteContactId(): ?string;

  public function setRemoteContactId(?string $remoteContactId): void;

  public function getResolvedContactId(): ?int;

  /**
   * @param int|null $contactId
   */
  public function setResolvedContactId(?int $contactId): void;

  public function isRemote(): bool;

  public function setRemote(bool $remote): void;

}
