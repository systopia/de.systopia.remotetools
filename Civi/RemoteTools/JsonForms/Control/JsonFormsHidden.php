<?php
/*
 * Copyright (C) 2022 SYSTOPIA GmbH
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

namespace Civi\RemoteTools\JsonForms\Control;

use Civi\RemoteTools\JsonForms\JsonFormsControl;

/**
 * Custom control that creates a hidden field.
 *
 * With the option 'internal' set to TRUE, the field should not be added to the
 * HTML code, but only be available in the data for validation and submission.
 * It might not be available for rules then.
 *
 * @codeCoverageIgnore
 */
class JsonFormsHidden extends JsonFormsControl {

  public function __construct(string $scope, ?array $options = NULL, array $keywords = []) {
    parent::__construct($scope, '', NULL, [
      'type' => 'hidden',
    ] + ($options ?? []), $keywords);
  }

}
