<?php

namespace NuvoleWeb\Drupal\DrupalExtension\ServiceContainer;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
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

  /**
   * {@inheritdoc}
   */
  public function configure(ArrayNodeDefinition $builder) {
    parent::configure($builder);

    // @codingStandardsIgnoreStart
    $builder->
      children()->
        arrayNode('text')->
          info(
            'Text strings, such as Log out or the Username field can be altered via behat.yml if they vary from the default values.' . PHP_EOL
            . '  log_out: "Sign out"' . PHP_EOL
            . '  log_in: "Sign in"' . PHP_EOL
            . '  password_field: "Enter your password"' . PHP_EOL
            . '  username_field: "Nickname"' . PHP_EOL
            . '  node_submit_label: "Save"'
          )->
          addDefaultsIfNotSet()->
            children()->
              scalarNode('log_in')->
                defaultValue('Log in')->
              end()->
              scalarNode('log_out')->
                defaultValue('Log out')->
              end()->
              scalarNode('password_field')->
                defaultValue('Password')->
              end()->
              scalarNode('username_field')->
                defaultValue('Username')->
              end()->
              scalarNode('node_submit_label')->
                defaultValue('Save')->
              end()->
          end()->
        end()->
      end();
    // @codingStandardsIgnoreEnd
  }

}
