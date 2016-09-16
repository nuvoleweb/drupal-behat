<?php

namespace NuvoleWeb\Drupal\Driver\Cores;

use Drupal\Driver\Cores\CoreInterface as OriginalCoreInterface;

/**
 * Interface CoreInterface.
 *
 * @package NuvoleWeb\Drupal\Driver\Cores
 */
interface CoreInterface extends OriginalCoreInterface {

  /**
   * Converts a node-type label into its id.
   *
   * @param string $type
   *   The node-type ID or label.
   *
   * @return string
   *   The node-type ID.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   *   When the passed node type does not exist.
   */
  public function convertLabelToNodeTypeId($type);

  /**
   * Converts a vocabulary label into its id.
   *
   * @param string $type
   *   The node-type id or label.
   *
   * @return string
   *   The node-type id.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   *   When the passed node type does not exist.
   */
  public function convertLabelToTermTypeId($type);

  /**
   * Loads a node by name.
   *
   * @param string $title
   *   The title of the node to load.
   *
   * @return object
   *   The loaded node.
   *
   * @throws \Exception
   *   Thrown when no node with the given title can be loaded.
   */
  public function loadNodeByName($title);

  /**
   * Get entity ID given its type, bundle and label.
   *
   * @param string $entity_type
   *    Entity type machine name.
   * @param string $bundle
   *    Entity type machine name.
   * @param string $label
   *    Entity type machine name.
   *
   * @return int
   *    Entity ID.
   */
  public function getEntityIdByLabel($entity_type, $bundle, $label);

  /**
   * Load user given its username.
   *
   * @param string $name
   *    User name.
   *
   * @return object
   *    The full user object.
   */
  public function loadUserByName($name);

  /**
   * Check whereas a user can perform and operation on a given node.
   *
   * @param string $op
   *    Operation: view, update or delete.
   * @param string $name
   *    Username.
   * @param object $node
   *    Node object.
   *
   * @return bool
   *    TRUE if user can perform operation, FALSE otherwise.
   */
  public function nodeAccess($op, $name, $node);

  /**
   * Get node ID given node object.
   *
   * @param object $node
   *    Node object.
   *
   * @return int
   *    Node ID.
   */
  public function getNodeId($node);

  /**
   * Load taxonomy term given its vocabulary and name.
   *
   * @param string $type
   *    Vocabulary machine name.
   * @param string $name
   *    Taxonomy term name.
   *
   * @return object
   *    Taxonomy term object.
   */
  public function loadTaxonomyTermByName($type, $name);

  /**
   * Get taxonomy term ID given taxonomy term object.
   *
   * @param object $term
   *    Taxonomy term object.
   *
   * @return int
   *    Taxonomy term ID.
   */
  public function getTaxonomyTermId($term);

}
