<?php

namespace NuvoleWeb\Drupal\DrupalExtension\Context;

use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\ExpectationException;
use Behat\MinkExtension\Context\RawMinkContext as OriginalRawMinkContext;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class RawMinkContext.
 *
 * @package NuvoleWeb\Drupal\DrupalExtension\Context
 */
class RawMinkContext extends OriginalRawMinkContext implements ServiceContainerAwareInterface, SnippetAcceptingContext {

  /**
   * Service container instance.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerBuilder
   */
  private $container;

  /**
   * {@inheritdoc}
   */
  public function setContainer(ContainerBuilder $container) {
    $this->container = $container;
  }

  /**
   * {@inheritdoc}
   */
  public function getContainer() {
    return $this->container;
  }

  /**
   * Checks that the given element is of the given type.
   *
   * @param \Behat\Mink\Element\NodeElement $element
   *   The element to check.
   * @param string $type
   *   The expected type.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   *   Thrown when the given element is not of the expected type.
   */
  public function assertElementType(NodeElement $element, $type) {
    if ($element->getTagName() !== $type) {
      throw new ExpectationException("The element is not a '$type'' field.", $this->getSession());
    }
  }

}
