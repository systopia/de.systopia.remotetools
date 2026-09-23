<?php
use CRM_Remotetools_ExtensionUtil as E;

return [
  [
    'name' => 'OptionValue_CRM_Xcm_Matcher_IdTrackerRemoteIdMatcher',
    'entity' => 'OptionValue',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'option_group_id.name' => 'xcm_matching_rules',
        'label' => E::ts('Identity Tracker: Remote Contact ID'),
        'value' => 'CRM_Xcm_Matcher_IdTrackerRemoteIdMatcher',
        'name' => 'CRM_Xcm_Matcher_IdTrackerRemoteIdMatcher',
        'weight' => 22,
        'is_reserved' => TRUE,
      ],
      'match' => [
        'option_group_id',
        'name',
        'value',
      ],
    ],
  ],
];
