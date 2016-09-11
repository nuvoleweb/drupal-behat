<?php

namespace NuvoleWeb\Drupal\DrupalExtension\Context;

use Drupal\DrupalExtension\Context\RawDrupalContext as OriginalRawDrupalContext;

/**
 * Class RawDrupalContext.
 *
 * @package NuvoleWeb\Drupal\DrupalExtension\Context
 */
class RawDrupalContext extends OriginalRawDrupalContext {

  /**
   * Get current Drupal core.
   *
   * @return \NuvoleWeb\Drupal\Driver\Cores\CoreInterface|\Drupal\Driver\Cores\CoreInterface
   *    Drupal core object instance.
   */
  public function getCore() {
    return $this->getDriver()->getCore();
  }

}
