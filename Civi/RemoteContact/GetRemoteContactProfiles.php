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

namespace Civi\RemoteContact;

use Civi\RemoteToolsRequest;

/**
 * Class RemoteEvent
 *
 * @package Civi\RemoteEvent\Event
 *
 * Abstract event class to provide some basic functions
 */
class GetRemoteContactProfiles extends RemoteToolsRequest {
  /**
   * @var string looking for a specific one? */
  protected ?string $name_filter = NULL;

  /**
   * @var array list of CRM_Remotetools_RemoteContactProfile instances matching the name_filter */
  protected array $profile_instances = [];

  /**
   * RemoteContactGetRequest constructor.
   *
   * @param null $name_filter
   */
  public function __construct(?string $name_filter = NULL) {
    parent::__construct([]);
    if ($name_filter) {
      // make sure this is a regex
      // todo: do a _much_ better job here!
      if (substr($name_filter, 0, 1) != '/') {
        $name_filter = '/' . $name_filter . '/';
      }
    }
    $this->name_filter = $name_filter;
  }

  /**
   * Does the instance name match the
   *
   * @param $instance_name
   */
  public function matchesName(string $instance_name) {
    if ($this->name_filter) {
      return preg_match($this->name_filter, $instance_name);
    }
    else {
      return TRUE;
    }
  }

  /**
   * Add a remote contact profile instance to the list
   * @param \CRM_Remotetools_RemoteContactProfile $instance
   */
  public function addInstance(\CRM_Remotetools_RemoteContactProfile $instance): void {
    $this->profile_instances[] = $instance;
  }

  /**
   * get the number of instances
   *
   * @return integer
   *   count
   */
  public function getInstanceCount(): int {
    return count($this->profile_instances);
  }

  /**
   * Get the instances gathered
   *
   * @return array
   *   all instances gathered
   */
  public function getInstances(): array {
    return $this->profile_instances;
  }

  /**
   * Get the (first) instance matching the name
   *
   * @return \CRM_Remotetools_RemoteContactProfile
   */
  public function getFirstInstance() {
    return reset($this->profile_instances);
  }

}
