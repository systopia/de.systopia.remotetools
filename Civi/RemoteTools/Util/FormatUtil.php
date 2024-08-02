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

namespace Civi\RemoteTools\Util;

final class FormatUtil {

  /**
   * @param string|null $decimalSeparator
   *   If NULL the decimal separator depends on the default locale.
   */
  public static function toHumanReadableBytes(int $bytes, int $decimals = 2, ?string $decimalSeparator = NULL): string {
    static $units = ['B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB'];

    $exponent = 0 === $bytes ? 0 : floor(log($bytes, 1024));
    $number = round($bytes / pow(1024, $exponent), $decimals);

    $formatter = new \NumberFormatter(\Locale::getDefault(), \NumberFormatter::DECIMAL);
    if (NULL !== $decimalSeparator) {
      $formatter->setSymbol(\NumberFormatter::DECIMAL_SEPARATOR_SYMBOL, $decimalSeparator);
    }

    return $formatter->format($number) . ' ' . $units[$exponent];
  }

}
