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

namespace Civi\RemoteTools\EntityProfile;

use Civi\RemoteTools\Api4\Query\ConditionInterface;
use Civi\RemoteTools\EntityProfile\Traits\ConstProfileMetadataTrait;
use Civi\RemoteTools\Form\FormSpec\Field\TextField;
use Civi\RemoteTools\Form\FormSpec\FormSpec;

final class TestRemoteGroupReadWriteEntityProfile extends AbstractRemoteEntityProfile {

  use ConstProfileMetadataTrait;

  public const NAME = 'readWriteTest';

  public const ENTITY_NAME = 'Group';

  public const REMOTE_ENTITY_NAME = 'TestRemoteGroup';

  public function getFilter(string $actionName, ?int $contactId): ?ConditionInterface {
    return NULL;
  }

  public function getCreateFormSpec(array $arguments, array $entityFields, ?int $contactId): FormSpec {
    return (new FormSpec('Create Form Title'))
      ->addElement(
        (new TextField('name', 'Enter Name'))->setRequired(TRUE)
      )
      ->addElement(
        (new TextField('title', 'Enter Name'))->setRequired(TRUE)
      );
  }

  public function getUpdateFormSpec(array $entityValues, array $entityFields, ?int $contactId): FormSpec {
    return (new FormSpec('Update Form Title'))
      ->addElement(
        (new TextField('name', 'Enter Name'))->setMinLength(2)
      );
  }

}
