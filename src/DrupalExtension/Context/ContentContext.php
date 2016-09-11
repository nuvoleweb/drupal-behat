<?php

namespace NuvoleWeb\Drupal\DrupalExtension\Context;

use Behat\Behat\Context\SnippetAcceptingContext;

/**
 * Class ContentContext.
 *
 * @package NuvoleWeb\Drupal\DrupalExtension\Context
 */
class ContentContext extends RawDrupalContext implements SnippetAcceptingContext {

  /**
   * Assert viewing content given its type and title.
   *
   * @param string $type
   *    Content type machine name.
   * @param string $title
   *    Content title.
   *
   * @Given I am visiting the :type content :title
   * @Given I visit the :type content :title
   */
  public function iAmViewingTheContent($type, $title) {
    $this->visitContentPage('view', $type, $title);
  }

  /**
   * Assert editing content given its type and title.
   *
   * @param string $type
   *    Content type machine name.
   * @param string $title
   *    Content title.
   *
   * @Given I am editing the :type content :title
   * @Given I edit the :type content :title
   */
  public function iAmEditingTheContent($type, $title) {
    $this->visitContentPage('edit', $type, $title);
  }

  /**
   * Assert deleting content given its type and title.
   *
   * @param string $type
   *    Content type machine name.
   * @param string $title
   *    Content title.
   *
   * @Given I am deleting the :type content :title
   * @Given I delete the :type content :title
   */
  public function iAmDeletingTheContent($type, $title) {
    $this->visitContentPage('delete', $type, $title);
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
   */
  protected function visitContentPage($op, $type, $title) {
    $nid = $this->getCore()->getEntityIdByLabel('node', $type, $title);
    $path = [
      'view' => "node/$nid",
      'edit' => "node/$nid/edit",
      'delete' => "node/$nid/delete",
    ];
    $this->visitPath($path[$op]);
  }

}
