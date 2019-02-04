<?php

namespace NuvoleWeb\Drupal\Driver\Objects\Drupal8;

use NuvoleWeb\Drupal\Driver\Objects\StateInterface;

/**
 * Class State.
 *
 * @package NuvoleWeb\Drupal\Driver\Objects\Drupal8
 */
class State implements StateInterface {

  /**
   * {@inheritdoc}
   */
  public function get($key, $default = NULL) {
    return \Drupal::state()->get($key, $default);
  }

  /**
   * {@inheritdoc}
   */
  public function set($key, $value) {
    \Drupal::state()->set($key, $value);
  }

  /**
   * {@inheritdoc}
   */
  public function delete($key) {
    \Drupal::state()->delete($key);
  }

  /**
   * {@inheritdoc}
   */
  public function resetCache() {
    \Drupal::state()->resetCache();
  }

}
