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

namespace Civi\RemoteTools\JsonForms\FormSpec\Factory\Control;

use Civi\RemoteTools\Form\FormSpec\AbstractFormInput;
use Civi\RemoteTools\JsonForms\FormSpec\ElementUiSchemaFactoryInterface;
use Civi\RemoteTools\JsonForms\JsonFormsControl;
use Civi\RemoteTools\JsonForms\JsonFormsElement;

final class RadiosControlFactory extends AbstractControlFactory {

  public function createInputSchema(
    AbstractFormInput $input,
    ElementUiSchemaFactoryInterface $factory
  ): JsonFormsElement {
    return new JsonFormsControl(
      $this->getScope($input), $input->getLabel(), $input->getDescription(), ['format' => 'radio']
    );
  }

  protected function supportsInput(AbstractFormInput $input): bool {
    return 'field' === $input->getType() && 'radios' === $input->getInputType();
  }

}
