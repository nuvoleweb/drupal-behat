<?php

namespace NuvoleWeb\Drupal\DrupalExtension\Context;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\DrupalExtension\Context\RawDrupalContext as OriginalRawDrupalContext;
use Drupal\node\Entity\Node;

/**
 * Class RawDrupalContext.
 *
 * @package NuvoleWeb\Drupal\DrupalExtension\Context
 */
class RawDrupalContext extends OriginalRawDrupalContext {

  /**
   * Get current Drupal core.
   *
   * @return \NuvoleWeb\Drupal\Driver\Cores\CoreInterface
   *    Drupal core object instance.
   */
  public function getCore() {
    return $this->getDriver()->getCore();
  }

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
   * Loads a node by name.
   *
   * @param string $title
   *   The title of the node to load.
   *
   * @return Node
   *   The loaded node.
   *
   * @throws \Exception
   *   Thrown when no node with the given title can be loaded.
   */
  public function loadNodeByName($title) {

    $result = \Drupal::entityQuery('node')
      ->condition('title', $title)
      ->condition('status', NODE_PUBLISHED)
      ->range(0, 1)
      ->execute();

    if (empty($result)) {
      $params = array(
        '@title' => $title,
      );
      throw new \Exception(new FormattableMarkup("Node @title not found.", $params));
    }

    $nid = current($result);
    return Node::load($nid);
  }

}
