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

namespace Civi\RemoteTools\JsonSchema\FormSpec\Factory;

use Civi\RemoteTools\Form\FormSpec\AbstractFormField;
use Civi\RemoteTools\Form\FormSpec\Field\FileField;
use Civi\RemoteTools\JsonSchema\JsonSchema;
use Civi\RemoteTools\JsonSchema\JsonSchemaFile;
use Webmozart\Assert\Assert;

final class FileFieldFactory extends AbstractFieldJsonSchemaFactory {

  public static function getPriority(): int {
    return IntegerFieldFactory::getPriority() + 1;
  }

  protected function doCreateSchema(AbstractFormField $field): JsonSchema {
    Assert::isInstanceOf($field, FileField::class);
    /** @var \Civi\RemoteTools\Form\FormSpec\Field\FileField $field */

    $keywords = [];
    if ($field->isReadOnly()) {
      $keywords['readOnly'] = TRUE;
    }

    if ($field->hasDefaultValue() && NULL !== $field->getFilename() && NULL !== $field->getUrl()) {
      $keywords['default'] = JsonSchema::fromArray([
        'filename' => $field->getFilename(),
        'url' => $field->getUrl(),
      ]);
      // If the field is read only, we cannot use the const keyword, because
      // the URL might contain a hash that depends on the time. This is at least
      // true for the /civicrm/file path.
    }
    elseif ($field->isReadOnly()) {
      $keywords['const'] = NULL;
    }

    return new JsonSchemaFile($field->getMaxFileSize(), $keywords, $field->isNullable());
  }

  public function supportsField(AbstractFormField $field): bool {
    return $field instanceof FileField;
  }

}
