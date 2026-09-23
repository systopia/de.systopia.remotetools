<?php
use CRM_Remotetools_ExtensionUtil as E;

return [
  [
    'name' => 'OptionValue_remote_contact',
    'entity' => 'OptionValue',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'option_group_id.name' => 'contact_id_history_type',
        'label' => E::ts('Remote Contact'),
        'value' => 'remote_contact',
        'name' => 'remote_contact',
        'description' => E::ts('Used by the RemoteTools extension to map CiviCRM contacts to remote users.'),
        'is_reserved' => TRUE,
        'is_active' => TRUE,
      ],
      'match' => [
        'option_group_id',
        'name',
        'value',
      ],
    ],
  ],
];
