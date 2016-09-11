<?php

namespace NuvoleWeb\Drupal\DrupalExtension\ServiceContainer;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Drupal\DrupalExtension\ServiceContainer\DrupalExtension as OriginalDrupalExtension;

/**
 * Class DrupalExtension.
 *
 * @package NuvoleWeb\Drupal\DrupalExtension\ServiceContainer
 */
class DrupalExtension extends OriginalDrupalExtension {

  /**
   * {@inheritdoc}
   */
  public function process(ContainerBuilder $container) {
    parent::process($container);
    $container->getParameterBag()->set('drupal.driver.cores.6.class', 'NuvoleWeb\Drupal\Driver\Cores\Drupal6');
    $container->getParameterBag()->set('drupal.driver.cores.7.class', 'NuvoleWeb\Drupal\Driver\Cores\Drupal7');
    $container->getParameterBag()->set('drupal.driver.cores.8.class', 'NuvoleWeb\Drupal\Driver\Cores\Drupal8');
  }

}
