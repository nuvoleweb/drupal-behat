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

}
