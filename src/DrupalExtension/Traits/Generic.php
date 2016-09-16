<?php

namespace NuvoleWeb\Drupal\DrupalExtension\Traits;

use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\ExpectationException;
use Behat\Mink\Exception\UnsupportedDriverActionException;

/**
 * Trait Generic.
 *
 * @package Nuvole\Drupal\Behat\Traits
 */
trait Generic {

  /**
   * Checks that the given element is of the given type.
   *
   * @param NodeElement $element
   *   The element to check.
   * @param string $type
   *   The expected type.
   *
   * @throws ExpectationException
   *   Thrown when the given element is not of the expected type.
   */
  public function assertElementType(NodeElement $element, $type) {
    if ($element->getTagName() !== $type) {
      throw new ExpectationException("The element is not a '$type'' field.", $this->getSession());
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

  /**
   * Visit taxonomy term page given its type and name.
   *
   * @Given I am visiting the :type term :title
   * @Given I visit the :type term :title
   */
  public function iAmViewingTheTerm($type, $title) {
    $this->visitTermPage('view', $type, $title);
  }

  /**
   * Visit taxonomy term edit page given its type and name.
   *
   * @Given I am editing the :type term :title
   * @Given I edit the :type term :title
   */
  public function iAmEditingTheTerm($type, $title) {
    $this->visitTermPage('edit', $type, $title);
  }

  /**
   * Provides a common step definition callback for node pages.
   *
   * @param string $op
   *   The operation being performed: 'view', 'edit', 'delete'.
   * @param string $type
   *   The node type either as id or as label.
   * @param string $title
   *   The node title.
   *
   * @throws ExpectationException
   *   When the node does not exist.
   */
  protected function visitTermPage($op, $type, $title) {
    $type = $this->convertLabelToTermTypeId($type);
    $result = \Drupal::entityQuery('taxonomy_term')
      ->condition('vid', $type)
      ->condition('name', $title)
      ->execute();

    if (!empty($result)) {
      $tid = array_shift($result);
      $path = [
        'view' => "taxonomy/term/$tid",
        'edit' => "taxonomy/term/$tid/edit",
        'delete' => "taxonomy/term/$tid/delete",
      ];
      $this->visitPath($path[$op]);
    }
    else {
      throw new ExpectationException("No term with vocabulary '$type' and title '$title' has been found.", $this->getSession());
    }
  }

  /**
   * Assert first element precedes second one.
   *
   * @Then :first should precede :second
   */
  public function shouldPrecede($first, $second) {
    $page = $this->getSession()->getPage()->getText();
    $pos1 = strpos($page, $first);
    if ($pos1 === FALSE) {
      throw new ExpectationException("Text not found: '$first'.", $this->getSession());
    }
    $pos2 = strpos($page, $second);
    if ($pos2 === FALSE) {
      throw new ExpectationException("Text not found: '$second'.", $this->getSession());
    }
    if ($pos2 < $pos1) {
      throw new \Exception("Text '$first' does not precede text '$second'.");
    }
  }

}
