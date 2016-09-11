<?php

namespace NuvoleWeb\Drupal\DrupalExtension\Context;

use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\TableNode;

/**
 * Extends Drupal Extension DrupalContext class.
 *
 * Load this context instead of Drupal\DrupalExtension\Context\DrupalContext.
 *
 * @package NuvoleWeb\Drupal\DrupalExtension\Context
 */
class DrupalContext extends RawDrupalContext implements SnippetAcceptingContext {

  /**
   * Assert access denied page.
   *
   * @Then I should get an access denied error
   */
  public function assertAccessDenied() {
    $this->assertSession()->statusCodeEquals(403);
  }

  /**
   * Pause execution for given number of seconds.
   *
   * @Then I wait :seconds seconds
   */
  public function iWaitSeconds($seconds) {
    sleep((int) $seconds);
  }

  /**
   * Creates content by filling specified form fields via the UI.
   *
   * Use as follow:
   *
   *  | Title    | Author     | Label | of the field      |
   *  | My title | Joe Editor | 1     | 2014-10-17 8:00am |
   *  | ...      | ...        | ...   | ...               |
   *
   * @Given I create :type content:
   */
  public function manuallyCreateNodes($type, TableNode $nodesTable) {
    $type = $this->getCore()->convertLabelToNodeTypeId($type);

    foreach ($nodesTable->getHash() as $nodeHash) {
      $this->getSession()->visit($this->locatePath("/node/add/$type"));
      $element = $this->getSession()->getPage();
      // Fill in the form.
      foreach ($nodeHash as $field => $value) {
        $element->fillField($field, $value);
      }
      $submit = $element->findButton($this->getDrupalText('node_submit_label'));
      if (empty($submit)) {
        throw new \Exception(sprintf("No submit button at %s", $this->getSession()->getCurrentUrl()));
      }
      // Submit the form.
      $submit->click();
    }

  }

}
