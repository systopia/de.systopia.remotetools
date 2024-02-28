<?php
declare(strict_types = 1);

namespace Civi\RemoteTools\EntityProfile\Helper;

use Civi\RemoteTools\Api4\Action\RemoteDeleteAction;
use Civi\RemoteTools\EntityProfile\RemoteEntityProfileInterface;

interface ProfileEntityDeleterInterface {

  /**
   * @phpstan-return array<array<string, mixed>> JSON serializable.
   *   One entry for each deleted record containing at least its id.
   *
   * @throws \CRM_Core_Exception
   */
  public function delete(RemoteEntityProfileInterface $profile, RemoteDeleteAction $action): array;

}
