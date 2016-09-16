<?php

namespace NuvoleWeb\Drupal\DrupalExtension\Context;

use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\ExpectationException;
use Behat\MinkExtension\Context\RawMinkContext as OriginalRawMinkContext;

/**
 * Class RawMinkContext.
 *
 * @package NuvoleWeb\Drupal\DrupalExtension\Context
 */
class RawMinkContext extends OriginalRawMinkContext {

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

}
