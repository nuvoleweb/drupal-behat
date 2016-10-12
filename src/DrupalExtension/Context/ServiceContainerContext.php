<?php

namespace NuvoleWeb\Drupal\DrupalExtension\Context;

use Behat\Gherkin\Node\TableNode;
use Drupal\Core\Site\Settings;
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
use function bovigo\assert\assert;
use function bovigo\assert\predicate\equals;
use function bovigo\assert\predicate\not;

/**
 * Contains Contacts specific step definitions.
 */
class ServiceContainerContext extends RawDrupalContext {

  /**
   * Override service parameters.
   *
   * @see BehatServiceProvider
   *
   * @Given I override the following service parameters:
   */
  public function assertOverrideParameters(TableNode $table) {
    $this->overrideParameters($table->getRowsHash());
  }

  /**
   * Rebuild container on after scenario.
   *
   * @AfterScenario
   */
  public function resetParameters() {
    if (\Drupal::state()->get('nuvole_web.drupal_extension.parameter_overrides')) {
      \Drupal::state()->set('nuvole_web.drupal_extension.parameter_overrides', []);
      \Drupal::service('kernel')->rebuildContainer();
    }
  }

  /**
   * Assert given service parameter has given value.
   *
   * @Then the service parameter :name should be set to :expected
   */
  public function assertParameters($name, $expected) {
    $value = \Drupal::getContainer()->getParameter($name);
    assert($value, equals($expected));
  }

  /**
   * Assert given service parameter has not given value.
   *
   * @Then the service parameter :name should not be set to :expected
   */
  public function negateParameters($name, $expected) {
    try {
      $value = \Drupal::getContainer()->getParameter($name);
      assert($value, not(equals($expected)));
    }
    catch (ParameterNotFoundException $e) {
    }
  }

  /**
   * Apply parameters overrides and rebuild container.
   *
   * @param array $parameters
   *    List of parameters to be overridden.
   */
  protected function overrideParameters(array $parameters) {
    $this->setServiceProvider();
    \Drupal::state()->set('nuvole_web.drupal_extension.parameter_overrides', $parameters);
    \Drupal::service('kernel')->rebuildContainer();
  }

  /**
   * Set custom service provider ar run-time.
   */
  protected function setServiceProvider() {
    // Setting service providers will change in Drupal 8.3.
    // @link https://www.drupal.org/node/2183323
    if (\Drupal::VERSION < '8.3') {
      $GLOBALS['conf']['container_service_providers']['BehatServiceProvider'] = '\NuvoleWeb\Drupal\DrupalExtension\ServiceProvider\BehatServiceProvider';
    }
    else {
      $settings = Settings::getAll();
      $settings['container_service_providers']['BehatServiceProvider'] = '\NuvoleWeb\Drupal\DrupalExtension\ServiceProvider\BehatServiceProvider';
      new Settings($settings);
    }
  }

}
