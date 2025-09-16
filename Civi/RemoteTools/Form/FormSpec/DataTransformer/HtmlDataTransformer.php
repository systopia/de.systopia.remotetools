<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
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

namespace Civi\RemoteTools\Form\FormSpec\DataTransformer;

use Civi\RemoteTools\Form\FormSpec\AbstractFormField;
use Civi\RemoteTools\Form\FormSpec\FieldDataTransformerInterface;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

/**
 * @implements FieldDataTransformerInterface<\Civi\RemoteTools\Form\FormSpec\Field\HtmlField>
 */
final class HtmlDataTransformer implements FieldDataTransformerInterface {

  /**
   * @inheritDoc
   */
  public function toEntityValue(mixed $data, AbstractFormField $field, ?array $defaultValuesInList = NULL): mixed {
    if (!is_string($data)) {
      return NULL;
    }

    $sanitizerConfig = (new HtmlSanitizerConfig())
      ->allowSafeElements()
      ->withMaxInputLength($field->getMaxLength() ?? -1);

    foreach ($field->getAllowedElements() as $element => $allowedAttributes) {
      $sanitizerConfig = $sanitizerConfig->allowElement($element, $allowedAttributes ?? '*');
    }

    foreach ($field->getBlockedElements() as $element) {
      $sanitizerConfig = $sanitizerConfig->blockElement($element);
    }

    foreach ($field->getDroppedElements() as $element) {
      $sanitizerConfig = $sanitizerConfig->dropElement($element);
    }

    $sanitizer = new HtmlSanitizer($sanitizerConfig);

    return $sanitizer->sanitize($data);
  }

}
