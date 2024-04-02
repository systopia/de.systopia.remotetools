<?php
/*-------------------------------------------------------+
| SYSTOPIA Remote Tools                                  |
| Copyright (C) 2020 SYSTOPIA                            |
| Author: B. Endres (endres@systopia.de)                 |
+--------------------------------------------------------+
| This program is released as free software under the    |
| Affero GPL license. You can redistribute it and/or     |
| modify it under the terms of this license which you    |
| can read by viewing the included agpl.txt or online    |
| at www.gnu.org/licenses/agpl.html. Removal of this     |
| copyright header is strictly prohibited without        |
| written permission from the original author(s).        |
+--------------------------------------------------------*/

use CRM_Remotetools_ExtensionUtil as E;

/**
 * Collection of upgrade steps.
 */
class CRM_Remotetools_Upgrader extends CRM_Extension_Upgrader_Base
{

    /**
     * Installation procedure
     */
    public function install()
    {
        // create custom data structures
        $customData = new CRM_Remotetools_CustomData(E::LONG_NAME);
        $customData->syncOptionGroup(E::path('resources/option_group_remote_contact_roles.json'));
        $customData->syncOptionGroup(E::path('resources/option_group_rules.json'));
        $customData->syncCustomGroup(E::path('resources/custom_group_remote_contact_data.json'));

        // also: add the 'Remote Contact' type to the identity tracker
        $exists_count = civicrm_api3(
            'OptionValue',
            'getcount',
            [
                'option_group_id' => 'contact_id_history_type',
                'value'           => 'remote_contact'
            ]
        );
        switch ($exists_count) {
            case 0:
                // not there -> create
                civicrm_api3(
                    'OptionValue',
                    'create',
                    [
                        'option_group_id' => 'contact_id_history_type',
                        'value'           => 'remote_contact',
                        'is_reserved'     => 1,
                        'description'     => E::ts(
                            "Used by the RemoteTools extension to map CiviCRM contacts to remote users oder contacts."
                        ),
                        'name'            => 'remote_contact',
                        'label'           => E::ts("Remote Contact")
                    ]
                );
                break;

            case 1:
                // does exist, nothing to do here
                break;

            default:
                // more than one exists: that's not good!
                CRM_Core_Session::setStatus(
                    E::ts(
                        "Multiple identity types 'remote_contact' contact exist in IdentityTracker's types! Please fix!"
                    ),
                    E::ts("Warning"),
                    'warn'
                );
                break;
        }
    }

    /**
     * Extension is being disabled
     */
    public function disable() {
        // Remove XCM matcher
        $matchers = ['CRM_Xcm_Matcher_IdTrackerRemoteIdMatcher'];
        foreach ($matchers as $matcher_name) {
            $entry = civicrm_api3('OptionValue', 'get', [
                'name'            => $matcher_name,
                'option_group_id' => 'xcm_matching_rules']);
            if (!empty($entry['id'])) {
                civicrm_api3('OptionValue', 'delete', ['id' => $entry['id']]);
            }
        }
    }

    /**
     * Adding roles
     *
     * @return TRUE on success
     * @throws Exception
     */
    public function upgrade_0001()
    {
        $this->ctx->log->info('Adding remote roles.');
        $customData = new CRM_Remotetools_CustomData(E::LONG_NAME);
        $customData->syncOptionGroup(E::path('resources/option_group_remote_contact_roles.json'));
        $customData->syncCustomGroup(E::path('resources/custom_group_remote_contact_data.json'));
        return true;
    }

    /**
     * Adding XCM RemoteContact ID matcher
     *
     * @return TRUE on success
     * @throws Exception
     */
    public function upgrade_0002()
    {
        $this->ctx->log->info('Adding XCM RemoteContact ID matcher.');
        $customData = new CRM_Remotetools_CustomData(E::LONG_NAME);
        $customData->syncOptionGroup(E::path('resources/option_group_rules.json'));
        return true;
    }
}
