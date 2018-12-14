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
   *   Entity type machine name.
   * @param string $bundle
   *   Entity bundle machine name, can be empty.
   * @param string $label
   *   Entity name.
   *
   * @return int
   *   Entity ID.
   */
  public function getEntityIdByLabel($entity_type, $bundle, $label);

  /**
   * Create an entity.
   *
   * @param string $entity_type
   *   Entity type.
   * @param mixed $values
   *   The Values to create the entity with.
   *
   * @return object
   *   Entity object.
   */
  public function entityCreate($entity_type, $values);

  /**
   * Load an entity.
   *
   * @param string $entity_type
   *   Entity type.
   * @param int $entity_id
   *   Entity ID.
   *
   * @return object
   *   Entity object.
   */
  public function entityLoad($entity_type, $entity_id);

  /**
   * Add a translation for an entity.
   *
   * @param object $entity
   *   The entity to translate.
   * @param string $language
   *   The language to translate to.
   * @param array $values
   *   The values for the translation.
   *
   * @return object
   *   The translation entity.
   */
  public function entityAddTranslation($entity, $language, array $values);

  /**
   * Load user given its username.
   *
   * @param string $name
   *   User name.
   *
   * @return object
   *   The full user object.
   */
  public function loadUserByName($name);

  /**
   * Check whereas a user can perform and operation on a given node.
   *
   * @param string $op
   *   Operation: view, update or delete.
   * @param string $name
   *   Username.
   * @param object $node
   *   Node object.
   *
   * @return bool
   *   TRUE if user can perform operation, FALSE otherwise.
   */
  public function nodeAccess($op, $name, $node);

  /**
   * Get node ID given node object.
   *
   * @param object $node
   *   Node object.
   *
   * @return int
   *   Node ID.
   */
  public function getNodeId($node);

  /**
   * Load taxonomy term given its vocabulary and name.
   *
   * @param string $type
   *   Vocabulary machine name.
   * @param string $name
   *   Taxonomy term name.
   *
   * @return object
   *   Taxonomy term object.
   */
  public function loadTaxonomyTermByName($type, $name);

  /**
   * Get taxonomy term ID given taxonomy term object.
   *
   * @param object $term
   *   Taxonomy term object.
   *
   * @return int
   *   Taxonomy term ID.
   */
  public function getTaxonomyTermId($term);

  /**
   * Create menu structure.
   *
   * @param string $menu_name
   *   Menu machine name.
   * @param mixed $menu_items
   *   List of menu items specifying title, parent and uri.
   *
   * @return object
   *   The menu entity which can be deleted with entityDelete()
   *
   * @throws \InvalidArgumentException
   *   Throws exception if menu not found.
   */
  public function createMenuStructure($menu_name, $menu_items);

  /**
   * Clears menu cache.
   */
  public function clearMenuCache();

  /**
   * Invalidate cache tags, clearing relevant caches.
   *
   * @param string[] $tags
   *   An array of cache tags to invalidate.
   */
  public function invalidateCacheTags(array $tags);

  /**
   * Get the drupal state.
   *
   * @return \NuvoleWeb\Drupal\Driver\Objects\StateInterface
   *   The drupal 8 state or an object with those methods.
   */
  public function state();

  /**
   * Get an editable config object.
   *
   * @param string $name
   *   The name of the config object.
   *
   * @return \NuvoleWeb\Drupal\Driver\Objects\EditableConfigInterface
   *   The config object to manipulate.
   */
  public function getEditableConfig($name);

}
