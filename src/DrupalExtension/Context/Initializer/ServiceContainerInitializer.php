<?php

namespace NuvoleWeb\Drupal\DrupalExtension\Context\Initializer;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Behat\Behat\Context\Context;
use Behat\Behat\Context\Initializer\ContextInitializer;
use NuvoleWeb\Drupal\DrupalExtension\Context\ServiceContainerAwareInterface;

/**
 * Service Container Initializer gives easy access to the container.
 */
class ServiceContainerInitializer implements ContextInitializer {

  /**
   * Service container instance.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerBuilder
   */
  private $container;

  /**
   * ServiceContainerInitializer constructor.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
   *   Service container instance.
   *
   * @see \NuvoleWeb\Drupal\DrupalExtension\ServiceContainer\DrupalExtension::loadContextInitializer
   */
  public function __construct(ContainerBuilder $container) {
    $this->container = $container;
  }

  /**
   * Initializes provided context.
   *
   * @param \Behat\Behat\Context\Context $context
   *   Context instance.
   */
  public function initializeContext(Context $context) {
    if ($context instanceof ServiceContainerAwareInterface) {
      /** @var \NuvoleWeb\Drupal\DrupalExtension\Context\ServiceContainerAwareInterface $context */
      $context->setContainer($this->container);
    }
  }

}
