<?php

namespace NuvoleWeb\Drupal\DrupalExtension\Context;

use Behat\Gherkin\Node\TableNode;
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
  public function overrideParameters(TableNode $table) {
    \Drupal::state()->set('nuvole_web.drupal_extension.parameter_overrides', $table->getRowsHash());
    \Drupal::service('kernel')->rebuildContainer();
  }

  /**
   * Rebuild container on after scenario.
   *
   * @AfterScenario
   */
  public function rebuildContainer() {
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
    catch (ParameterNotFoundException $e) { }
  }

}
