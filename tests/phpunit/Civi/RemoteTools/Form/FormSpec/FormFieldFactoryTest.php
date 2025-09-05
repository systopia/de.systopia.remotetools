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
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\RemoteTools\Form\FormSpec\FormFieldFactory
 */
final class FormFieldFactoryTest extends TestCase {

  private FormFieldFactory $factory;

  private SettingsBag&MockObject $settingsMock;

  protected function setUp(): void {
    parent::setUp();
    $this->settingsMock = $this->createMock(SettingsBag::class);
    $this->factory = new FormFieldFactory($this->settingsMock);
  }

  public function testCreateFormFieldBoolean(): void {
    $field = [
      'name' => 'test',
      'data_type' => 'Boolean',
      'input_type' => 'CheckBox',
    ];

    static::assertEquals(new CheckboxField('test', 'test'), $this->factory->createFormField($field, []));
  }

  public function testCreateFormFieldDate(): void {
    $field = [
      'name' => 'test',
      'data_type' => 'Date',
      'input_type' => 'Date',
    ];

    static::assertEquals(new DateField('test', 'test'), $this->factory->createFormField($field, []));
  }

  public function testCreateFormFieldFloat(): void {
    $field = [
      'name' => 'test',
      'data_type' => 'Float',
      'input_type' => 'Number',
    ];

    static::assertEquals(new FloatField('test', 'test'), $this->factory->createFormField($field, []));
  }

  public function testCreateFormFieldInteger(): void {
    $field = [
      'name' => 'test',
      'data_type' => 'Integer',
      'input_type' => 'Number',
    ];

    static::assertEquals(new IntegerField('test', 'test'), $this->factory->createFormField($field, []));
  }

  public function testCreateFormFieldMoney(): void {
    $field = [
      'name' => 'test',
      'data_type' => 'Money',
      'input_type' => 'Text',
    ];

    static::assertEquals(
      new MoneyField('test', 'test in EUR', 'EUR'),
      $this->factory->createFormField($field, ['currency' => 'EUR'])
    );
  }

  public function testCreateFormFieldString(): void {
    $field = [
      'name' => 'test',
      'data_type' => 'String',
      'input_type' => 'Text',
    ];

    static::assertEquals(
      (new TextField('test', 'test'))->setMaxLength(255),
      $this->factory->createFormField($field, [])
    );

    $field['input_attrs'] = ['maxlength' => 23];

    static::assertEquals(
      (new TextField('test', 'test'))->setMaxLength(23),
      $this->factory->createFormField($field, [])
    );
  }

  public function testCreateFormFieldText(): void {
    $field = [
      'name' => 'test',
      'data_type' => 'Text',
      'input_type' => 'TextArea',
    ];

    static::assertEquals(
      (new MultilineTextField('test', 'test'))->setMaxLength(10000),
      $this->factory->createFormField($field, [])
    );

    $field['input_attrs'] = ['maxlength' => 123];

    static::assertEquals(
      (new MultilineTextField('test', 'test'))->setMaxLength(123),
      $this->factory->createFormField($field, [])
    );
  }

  public function testCreateFormFieldTimestamp(): void {
    $field = [
      'name' => 'test',
      'data_type' => 'Timestamp',
      'input_type' => 'Date',
    ];

    static::assertEquals(new DateTimeField('test', 'test'), $this->factory->createFormField($field, []));
  }

  public function testCreateFormFieldEmail(): void {
    $field = [
      'name' => 'test',
      'data_type' => 'String',
      'input_type' => 'Email',
    ];

    static::assertEquals(new EmailField('test', 'test'), $this->factory->createFormField($field, []));
  }

  public function testCreateFormFieldEntityRef(): void {
    $field = [
      'name' => 'test',
      'data_type' => 'Integer',
      'input_type' => 'EntityRef',
      'fk_entity' => 'Contact',
      'fk_column' => 'id',
    ];

    // There's no input field for EntityRef, yet.
    static::assertEquals(new IntegerField('test', 'test'), $this->factory->createFormField($field, []));
  }

  public function testCreateFormFieldHidden(): void {
    $field = [
      'name' => 'test',
      'data_type' => 'Integer',
      'input_type' => 'Hidden',
    ];

    static::assertEquals(
      (new IntegerField('test', 'test'))->setHidden(TRUE),
      $this->factory->createFormField($field, [])
    );
  }

  public function testCreateFormFieldFile(): void {
    $field = [
      'name' => 'test',
      'data_type' => 'Integer',
      'input_type' => 'File',
      'fk_entity' => 'File',
    ];

    static::assertEquals(new FileField('test', 'test'), $this->factory->createFormField($field, []));
  }

