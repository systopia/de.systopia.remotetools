<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
 *
 *  This program is free software: you can redistribute it and/or modify it under
 *  the terms of the GNU Affero General Public License as published by the Free
 *  Software Foundation, either version 3 of the License, or (at your option) any
 *  later version.
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

namespace Civi\RemoteTools\Form\FormSpec;

use Civi\Core\SettingsBag;
use Civi\RemoteTools\Form\FormSpec\Field\CheckboxesField;
use Civi\RemoteTools\Form\FormSpec\Field\CheckboxField;
use Civi\RemoteTools\Form\FormSpec\Field\DateField;
use Civi\RemoteTools\Form\FormSpec\Field\DateTimeField;
use Civi\RemoteTools\Form\FormSpec\Field\EmailField;
use Civi\RemoteTools\Form\FormSpec\Field\FieldListField;
use Civi\RemoteTools\Form\FormSpec\Field\FileField;
use Civi\RemoteTools\Form\FormSpec\Field\FloatField;
use Civi\RemoteTools\Form\FormSpec\Field\HtmlField;
use Civi\RemoteTools\Form\FormSpec\Field\IntegerField;
use Civi\RemoteTools\Form\FormSpec\Field\MoneyField;
use Civi\RemoteTools\Form\FormSpec\Field\MultilineTextField;
use Civi\RemoteTools\Form\FormSpec\Field\RadiosField;
use Civi\RemoteTools\Form\FormSpec\Field\SelectField;
use Civi\RemoteTools\Form\FormSpec\Field\TextField;
use Civi\RemoteTools\Form\FormSpec\Field\UrlField;
use CRM_Remotetools_ExtensionUtil as E;

/**
 * @phpstan-import-type optionsT from FormFieldFactoryInterface
 * @phpstan-import-type fieldT from FormFieldFactoryInterface
 */
final class FormFieldFactory implements FormFieldFactoryInterface {

  private SettingsBag $settings;

  public function __construct(?SettingsBag $settings = NULL) {
    $this->settings = $settings ?? \Civi::settings();
  }

  public function createFormField(
    array $field,
    ?array $entityValues,
    string $formFieldNamePrefix = ''
  ): AbstractFormField {
    $formField = $this->doCreateFormField($field, $entityValues, $formFieldNamePrefix);
    $formField->setDescription($field['help_pre'] ?? '');
    $formField->setHidden('Hidden' === $field['input_type']);

    $formField->setRequired(TRUE === ($field['required'] ?? NULL));

    $this->setDefaultValue($formField, $field, $entityValues);

    $formField->setReadOnly(TRUE === ($field['readonly'] ?? NULL));

    return $formField;
  }

  /**
   * @phpstan-param fieldT $field
   * @phpstan-param array<string, mixed>|null $entityValues
   *
   * @phpstan-ignore-next-line Generic template not specified.
   * phpcs:disable Generic.Metrics.CyclomaticComplexity.MaxExceeded
   */
  private function doCreateFormField(
    array $field,
    ?array $entityValues,
    string $formFieldNamePrefix
  ): AbstractFormField {
  // phpcs:enable
    $name = $formFieldNamePrefix . $field['name'];
    $label = $field['title'] ?? $field['label'] ?? $name;

    if ('CheckBox' === $field['input_type'] && is_array($field['options'] ?? NULL)) {
      return new CheckboxesField($name, $label, $this->toFormFieldOptions($field['options']));
    }

    if (isset($field['serialize']) && 0 !== $field['serialize']) {
      if (is_array($field['options'] ?? NULL)) {
        // input_type "CheckBox" and "Select" are currently treated equally.
        // A multi select field might be considered in the future.
        return new CheckboxesField($name, $label, $this->toFormFieldOptions($field['options']));
      }

      return new FieldListField($name, $label, $this->createFormField(
        ['serialize' => 0] + $field,
        ['currency' => $entityValues['currency'] ?? NULL]
      )->setDescription(''));
    }

    if ('Select' === $field['input_type'] && is_array($field['options'] ?? NULL)) {
      return new SelectField($name, $label, $this->toFormFieldOptions($field['options']));
    }

    if ('Radio' === $field['input_type'] && is_array($field['options'] ?? NULL)) {
      return new RadiosField($name, $label, $this->toFormFieldOptions($field['options']));
    }

    switch ($field['data_type']) {
      case 'Array':
        throw new \InvalidArgumentException('Data type "Array" is not supported');

      case 'Boolean':
        return new CheckboxField($name, $label);

      case 'Date':
        return new DateField($name, $label);

      case 'Float':
        return new FloatField($name, $label);

      case 'Integer':
        // @todo Handle input_type "EntityRef".
        switch ($field['input_type']) {
          case 'File':
            if ('File' === ($field['fk_entity'] ?? NULL)) {
              return new FileField($name, $label);
            }
            // fall through
          default:
            return new IntegerField($name, $label);
        }

      case 'Money':
        $currency = is_string($entityValues['currency'] ?? NULL)
          ? $entityValues['currency'] : $this->settings->get('defaultCurrency');
        assert(is_string($currency));

        return new MoneyField($name, E::ts('%1 in %2', [1 => $label, 2 => $currency]), $currency);

      case 'String':
        return match ($field['input_type']) {
        'Email' => new EmailField($name, $label),
        'Url' => new UrlField($name, $label),
        default => (new TextField($name, $label))->setMaxLength($field['input_attrs']['maxlength'] ?? 255),
        };

        case 'Text':
          if ('RichTextEditor' === $field['input_type']) {
            return (new HtmlField($name, $label))
              ->setMaxLength($field['input_attrs']['maxlength'] ?? 20000);
          }

          return (new MultilineTextField($name, $label))
            ->setMaxLength($field['input_attrs']['maxlength'] ?? 10000);

        case 'Timestamp':
          return new DateTimeField($name, $label);

        default:
          // Fallback
          return (new TextField($name, $label))->setMaxLength($field['input_attrs']['maxlength'] ?? 255);
    }
  }

  /**
   * @phpstan-param fieldT $field
   * @phpstan-param array<string, mixed>|null $entityValues
   *
   * @phpstan-ignore-next-line Generic template not specified.
   */
  private function setDefaultValue(AbstractFormField $formField, array $field, ?array $entityValues): void {
    if (NULL !== $entityValues && array_key_exists($field['name'], $entityValues)) {
      // @phpstan-ignore-next-line
      $formField->setDefaultValue($entityValues[$field['name']]);
    }
    elseif (isset($field['default_value'])) {
      $formField->setDefaultValue($field['default_value']);
    }
  }

  /**
   * @phpstan-param optionsT $options
   *
   * @phpstan-return array<int|string, string>
   */
  private function toFormFieldOptions(array $options): array {
    $value = reset($options);
    if (FALSE === $value) {
      return [];
    }

    if (!is_array($value)) {
      /** @var array<int|string, string> $options */
      return $options;
    }

    /** @var list<array{id: int|string, label: string}> $options */
    $formFieldOptions = [];
    foreach ($options as $option) {
      $formFieldOptions[$option['id']] = $option['label'];
    }

    return $formFieldOptions;
  }

}
