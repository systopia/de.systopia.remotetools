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

namespace Civi\RemoteTools\Form\FormSpec\Field;

use CRM_Remotetools_ExtensionUtil as E;

/**
 * Can be used for entities that allow attached files like Activity.
 *
 * This field is supposed to be used in combination with AttachmentsLoader and
 * AttachmentsPersister.
 *
 * @see \Civi\RemoteTools\Helper\AttachmentsLoaderInterface
 * @see \Civi\RemoteTools\Helper\AttachmentsPersisterInterface
 */
final class AttachmentsField extends FieldListField {

  private TextField $descriptionField;

  private FileField $fileField;

  public function __construct(string $name, string $label) {
    $this->descriptionField = new TextField('description', E::ts('Description'));
    $this->fileField = (new FileField('file', E::ts('File')))->setRequired(TRUE);

    parent::__construct($name, $label, new FieldCollectionField('', '', [
      $this->fileField,
      $this->descriptionField,
    ]));
  }

  /**
   * @phpstan-return non-negative-int
   *   If maxItems is not set the value of "max_attachments" in the CiviCRM
   *   settings is returned.
   */
  public function getMaxItems(): int {
    // @phpstan-ignore cast.int, return.type
    return parent::getMaxItems() ?? (int) \Civi::settings()->get('max_attachments');
  }

  public function setDescriptionFieldLabel(string $label): static {
    $this->descriptionField->setLabel($label);

    return $this;
  }

  public function setDescriptionFieldRequired(bool $required): static {
    $this->descriptionField->setRequired($required);

    return $this;
  }

  public function setFileFieldLabel(string $label): static {
    $this->fileField->setLabel($label);

    return $this;
  }

}