  public function testCreateFormFieldRichTextEditor(): void {
    $field = [
      'name' => 'test',
      'data_type' => 'Text',
      'input_type' => 'RichTextEditor',
    ];

    static::assertEquals(new HtmlField('test', 'test'), $this->factory->createFormField($field, []));
  }

  public function testCreateFormFieldCheckBox(): void {
    $field = [
      'name' => 'test',
      'data_type' => 'String',
      'input_type' => 'CheckBox',
      'options' => ['foo' => 'Foo', 'bar' => 'Bar'],
      'serialize' => 1,
    ];

    static::assertEquals(
      new CheckboxesField('test', 'test', ['foo' => 'Foo', 'bar' => 'Bar']),
      $this->factory->createFormField($field, [])
    );

    $field['options'] = [
      ['id' => 'foo', 'label' => 'Foo'],
      ['id' => 'bar', 'label' => 'Bar'],
    ];

    static::assertEquals(
      new CheckboxesField('test', 'test', ['foo' => 'Foo', 'bar' => 'Bar']),
      $this->factory->createFormField($field, [])
    );
  }

  public function testCreateFormFieldRadio(): void {
    $field = [
      'name' => 'test',
      'data_type' => 'String',
      'input_type' => 'Radio',
      'options' => ['foo' => 'Foo', 'bar' => 'Bar'],
    ];

    static::assertEquals(
      new RadiosField('test', 'test', ['foo' => 'Foo', 'bar' => 'Bar']),
      $this->factory->createFormField($field, [])
    );

    $field['options'] = [
      ['id' => 'foo', 'label' => 'Foo'],
      ['id' => 'bar', 'label' => 'Bar'],
    ];

    static::assertEquals(
      new RadiosField('test', 'test', ['foo' => 'Foo', 'bar' => 'Bar']),
      $this->factory->createFormField($field, [])
    );
  }

  public function testCreateFormFieldSelect(): void {
    $field = [
      'name' => 'test',
      'data_type' => 'String',
      'input_type' => 'Select',
      'options' => ['foo' => 'Foo', 'bar' => 'Bar'],
    ];

    static::assertEquals(
      new SelectField('test', 'test', ['foo' => 'Foo', 'bar' => 'Bar']),
      $this->factory->createFormField($field, [])
    );

    $field['options'] = [
      ['id' => 'foo', 'label' => 'Foo'],
      ['id' => 'bar', 'label' => 'Bar'],
    ];

    static::assertEquals(
      new SelectField('test', 'test', ['foo' => 'Foo', 'bar' => 'Bar']),
      $this->factory->createFormField($field, [])
    );
  }

  public function testCreateFormFieldUrl(): void {
    $field = [
      'name' => 'test',
      'data_type' => 'String',
      'input_type' => 'Url',
    ];

    static::assertEquals(new UrlField('test', 'test'), $this->factory->createFormField($field, []));
  }

  public function testCreateFormFieldIntegerDefaultValue(): void {
    $field = [
      'name' => 'test',
      'data_type' => 'Integer',
      'input_type' => 'Number',
      'default_value' => 123,
    ];

    static::assertEquals(
      (new IntegerField('test', 'test'))->setDefaultValue(123),
      $this->factory->createFormField($field, [])
    );
  }

  public function testCreateFormFieldFloatReadonly(): void {
    $field = [
      'name' => 'test',
      'data_type' => 'Float',
      'input_type' => 'Number',
      'readonly' => TRUE,
    ];

    static::assertEquals(
      (new FloatField('test', 'test'))->setReadOnly(TRUE),
      $this->factory->createFormField($field, [])
    );
  }

  public function testCreateFormFieldEmailRequired(): void {
    $field = [
      'name' => 'test',
      'data_type' => 'String',
      'input_type' => 'Email',
      'required' => TRUE,
    ];

    static::assertEquals(
      (new EmailField('test', 'test'))->setRequired(TRUE),
      $this->factory->createFormField($field, [])
    );
  }

  public function testCreateFormFieldUrlExistingValue(): void {
    $field = [
      'name' => 'test',
      'data_type' => 'String',
      'input_type' => 'Url',
    ];

    static::assertEquals(
      (new UrlField('test', 'test'))->setDefaultValue('https://example.org/'),
      $this->factory->createFormField($field, ['test' => 'https://example.org/'])
    );
  }

  public function testCreateFormFieldSerialized(): void {
    $field = [
      'name' => 'test',
      'label' => 'Test',
      'description' => 'Description',
      'data_type' => 'Integer',
      'input_type' => 'Number',
      'serialize' => 1,
    ];

    static::assertEquals(
      (new FieldListField('test', 'Test', new IntegerField('test', 'Test')))
        ->setDescription('Description'),
      $this->factory->createFormField($field, [])
    );
  }

}
