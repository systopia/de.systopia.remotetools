<?php
use CRM_Remotetools_ExtensionUtil as E;

return [
  [
    'name' => 'CustomGroup_remote_contact_data',
    'entity' => 'CustomGroup',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'remote_contact_data',
        'table_name' => 'civicrm_value_remote_contact_data',
        'title' => E::ts('RemoteContact Information'),
        'collapse_display' => TRUE,
        'is_active' => TRUE,
      ],
      'match' => [
        'name',
      ],
    ],
  ],
  [
    'name' => 'OptionGroup_remote_contact_roles',
    'entity' => 'OptionGroup',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'remote_contact_roles',
        'title' => E::ts('Remote Contact Roles'),
        'option_value_fields' => [
          'name',
          'label',
          'description',
        ],
        'is_active' => TRUE,
      ],
      'match' => [
        'name',
      ],
    ],
  ],
  [
    'name' => 'OptionGroup_remote_contact_roles_OptionValue_remote_user',
    'entity' => 'OptionValue',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'option_group_id.name' => 'remote_contact_roles',
        'label' => E::ts('CiviRemote User'),
        'value' => '1',
        'name' => 'remote-user',
        'description' => E::ts('Contact has a CiviRemote-ID, or has had them in the past'),
        'is_reserved' => TRUE,
      ],
      'match' => [
        'option_group_id',
        'name',
        'value',
      ],
    ],
  ],
  [
    'name' => 'CustomGroup_remote_contact_data_CustomField_remote_contact_roles',
    'entity' => 'CustomField',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'custom_group_id.name' => 'remote_contact_data',
        'name' => 'remote_contact_roles',
        'column_name' => 'remote_contact_roles',
        'label' => E::ts('Remote Roles'),
        'html_type' => 'Select',
        'is_searchable' => TRUE,
        'option_group_id.name' => 'remote_contact_roles',
        'serialize' => 1,
        'in_selector' => TRUE,
        'is_active' => TRUE,
      ],
      'match' => [
        'name',
        'custom_group_id',
      ],
    ],
  ],
];
