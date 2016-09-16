<?php

namespace NuvoleWeb\Drupal\DrupalExtension\Context;

use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Exception\ExpectationException;

/**
 * Class ChosenFieldContext.
 *
 * @package NuvoleWeb\Drupal\DrupalExtension\Context
 */
class ChosenFieldContext extends RawMinkContext {

  /**
   * Fills in chosen form fields with provided table.
   *
   * @When /^(?:|I )fill in the following chosen fields:$/
   */
  public function fillChosenFields(TableNode $fields) {
    foreach ($fields->getRowsHash() as $field => $value) {
      $this->iSetChosenElement($field, $value);
    }
  }

  /**
   * Fills in chosen form fields with provided table.
   *
   * @When /^(?:|I )unset the following chosen fields:$/
   */
  public function unsetChosenFields(TableNode $fields) {
    foreach ($fields->getRowsHash() as $field => $value) {
      $this->iUnSetChosenElement($value, $field);
    }
  }

  /**
   * Select one more option from a Chosen select box.
   *
   * @When /^I add "([^"]*)" to the chosen element "([^"]*)"$/
   */
  public function iAddChosenElement($value, $locator) {
    $this->iSetChosenElement($locator, $value);
  }

  /**
   * This is from a patch which is very much work-in-progress.
   *
   * @link https://www.drupal.org/node/2562805.
   *
   * @When /^I set the chosen element "([^"]*)" to "([^"]*)"$/
   */
  public function iSetChosenElement($locator, $value) {
    $session = $this->getSession();
    $el = $session->getPage()->findField($locator);

    if (empty($el)) {
      throw new ExpectationException('No such select element ' . $locator, $session);
    }

    $element_id = str_replace('-', '_', $el->getAttribute('id')) . '_chosen';

    $el = $session->getPage()->find('xpath', "//div[@id='{$element_id}']");

    if ($el->hasClass('chosen-container-single')) {
      // This is a single select element.
      $el = $session->getPage()->find('xpath', "//div[@id='{$element_id}']/a[@class='chosen-single']");
      $el->click();
    }
    elseif ($el->hasClass('chosen-container-multi')) {
      // This is a multi select element.
      $el = $session->getPage()->find('xpath', "//div[@id='{$element_id}']/ul[@class='chosen-choices']/li[@class='search-field']/input");
      $el->click();
    }

    $selector = "//div[@id='{$element_id}']/div[@class='chosen-drop']/ul[@class='chosen-results']/li[text() = '{$value}']";
    $el = $session->getPage()->find('xpath', $selector);

    if (empty($el)) {
      throw new ExpectationException('No such option ' . $value . ' in ' . $locator, $session);
    }

    $el->click();
  }

  /**
   * Remove an option from a Chosen select box.
   *
   * @When /^I remove "([^"]*)" from the chosen element "([^"]*)"$/
   */
  public function iUnSetChosenElement($value, $locator) {
    $session = $this->getSession();
    $el = $session->getPage()->findField($locator);

    if (empty($el)) {
      throw new ExpectationException('No such select element ' . $locator, $session);
    }

    $element_id = str_replace('-', '_', $el->getAttribute('id')) . '_chosen';

    $el = $session->getPage()->find('xpath', "//div[@id='{$element_id}']");
    if ($el->hasClass('chosen-container-single')) {
      // This is a single select element, unsetting doesn't make sense.
    }

    $selector = "//div[@id='{$element_id}']/ul[@class='chosen-choices']//li[span = '{$value}']/a";
    $el = $session->getPage()->find('xpath', $selector);

    if (empty($el)) {
      throw new ExpectationException('No such option ' . $value . ' selected in ' . $locator, $session);
    }

    $el->click();
  }

}
