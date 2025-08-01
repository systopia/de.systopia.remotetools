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

declare(strict_types = 1);

use CRM_Remotetools_ExtensionUtil as E;

/**
 * CiviRemote configuration page
 */
class CRM_Remotetools_Form_Settings extends CRM_Core_Form {

  public function buildQuickForm() {
    $this->setTitle(E::ts('CiviRemote Configuration'));

    // add form elements
    $this->add(
        'checkbox',
        'remotecontact_matching_enabled',
        E::ts('Remote Contact Matching Enabled')
    );
    $this->add(
        'checkbox',
        'remotecontact_matching_creates_contacts_enabled',
        E::ts('Remote Contact Matching Creates New Contacts')
    );
    $this->add(
        'select',
        'remotecontact_matching_profile',
        E::ts('Remote Contact Matching Profile'),
        CRM_Xcm_Configuration::getProfileList()
    );

    $this->addButtons(
        [
            [
              'type'      => 'submit',
              'name'      => E::ts('Save'),
              'isDefault' => TRUE,
            ],
        ]
    );

    // set defaults
    $this->setDefaults(
        [
          'remotecontact_matching_enabled' => Civi::settings()->get(
                'remotecontact_matching_enabled'
          ),
          'remotecontact_matching_creates_contacts_enabled' => Civi::settings()->get(
                'remotecontact_matching_creates_contacts_enabled'
          ),
          'remotecontact_matching_profile' => Civi::settings()->get(
                'remotecontact_matching_profile'
          ),
        ]
    );
    parent::buildQuickForm();
  }

  public function postProcess() {
    // store values
    $values = $this->exportValues();
    Civi::settings()->set('remotecontact_matching_enabled',
                          $values['remotecontact_matching_enabled'] ?? 0);
    Civi::settings()->set('remotecontact_matching_creates_contacts_enabled',
                          $values['remotecontact_matching_creates_contacts_enabled'] ?? 0);
    Civi::settings()->set('remotecontact_matching_profile',
                          $values['remotecontact_matching_profile'] ?? '');
    CRM_Core_Session::setStatus(E::ts('CiviRemote Configuration Updated'));
    parent::postProcess();
  }

}
