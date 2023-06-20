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

require_once 'remotetools.civix.php';

use CRM_Remotetools_ExtensionUtil as E;
use Civi\RemoteContact\RemoteContactGetRequest as RemoteContactGetRequest;
use Symfony\Bridge\ProxyManager\LazyProxy\Instantiator\RuntimeInstantiator;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Config\Resource\GlobResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function remotetools_civicrm_config(&$config)
{
    if (file_exists(__DIR__ . '/vendor/autoload.php')) {
      require_once __DIR__ . '/vendor/autoload.php';
    }
    _remotetools_civix_civicrm_config($config);

    // register events (with our own wrapper to avoid duplicate registrations)
    $dispatcher = new \Civi\RemoteToolsDispatcher();

    // EVENT REMOTECONTAT GETPROFILES
    $dispatcher->addUniqueListener(
        'civi.remotecontact.getprofiles',
        ['CRM_Remotetools_RemoteContactProfile', 'registerKnownProfiles']);

    // EVENT REMOTECONTACT GETFIELDS
    $dispatcher->addUniqueListener(
        'civi.remotecontact.getfields',
        ['Civi\RemoteContact\GetFieldsEvent', 'addProfileFields'], RemoteContactGetRequest::BEFORE_EXECUTE_REQUEST);

    // EVENT REMOTECONTACT GET
    $dispatcher->addUniqueListener(
        'civi.remotecontact.get',
        ['Civi\RemoteContact\RemoteContactGetRequest', 'initProfile'], RemoteContactGetRequest::INITIALISATION);
    $dispatcher->addUniqueListener(
        'civi.remotecontact.get',
        ['CRM_Remotetools_RemoteContactQueryTools', 'processMultivalueOrSearch'], RemoteContactGetRequest::INITIALISATION - 10);
    $dispatcher->addUniqueListener(
        'civi.remotecontact.get',
        ['Civi\RemoteContact\RemoteContactGetRequest', 'addProfileRequirements'], RemoteContactGetRequest::BEFORE_EXECUTE_REQUEST);
    $dispatcher->addUniqueListener(
        'civi.remotecontact.get',
        ['Civi\RemoteContact\RemoteContactGetRequest', 'addProfileRequirements'], RemoteContactGetRequest::BEFORE_EXECUTE_REQUEST);
    $dispatcher->addUniqueListener(
        'civi.remotecontact.get',
        ['Civi\RemoteContact\RemoteContactGetRequest', 'executeRequest'], RemoteContactGetRequest::EXECUTE_REQUEST);
    $dispatcher->addUniqueListener(
        'civi.remotecontact.get',
        ['Civi\RemoteContact\RemoteContactGetRequest', 'filterResult'], RemoteContactGetRequest::AFTER_EXECUTE_REQUEST);

}

function remotetools_civicrm_container(ContainerBuilder $container): void {
    // Allow lazy service instantiation (requires symfony/proxy-manager-bridge)
    if (class_exists(\ProxyManager\Configuration::class) && class_exists(RuntimeInstantiator::class)) {
        $container->setProxyInstantiator(new RuntimeInstantiator());
    }

    $globResource = new GlobResource(__DIR__ . '/services', '/*.php', FALSE);
    // Container will be rebuilt if a *.php file is added to services
    $container->addResource($globResource);
    foreach ($globResource->getIterator() as $path => $info) {
        // Container will be rebuilt if file changes
        $container->addResource(new FileResource($path));
        require $path;
    }
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function remotetools_civicrm_install()
{
    _remotetools_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_postInstall
 */
function remotetools_civicrm_postInstall()
{
    _remotetools_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_uninstall
 */
function remotetools_civicrm_uninstall()
{
    _remotetools_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function remotetools_civicrm_enable()
{
    _remotetools_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_disable
 */
function remotetools_civicrm_disable()
{
    _remotetools_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_upgrade
 */
function remotetools_civicrm_upgrade($op, CRM_Queue_Queue $queue = null)
{
    return _remotetools_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_entityTypes
 */
function remotetools_civicrm_entityTypes(&$entityTypes)
{
    _remotetools_civix_civicrm_entityTypes($entityTypes);
}

/**
 * Define custom (Drupal) permissions
 */
function remotetools_civicrm_permission(&$permissions) {
    // remote contacts
    $permissions['match remote contacts'] = E::ts('RemoteContacts: match and link');
    $permissions['retrieve remote contact information'] = E::ts('RemoteContacts: retrieve');
    $permissions['retrieve own contact information'] = E::ts('RemoteContacts: retrieve self');
    $permissions['update remote contact information'] = E::ts('RemoteContacts: update');
}

/**
 * Set permissions RemoteContact API
 */
function remotetools_civicrm_alterAPIPermissions($entity, $action, &$params, &$permissions) {
    $permissions['remote_contact']['match']     = ['match remote contacts'];
    $permissions['remote_contact']['get_roles'] = ['retrieve remote contact information'];
    $permissions['remote_contact']['get']       = ['retrieve remote contact information'];
    $permissions['remote_contact']['get_self']  = ['retrieve own contact information'];
    $permissions['remote_contact']['update']    = ['update remote contact information'];
}
