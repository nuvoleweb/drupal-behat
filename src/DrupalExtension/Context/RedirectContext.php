<?php

namespace NuvoleWeb\Drupal\DrupalExtension\Context;

use Behat\MinkExtension\Context\RawMinkContext;
use Behat\Behat\Context\SnippetAcceptingContext;
use function bovigo\assert\assert;
use function bovigo\assert\predicate\hasKey;
use function bovigo\assert\predicate\equals;

/**
 * Class RedirectContext.
 *
 * Contains step definitions that helps testing redirection.
 *
 * @package NuvoleWeb\Drupal\DrupalExtension\Context
 */
class RedirectContext extends RawMinkContext implements SnippetAcceptingContext {

  /**
   * Store follow redirect configuration.
   *
   * @var null
   */
  private $followRedirect = NULL;

  /**
   * Do not follow redirects.
   *
   * @When /^I do not follow redirects$/
   */
  public function disableFollowRedirects() {
    /* @var \Behat\Mink\Driver\GoutteDriver $driver */
    $driver = $this->getSession()->getDriver();
    $this->followRedirect = $driver->getClient()->isFollowingRedirects();
    $driver->getClient()->followRedirects(FALSE);
  }

  /**
   * Reset follow redirect to its original value.
   *
   * @AfterScenario
   */
  public function resetFollowRedirect() {
    if ($this->followRedirect !== NULL) {
      /* @var \Behat\Mink\Driver\GoutteDriver $driver */
      $driver = $this->getSession()->getDriver();
      $driver->getClient()->followRedirects($this->followRedirect);
    }

    $this->followRedirect = NULL;
  }

  /**
   * Checks if I am redirected to $actualPath.
   *
   * @param string $actualPath
   *   Path to be redirected to.
   *
   * @Then /^I (?:am|should be) redirected to "([^"]*)"$/
   */
  public function thenIamRedirectedTo($actualPath) {
    $headers = $this->getSession()->getResponseHeaders();
    assert($headers['Location'], hasKey(0));

    $redirectComponents = parse_url($headers['Location'][0]);
    assert($redirectComponents['path'], equals($actualPath));
  }

}
