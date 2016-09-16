<?php

namespace NuvoleWeb\Drupal\Driver\Cores;

use function bovigo\assert\assert;
use function bovigo\assert\predicate\isNotEmpty;
use function bovigo\assert\predicate\isNotEqualTo;
use Drupal\Driver\Cores\Drupal8 as OriginalDrupal8;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\taxonomy\Entity\Term;
use Drupal\taxonomy\Entity\Vocabulary;

/**
 * Class Drupal8.
 *
 * @package NuvoleWeb\Drupal\Driver\Cores
 */
class Drupal8 extends OriginalDrupal8 implements CoreInterface {

  /**
   * {@inheritdoc}
   */
  public function convertLabelToNodeTypeId($type) {
    // First suppose that the id has been passed.
    if (NodeType::load($type)) {
      return $type;
    }
    $storage = \Drupal::entityTypeManager()->getStorage('node_type');
    $result = $storage->loadByProperties(['name' => $type]);
    assert($result, isNotEmpty());
    return key($result);
  }

  /**
   * {@inheritdoc}
   */
  public function convertLabelToTermTypeId($type) {
    // First suppose that the id has been passed.
    if (Vocabulary::load($type)) {
      return $type;
    }
    $storage = \Drupal::entityTypeManager()->getStorage('taxonomy_vocabulary');
    $result = $storage->loadByProperties(['name' => $type]);
    assert($result, isNotEmpty());
    return key($result);
  }

  /**
   * {@inheritdoc}
   */
  public function loadNodeByName($title) {
    $result = \Drupal::entityQuery('node')
      ->condition('title', $title)
      ->condition('status', NODE_PUBLISHED)
      ->range(0, 1)
      ->execute();
    assert($result, isNotEmpty());
    $nid = current($result);
    return Node::load($nid);
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityIdByLabel($entity_type, $bundle, $label) {
    /** @var \Drupal\node\NodeStorage $storage */
    $storage = \Drupal::entityTypeManager()->getStorage($entity_type);
    $bundle_key = $storage->getEntityType()->getKey('bundle');
    $label_key = $storage->getEntityType()->getKey('label');
    $result = \Drupal::entityQuery($entity_type)
      ->condition($bundle_key, $bundle)
      ->condition($label_key, $label)
      ->range(0, 1)
      ->execute();
    assert($result, isNotEmpty());
    return current($result);
  }

  /**
   * {@inheritdoc}
   */
  public function loadUserByName($name) {
    $user = user_load_by_name($name);
    assert($user, isNotEqualTo(FALSE));
    return $user;
  }

  /**
   * {@inheritdoc}
   */
  public function nodeAccess($op, $name, $node) {
    $account = $this->loadUserByName($name);
    return $node->access($op, $account);
  }

  /**
   * {@inheritdoc}
   */
  public function getNodeId($node) {
    return $node->id();
  }

  /**
   * {@inheritdoc}
   */
  public function loadTaxonomyTermByName($type, $name) {
    $result = \Drupal::entityQuery('taxonomy_term')
      ->condition('name', $name)
      ->condition('vid', $type)
      ->range(0, 1)
      ->execute();
    assert($result, isNotEmpty());
    $id = current($result);
    return Term::load($id);
  }

  /**
   * {@inheritdoc}
   */
  public function getTaxonomyTermId($term) {
    return $term->id();
  }

}
