<?php
declare(strict_types = 1);

namespace Civi\RemoteTools\EntityProfile\Helper;

use Civi\Api4\Generic\Result;
use Civi\RemoteTools\Api4\Action\RemoteGetAction;
use Civi\RemoteTools\EntityProfile\RemoteEntityProfileInterface;

interface ProfileEntityLoaderInterface {

  /**
   * @throws \CRM_Core_Exception
   */
  public function get(RemoteEntityProfileInterface $profile, RemoteGetAction $action): Result;

}
