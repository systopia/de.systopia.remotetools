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

/**
 * Collection of upgrade steps.
 */
class CRM_Remotetools_Upgrader extends CRM_Extension_Upgrader_Base {

  public function upgrade_0001(): bool {
    // Replaced by managed entity.
    return TRUE;
  }

  public function upgrade_0002(): bool {
    // Replaced by managed entity.
    return TRUE;
  }

}
