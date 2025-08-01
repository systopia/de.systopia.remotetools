<?php
use CRM_Remotetools_ExtensionUtil as E;

return [
  [
    'name' => 'Navigation_civi_remote_settings',
    'entity' => 'Navigation',
    'cleanup' => 'always',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'label' => E::ts('CiviRemote'),
        'name' => 'civi_remote_settings',
        'url' => 'civicrm/admin/remotetools',
        'permission' => [
          'administer CiviCRM',
        ],
        'permission_operator' => 'AND',
        'parent_id.name' => 'System Settings',
      ],
      'match' => [
        'name',
        'domain_id',
      ],
    ],
  ],
];
