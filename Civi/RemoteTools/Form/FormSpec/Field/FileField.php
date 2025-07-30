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

namespace Civi\RemoteTools\Form\FormSpec\Field;

use Civi\RemoteTools\Form\FormSpec\AbstractFormField;

/**
 * @extends AbstractFormField<int>
 *
 * @codeCoverageIgnore
 *
 * @api
 */
class FileField extends AbstractFormField {

  private ?string $filename = NULL;

  private ?int $maxFileSize = NULL;

  private ?string $url = NULL;

  public function getDataType(): string {
    // ID of File entity.
    return 'integer';
  }

  public function getFilename(): ?string {
    return $this->filename;
  }

  public function setFilename(?string $filename): self {
    $this->filename = $filename;

    return $this;
  }

  public function getInputType(): string {
    return 'file';
  }

  /**
   * @returns int
   *   The maximum file size in bytes. Might only be approximately if files are
   *   transferred Base64 encoded.
   */
  public function getMaxFileSize(): ?int {
    return $this->maxFileSize;
  }

  /**
   * @param int|null $maxFileSize
   *   The maximum file size in bytes. Might only be approximately if files are
   *   transferred Base64 encoded.
   */
  public function setMaxFileSize(?int $maxFileSize): self {
    $this->maxFileSize = $maxFileSize;

    return $this;
  }

  public function getUrl(): ?string {
    return $this->url;
  }

  public function setUrl(?string $url): self {
    $this->url = $url;

    return $this;
  }

}
