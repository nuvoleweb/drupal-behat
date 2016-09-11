<?php

use Behat\Behat\Context\Context;
use Drupal\DrupalExtension\Context\RawDrupalContext;

/**
 * Features context for testing the Drupal Extension.
 */
class FeatureContext extends RawDrupalContext implements Context {

  use \NuvoleWeb\Drupal\DrupalExtension\Traits\Generic;

}
