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

use CRM_Remotetools_ExtensionUtil as E;

/**
 * Adds fields for option suffixes, e.g. option_field:value
 */
final class EntityProfileOptionSuffixDecorator extends AbstractRemoteEntityProfileDecorator {

  /**
   * @inheritDoc
   */
  public function getRemoteFields(array $entityFields): array {
    $remoteFields = parent::getRemoteFields($entityFields);

    foreach ($remoteFields as $fieldName => $field) {
      if (($field['options'] ?? FALSE) !== FALSE && is_array($field['suffixes'] ?? NULL)) {
        foreach ($field['suffixes'] as $suffix) {
          $remoteFields[$fieldName . ':' . $suffix] ??= $this->createField($field, $fieldName, $suffix);
        }
      }
    }

    return $remoteFields;
  }

  /**
   * @phpstan-param array<string, mixed> $field
   *
   * @phpstan-return array<string, mixed>
   *
   * phpcs:disable Generic.Metrics.CyclomaticComplexity.TooHigh
   */
  private function createField(array $field, string $fieldName, string $suffix): array {
    // phpcs:enable
    /** @var string $title */
    $title = $field['title'] ?? $fieldName;
    /** @var string $label */
    $label = $field['label'] ?? $title;
    /** @var string $description */
    $description = $field['description'] ?? $label;

    return [
      'name' => $fieldName . ':' . $suffix,
      'type' => 'Option',
      'entity' => $field['entity'] ?? NULL,
      'data_type' => 'String',
      'nullable' => $field['nullable'] ?? TRUE,
      'readonly' => $field['readonly'] ?? TRUE,
      'permission' => $field['permission'] ?? NULL,
      'fk_entity' => $field['fk_entity'] ?? NULL,
      // @phpstan-ignore-next-line
      'options' => $this->getOptions($field['options'] ?? TRUE, $suffix),
      'title' => sprintf('%s [%s]', $title, $suffix),
      'label' => sprintf('%s [%s]', $label, $suffix),
      'description' => sprintf('%s [%s:%s]', $description, $fieldName, $suffix),
      'help_pre' => $field['help_pre'] ?? NULL,
      'help_post' => $field['help_post'] ?? NULL,
      'custom_field_id' => $field['custom_field_id'] ?? NULL,
      'custom_group_id' => $field['custom_group_id'] ?? NULL,
    ];
  }

  /**
   * @param bool|array $options
   * @phpstan-param true|array<string, scalar|null>|array<int, array<string, scalar|null>> $options
   *   The actual value depends on the value of the "loadOptions" action
   *   parameter that was used.
   *
   * @return bool|array
   * @phpstan-return true|array<string, scalar|null>
   */
  private function getOptions($options, string $suffix) {
    if (!is_array($options)) {
      return TRUE;
    }

    if (is_array($options[0] ?? NULL)) {
      $result = [];
      foreach ($options as $option) {
        // Should always be true.
        if (isset($option['id'])) {
          $result[(string) $option['id']] = $option[$suffix] ?? NULL;
        }
      }

      return $result;
    }

    if ('name' === $suffix) {
      /** @phpstan-var array<string, scalar|null> */
      return $options;
    }

    return TRUE;
  }

}
