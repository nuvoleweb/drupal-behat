<?php

namespace NuvoleWeb\Drupal\DrupalExtension\Context;

use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\ExpectationException;
use Behat\Mink\Exception\UnsupportedDriverActionException;

/**
 * Visibility context with step definitions.
 */
class VisibilityContext extends RawMinkContext {

  /**
   * Assert presence of given element on the page.
   *
   * @Then the element :tag with text :text should be visible
   */
  public function assertElementVisibility($tag, $text) {
    $element = $this->getSession()->getPage();
    /** @var \Behat\Mink\Element\NodeElement[] $nodes */
    $nodes = $element->findAll('css', $tag);
    foreach ($nodes as $node) {
      if ($node->getText() === $text) {
        $this->assertElementVisible($text, $node);
      }
    }
  }

  /**
   * Assert absence of given element on the page.
   *
   * @Then the element :tag with text :text should not be visible
   */
  public function assertElementNonVisibility($tag, $text) {
    $element = $this->getSession()->getPage();
    /** @var \Behat\Mink\Element\NodeElement[] $nodes */
    $nodes = $element->findAll('css', $tag);
    foreach ($nodes as $node) {
      if ($node->getText() === $text) {
        $this->assertElementNotVisible($text, $node);
      }
    }
  }

  /**
   * Assert presence of given field on the page.
   *
   * @Then I should see the field :field
   */
  public function iShouldSeeTheField($field) {
    $element = $this->getSession()->getPage();
    $result = $element->findField($field);
    $this->assertElementVisible($field, $result);
  }

  /**
   * Assert absence of given field.
   *
   * @Then I should not see the field :field
   */
  public function iShouldNotSeeTheField($field) {
    $element = $this->getSession()->getPage();
    $this->assertElementNotVisible($field, $element->findField($field));
  }

  /**
   * Assert visibility of an element.
   *
   * @param string $element
   *   Element selector or a string that describes it.
   * @param \Behat\Mink\Element\NodeElement $node
   *   Node representing the element above, if any.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   *    Throws exception if element not found.
   */
  protected function assertElementVisible($element, NodeElement $node) {
    try {
      if ($node && !$node->isVisible()) {
        throw new ExpectationException(sprintf("The element '%s' is not present on the page %s", $element, $this->getSession()->getCurrentUrl()), $this->getSession());
      }
    }
    catch (UnsupportedDriverActionException $e) {
      // We catch the UnsupportedDriverActionException exception in case
      // this step is not being performed by a driver that supports javascript.
      // All other exceptions are valid.
      if (empty($node)) {
        throw new ExpectationException(sprintf("The element '%s' is not present on the page %s", $element, $this->getSession()->getCurrentUrl()), $this->getSession());
      }
    }
  }

  /**
   * Assert non visibility of an element.
   *
   * @param string $element
   *   Element selector or a string that describes it.
   * @param \Behat\Mink\Element\NodeElement $node
   *   Node representing the element above, if any.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   *    Throws exception if element is found.
   */
  protected function assertElementNotVisible($element, NodeElement $node) {
    try {
      if ($node && $node->isVisible()) {
        throw new ExpectationException(sprintf("The field '%s' was present on the page %s and was not supposed to be", $element, $this->getSession()->getCurrentUrl()), $this->getSession());
      }
    }
    catch (UnsupportedDriverActionException $e) {
      // We catch the UnsupportedDriverActionException exception in case
      // this step is not being performed by a driver that supports javascript.
      // All other exceptions are valid.
      if ($node) {
        throw new ExpectationException(sprintf("The field '%s' was present on the page %s and was not supposed to be", $element, $this->getSession()->getCurrentUrl()), $this->getSession());
      }
    }
  }

}
