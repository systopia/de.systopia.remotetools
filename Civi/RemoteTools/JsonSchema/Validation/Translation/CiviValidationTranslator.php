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

namespace Civi\RemoteTools\JsonSchema\Validation\Translation;

use Opis\JsonSchema\Errors\ValidationError;
use Systopia\JsonSchema\Translation\TranslatorFactory;
use Systopia\JsonSchema\Translation\TranslatorInterface;

/**
 * @codeCoverageIgnore
 */
final class CiviValidationTranslator implements TranslatorInterface {

  private string $lastLocale = '';

  /**
   * @phpstan-ignore-next-line Not initialized in constructor.
   */
  private TranslatorInterface $translator;

  /**
   * @inheritDoc
   */
  public function trans(string $id, array $parameters, ValidationError $error): string {
    return $this->getTranslator()->trans($id, $parameters, $error);
  }

  private function getTranslator(): TranslatorInterface {
    if (\CRM_Core_I18n::getLocale() !== $this->lastLocale) {
      $this->lastLocale = \CRM_Core_I18n::getLocale();
      $this->translator = TranslatorFactory::createTranslator($this->lastLocale);
    }

    return $this->translator;
  }

}
