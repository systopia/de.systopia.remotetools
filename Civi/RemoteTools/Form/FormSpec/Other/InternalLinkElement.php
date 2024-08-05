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

namespace Civi\RemoteTools\Form\FormSpec\Other;

use Civi\RemoteTools\Form\FormSpec\FormElementInterface;

/**
 * Displays a link to a CiviCRM resource. The URL is not exposed to users, but
 * the remote system acts as proxy.
 */
final class InternalLinkElement implements FormElementInterface {

  private string $url;

  private string $label;

  private ?string $description;

  private ?string $filename;

  /**
   * @param string|null $filename
   *   If given, this filename will be appended to the URL to give the users a
   *   more convenient URL.
   */
  public function __construct(string $url, string $label, ?string $description = NULL, ?string $filename = NULL) {
    $this->url = $url;
    $this->label = $label;
    $this->description = $description;
    $this->filename = $filename;
  }

  public function getType(): string {
    return 'internalLink';
  }

  public function getUrl(): string {
    return $this->url;
  }

  public function setUrl(string $url): self {
    $this->url = $url;

    return $this;
  }

  public function getLabel(): string {
    return $this->label;
  }

  public function setLabel(string $label): self {
    $this->label = $label;

    return $this;
  }

  public function getDescription(): ?string {
    return $this->description;
  }

  public function setDescription(?string $description): self {
    $this->description = $description;

    return $this;
  }

  /**
   * @return string|null
   *   If set, this filename will be appended to the URL to give the users a
   *   more convenient URL.
   */
  public function getFilename(): ?string {
    return $this->filename;
  }

  /**
   * @param string|null $filename
   *   If given, this filename will be appended to the URL to give the users a
   *   more convenient URL.
   */
  public function setFilename(?string $filename): self {
    $this->filename = $filename;

    return $this;
  }

}
