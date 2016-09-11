<?php

namespace NuvoleWeb\Drupal\DrupalExtension\Context;

use Behat\Mink\Exception\ExpectationException;
use Drupal\Component\Render\FormattableMarkup;
use Drupal\DrupalExtension\Context\RawDrupalContext as OriginalRawDrupalContext;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\taxonomy\Entity\Vocabulary;

/**
 * Class RawDrupalContext.
 *
 * @package NuvoleWeb\Drupal\DrupalExtension\Context
 */
class RawDrupalContext extends OriginalRawDrupalContext {

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

  /**
   * Converts a node-type label into its id.
   *
   * @param string $type
   *   The node-type ID or label.
   *
   * @return string
   *   The node-type ID.
   *
   * @throws ExpectationException
   *   When the passed node type does not exist.
   */
  protected function convertLabelToNodeTypeId($type) {
    // First suppose that the id has been passed.
    if (NodeType::load($type)) {
      return $type;
    }
    $storage = \Drupal::entityTypeManager()->getStorage('node_type');
    if ($result = $storage->loadByProperties(['name' => $type])) {
      return key($result);
    }

    throw new ExpectationException("Node type '$type' doesn't exist.", $this->getSession());
  }

  /**
   * Converts a vocabulary label into its id.
   *
   * @param string $type
   *   The node-type id or label.
   *
   * @return string
   *   The node-type id.
   *
   * @throws ExpectationException
   *   When the passed node type doesn't exist.
   */
  protected function convertLabelToTermTypeId($type) {
    // First suppose that the id has been passed.
    if (Vocabulary::load($type)) {
      return $type;
    }
    $storage = \Drupal::entityTypeManager()->getStorage('taxonomy_vocabulary');
    if ($result = $storage->loadByProperties(['name' => $type])) {
      return key($result);
    }

    throw new ExpectationException("Node type '$type' doesn't exist.", $this->getSession());
  }

}
