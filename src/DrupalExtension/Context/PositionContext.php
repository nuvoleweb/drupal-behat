<?php

namespace NuvoleWeb\Drupal\DrupalExtension\Context;

use Behat\Mink\Exception\ExpectationException;

/**
 * Position context to determine the order of elements.
 */
class PositionContext extends RawMinkContext {

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
