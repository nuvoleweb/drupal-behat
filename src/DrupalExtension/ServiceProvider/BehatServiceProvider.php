<?php

namespace NuvoleWeb\Drupal\DrupalExtension\ServiceProvider;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Behat service provider for getting parameter overrides.
 */
class BehatServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    foreach ($this->getParameters() as $name => $value) {
      $container->setParameter($name, $value);
    }
  }

  /**
   * Get parameters set in ServiceContainerContext.
   *
   * @see ServiceContainerContext::overrideParameters()
   *
   * @return array
   *   Array of parameters.
   */
  protected function getParameters() {
    try {
      return \Drupal::state()->get('nuvole_web.drupal_extension.parameter_overrides', []);
    }
    catch (ServiceNotFoundException $e) {
      return [];
    }
  }

}
