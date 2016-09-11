<?php

namespace NuvoleWeb\Drupal\Driver\Cores;

use function bovigo\assert\assert;
use function bovigo\assert\predicate\isNotEmpty;
use Drupal\Driver\Cores\Drupal8 as OriginalDrupal8;
use Drupal\node\Entity\NodeType;
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

}
