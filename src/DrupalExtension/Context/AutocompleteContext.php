<?php

namespace NuvoleWeb\Drupal\DrupalExtension\Context;

use Behat\Mink\Exception\ExpectationException;
use Behat\Mink\Exception\UnsupportedDriverActionException;

/**
 * Autocomplete step definitions.
 */
class AutocompleteContext extends RawMinkContext {

  /**
   * Fill out autocomplete fields based on gist.
   *
   * @When I fill in the autocomplete :autocomplete with :text
   */
  public function fillInDrupalAutocomplete($locator, $text) {

    $session = $this->getSession();
    $el = $session->getPage()->findField($locator);

    if (empty($el)) {
      throw new ExpectationException('No such autocomplete element ' . $locator, $session);
    }

    // Set the text and trigger the autocomplete with a space keystroke.
    $el->setValue($text);

    try {
      $el->keyDown(' ');
      $el->keyUp(' ');

      // Wait for ajax.
      $this->getSession()->wait(1000, '(typeof(jQuery)=="undefined" || (0 === jQuery.active && 0 === jQuery(\':animated\').length))');
      // Wait a second, just to be sure.
      sleep(1);

      // Select the autocomplete popup with the name we are looking for.
      $popup = $session->getPage()->find('xpath', "//ul[contains(@class, 'ui-autocomplete')]/li/a[text() = '{$text}']");

      if (empty($popup)) {
        throw new ExpectationException('No such option ' . $text . ' in ' . $locator, $session);
      }

      // Clicking on the popup fills the autocomplete properly.
      $popup->click();
    }
    catch (UnsupportedDriverActionException $e) {
      // So javascript is not supported.
      // We did set the value correctly, so Drupal will figure it out.
    }

  }

}
