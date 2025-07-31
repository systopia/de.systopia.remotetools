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

/**
 * @codeCoverageIgnore
 *
 * @api
 */
final class MoneyField extends FloatField {

  private string $currency;

  public function __construct(string $name, string $label, string $currency) {
    parent::__construct($name, $label);
    $this->setPrecision(2);
    $this->currency = $currency;
  }

  public function getInputType(): string {
    return 'money';
  }

  public function getCurrency(): string {
    return $this->currency;
  }

  /**
   * @return $this
   */
  public function setCurrency(string $currency): static {
    $this->currency = $currency;

    return $this;
  }

}
