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

use Webmozart\Assert\Assert;

final class RequestContext implements RequestContextInterface {

  /**
   * @phpstan-var array<string, mixed>
   */
  private array $data = [];

  /**
   * @inheritDoc
   */
  public function get(string $key, $default = NULL) {
    return $this->data[$key] ?? $default;
  }

  /**
   * @inheritDoc
   */
  public function set(string $key, $value): void {
    $this->data[$key] = $value;
  }

  public function getContactId(): int {
    if ($this->isRemote()) {
      $contactId = $this->getResolvedContactId();
      Assert::integer($contactId, 'Resolved contact ID missing');

      return $contactId;
    }

    return $this->getLoggedInContactId();
  }

  /**
   * @inheritDoc
   */
  public function getLoggedInContactId(): int {
    return \CRM_Core_Session::getLoggedInContactID() ?? 0;
  }

  public function getRemoteContactId(): ?string {
    // @phpstan-ignore-next-line
    return $this->get('remoteContactId');
  }

  public function setRemoteContactId(?string $remoteContactId): void {
    $this->set('remoteContactId', $remoteContactId);
  }

  public function getResolvedContactId(): ?int {
    // @phpstan-ignore-next-line
    return $this->get('resolvedContactId');
  }

  public function setResolvedContactId(?int $contactId): void {
    $this->set('resolvedContactId', $contactId);
  }

  public function isRemote(): bool {
    // @phpstan-ignore-next-line
    return $this->get('isRemote', FALSE);
  }

  public function setRemote(bool $remote): void {
    $this->set('isRemote', $remote);
  }

}
