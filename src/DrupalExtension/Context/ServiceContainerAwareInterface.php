<?php

namespace NuvoleWeb\Drupal\DrupalExtension\Context;

use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Interface ServiceContainerAwareInterface.
 *
 * @package NuvoleWeb\Drupal\DrupalExtension\Context
 */
interface ServiceContainerAwareInterface {

  /**
   * Set reference to service container.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
   *   Service container instance.
   */
  public function setContainer(ContainerBuilder $container);

  /**
   * Get service container.
   *
   * @return \Symfony\Component\DependencyInjection\ContainerBuilder
   *   Service container instance.
   */
  public function getContainer();

}
