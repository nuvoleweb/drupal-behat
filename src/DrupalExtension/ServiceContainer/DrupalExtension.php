<?php

namespace NuvoleWeb\Drupal\DrupalExtension\ServiceContainer;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Drupal\DrupalExtension\ServiceContainer\DrupalExtension as OriginalDrupalExtension;

/**
 * Drupal extension which loads the service container.
 */
class DrupalExtension extends OriginalDrupalExtension {

  /**
   * {@inheritdoc}
   */
  public function load(ContainerBuilder $container, array $config) {
    parent::load($container, $config);

    // Load default service definitions.
    $container_overrides = new ContainerBuilder();
    $loader = new YamlFileLoader($container_overrides, new FileLocator(__DIR__ . '/../../..'));
    $loader->load('services.yml');
    $container->merge($container_overrides);

    // Load custom service definitions.
    if ($config['services']) {
      $path_parts = pathinfo($config['services']);
      $container_overrides = new ContainerBuilder();
      $loader = new YamlFileLoader($container_overrides, new FileLocator($path_parts['dirname']));
      $loader->load($path_parts['basename']);
      $container->merge($container_overrides);
    }

    $this->loadContextInitializer($container);
  }

  /**
   * Load context initializer.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
   *   Service container instance.
   */
  private function loadContextInitializer(ContainerBuilder $container) {
    // Set current service container instance for service container initializer.
    $definition = $container->getDefinition('drupal.behat.context_initializer.service_container');
    $definition->addArgument($container);
  }

  /**
   * {@inheritdoc}
   */
  public function configure(ArrayNodeDefinition $builder) {
    parent::configure($builder);

    $builder->append(
      $builder
        ->children()
        ->scalarNode('services')
        ->defaultValue('')
        ->info('Path to service definition YAML file, e.g. "/path/to/my_services.yml". Services and parameters specified therein will override the original Behat Extension service definitions.')
    );

    $builder->find('text')->append(
      $builder
        ->children()
        ->scalarNode('node_submit_label')
        ->defaultValue('Save')
    );
  }

}
