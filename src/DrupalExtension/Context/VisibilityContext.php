<?php

namespace NuvoleWeb\Drupal\DrupalExtension\Context;

use Behat\Mink\Exception\UnsupportedDriverActionException;

/**
 * Class VisibilityContext.
 *
 * @package NuvoleWeb\Drupal\DrupalExtension\Context
 */
class VisibilityContext extends RawMinkContext {

  /**
   * Assert presence of given field on the page.
   *
   * @Then I should see the field :field
   */
  public function iShouldSeeTheField($field) {
    $element = $this->getSession()->getPage();
    $result = $element->findField($field);
    try {
      if ($result && !$result->isVisible()) {
        throw new \Exception(sprintf("No field '%s' on the page %s", $field, $this->getSession()->getCurrentUrl()));
      }
    }
    catch (UnsupportedDriverActionException $e) {
      // We catch the UnsupportedDriverActionException exception in case
      // this step is not being performed by a driver that supports javascript.
      // All other exceptions are valid.
    }
    if (empty($result)) {
      throw new \Exception(sprintf("No field '%s' on the page %s", $field, $this->getSession()->getCurrentUrl()));
    }
  }

  /**
   * Assert absence of given field.
   *
   * @Then I should not see the field :field
   */
  public function iShouldNotSeeTheField($field) {
    $element = $this->getSession()->getPage();
    $result = $element->findField($field);
    try {
      if ($result && $result->isVisible()) {
        throw new \Exception(sprintf("The field '%s' was present on the page %s and was not supposed to be", $field, $this->getSession()->getCurrentUrl()));
      }
    }
    catch (UnsupportedDriverActionException $e) {
      // We catch the UnsupportedDriverActionException exception in case
      // this step is not being performed by a driver that supports javascript.
      // All other exceptions are valid.
      if ($result) {
        throw new \Exception(sprintf("The field '%s' was present on the page %s and was not supposed to be", $field, $this->getSession()->getCurrentUrl()));
      }
    }
  }

}
