<?php

namespace NuvoleWeb\Drupal\DrupalExtension\Context;

use Behat\Mink\Exception\ExpectationException;

/**
 * Class TaxonomyTermContext.
 *
 * @package NuvoleWeb\Drupal\DrupalExtension\Context
 */
class TaxonomyTermContext extends RawDrupalContext {

  /**
   * Visit taxonomy term page given its type and name.
   *
   * @Given I visit the :type term :title
   * @Given I am visiting the :type term :title
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
   * Visit taxonomy term delete page given its type and name.
   *
   * @Given I am deleting the :type term :title
   * @Given I delete the :type term :title
   */
  public function iAmDeletingTheTerm($type, $title) {
    $this->visitTermPage('delete', $type, $title);
  }

  /**
   * Provides a common step definition callback for taxonomy term pages.
   *
   * @param string $op
   *   The operation being performed: 'view', 'edit', 'delete'.
   * @param string $type
   *   The term's vocabulary, either as machine name or label.
   * @param string $name
   *   The term name.
   *
   * @throws ExpectationException
   *   When the term does not exist.
   */
  protected function visitTermPage($op, $type, $name) {
    $type = $this->getCore()->convertLabelToTermTypeId($type);
    $term = $this->getCore()->loadTaxonomyTermByName($type, $name);

    if (!empty($term)) {
      $path = [
        'view' => "taxonomy/term/{$this->getCore()->getTaxonomyTermId($term)}",
        'edit' => "taxonomy/term/{$this->getCore()->getTaxonomyTermId($term)}/edit",
        'delete' => "taxonomy/term/{$this->getCore()->getTaxonomyTermId($term)}/delete",
      ];
      $this->visitPath($path[$op]);
    }
    else {
      throw new ExpectationException("No taxonomy term with vocabulary '$type' and name '$name' has been found.", $this->getSession());
    }
  }

}
