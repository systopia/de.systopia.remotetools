<?php
/*-------------------------------------------------------+
| SYSTOPIA Remote Tools                                  |
| Copyright (C) 2021 SYSTOPIA                            |
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

use Civi\RemoteContact\GetRemoteContactProfiles;
use Civi\Test\Api3TestTrait;
use Civi\Test\HeadlessInterface;
use Civi\Test\HookInterface;
use Civi\Test\TransactionalInterface;
use CRM_Remotetools_ExtensionUtil as E;

/**
 * This is the test base class with lots of utility functions
 *
 * @group headless
 */
abstract class CRM_Remotetools_RemoteContactTestBase extends CRM_Remotetools_TestBase implements
    HeadlessInterface,
    HookInterface,
    TransactionalInterface {
  use Api3TestTrait {
    callAPISuccess as protected traitCallAPISuccess;
  }

  /**
   * Run a RemoteContact.get query
   *
   * @param string $profile name
   *   the profile name (required)
   *
   * @param array $query
   *   the query parameters
   */
  public function remoteContactQuery(string $profile, array $query = []): array {
    $query['profile'] = $profile;
    $result = $this->traitCallAPISuccess('RemoteContact', 'get', $query);
    return $result['values'];
  }

  /**
   * @var array list of known profiles to be used with registerRemoteContactProfile */
  private static array $known_profiles = [];

  /**
   * Register the profiles provided by this module itself.
   */
  public static function registerKnownProfiles(GetRemoteContactProfiles $profiles): void {
    foreach (self::$known_profiles as $name => $profile) {
      if ($profiles->matchesName($name)) {
        $profiles->addInstance($profile);
      }
    }
  }

  /**
   * Will register a certain RemoteContact profile
   *
   * @param string $name
   *   name of the profile
   *
   * @param CRM_Remotetools_RemoteContactProfile $profile
   *   the profile instance
   */
  public function registerRemoteContactProfile(string $name, CRM_Remotetools_RemoteContactProfile $profile): void {
    // record profile
    self::$known_profiles[$name] = $profile;

    // make sure we're registered
    $dispatcher = new \Civi\RemoteToolsDispatcher();
    $dispatcher->addUniqueListener(
        'civi.remotecontact.getprofiles',
        ['CRM_Remotetools_RemoteContactTestBase', 'registerKnownProfiles']);
  }

  /*
   *************************************************
   ***                TEST PROFILES              ***
   *************************************************
   */

  protected const MULTI_VALUE_CUSTOM_PROFILE = 'testMultiValueCustomProfile';

  /**
   * Provide a test profile for multi-value custom fields
   */
  protected function registerMultiValueCustomProfile(): void {
    static $already_registered = FALSE;
    if ($already_registered) {
      return;
    }

    // add profile with custom fields
    $this->registerRemoteContactProfile(self::MULTI_VALUE_CUSTOM_PROFILE,
        new class() extends CRM_Remotetools_RemoteContactProfile {

          public function getProfileID() {
            return 'testMultiValueCustomProfile';
          }

          public function addFields($fields_collection) {
            $fields_collection->setFieldSpec(
                'id',
                [
                  'name' => 'id',
                  'type' => CRM_Utils_Type::T_INT,
                  'title' => 'Contact ID',
                  'localizable' => 0,
                  'api.filter' => 1,
                  'api.sort' => 1,
                  'is_core_field' => TRUE,
                ]
            );
            $fields_collection->setFieldSpec(
                'contact_multi_test1',
                [
                  'name' => 'contact_multi_test1',
                  'type' => CRM_Utils_Type::T_ENUM,
                  'title' => 'Type',
                  'options' => CRM_Remotetools_DataTools::getOptions('test_number_list'),
                  'localizable' => 0,
                  'serialize' => 1,
                  'api.filter' => 1,
                  'api.sort' => 1,
                  'is_core_field' => FALSE,
                ]
            );
            $fields_collection->setFieldSpec(
                'contact_multi_test2',
                [
                  'name' => 'contact_multi_test2',
                  'type' => CRM_Utils_Type::T_ENUM,
                  'title' => 'Type',
                  'options' => CRM_Remotetools_DataTools::getOptions('test_number_list'),
                  'localizable' => 0,
                  'serialize' => 1,
                  'api.filter' => 1,
                  'api.sort' => 1,
                  'is_core_field' => FALSE,
                ]
            );
          }

          public function getExternalToInternalFieldMapping(): array {
                $ex2int_mapping = [
                  'id'                  => 'id',
                  'contact_multi_test1' => 'contact_test1.contact_multi_test1',
                  'contact_multi_test2' => 'contact_test1.contact_multi_test2',
                ];
                // resolve custom fields
                $mapping = array_flip($ex2int_mapping);
                CRM_Remotetools_CustomData::resolveCustomFields($mapping);
                $ex2int_mapping = array_flip($mapping);
                return $ex2int_mapping;
          }

        }
    );
    $already_registered = TRUE;
  }

}
