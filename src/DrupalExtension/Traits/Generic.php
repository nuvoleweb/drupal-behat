<?php

namespace NuvoleWeb\Drupal\DrupalExtension\Traits;

use Behat\Mink\Exception\ExpectationException;

/**
 * Trait Generic.
 *
 * @package Nuvole\Drupal\Behat\Traits
 */
trait Generic {

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

}
