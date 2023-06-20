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

namespace Civi\RemoteTools\Api4\Action;

use Civi\Api4\Generic\Result;
use Civi\RemoteTools\Api4\Action\Traits\ProfileParameterOptionalTrait;
use Civi\RemoteTools\Exception\ActionHandlerNotFoundException;

/**
 * If no profile is set and no action handler is found an "id" field is added by
 * default.
 *
 * Note: SearchKit administration fails to load if no field is returned for
 * CiviCRM <5.62.
 *
 * @see https://github.com/civicrm/civicrm-core/pull/26045
 */
class RemoteGetFieldsAction extends AbstractRemoteGetFieldsAction implements ProfileAwareRemoteActionInterface {

  // Called by API explorer and SearchKit, so parameters need to be optional.
  use ProfileParameterOptionalTrait;

  public function _run(Result $result): void {
    try {
      $this->doRun($result);
    }
    // @phpstan-ignore-next-line
    catch (ActionHandlerNotFoundException $e) {
      if (NULL !== $this->profile) {
        throw $e;
      }

      if ($this->select !== ['row_count']) {
        $result[] = [
          'default_value' => NULL,
          'type' => 'Field',
          'entity' => $this->getEntityName(),
          'required' => TRUE,
          'nullable' => FALSE,
          'readonly' => TRUE,
          'name' => 'id',
          'title' => 'ID',
          'data_type' => 'Integer',
          'options' => FALSE,
          'label' => 'ID',
        ];
      }

      if (in_array('row_count', $this->select, TRUE)) {
        $result->setCountMatched(1);
      }
    }
  }

}
