<?php

namespace NuvoleWeb\Drupal\Tests\Behat;

use NuvoleWeb\Drupal\DrupalExtension\Context\RawDrupalContext;
use Webmozart\Assert\Assert;

/**
 * Class TestContext.
 *
 * @package NuvoleWeb\Drupal\Tests\Behat
 */
class TestContext extends RawDrupalContext {

  /**
   * Assert service container.
   *
   * @Given I can access the service container
   *
   * @throws \Exception
   */
  public function assertServiceContainer() {
    $container = $this->getContainer();
    if (empty($container)) {
      throw new \Exception('The service container is not set.');
    }
  }

  /**
   * Assert service.
   *
   * @Then the service container can load the :name service
   */
  public function assertService($name) {
    $this->getContainer()->get($name);
  }

}
