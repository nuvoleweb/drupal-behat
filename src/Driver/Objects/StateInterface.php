<?php

namespace NuvoleWeb\Drupal\Driver\Objects;

/**
 * Defines the interface for the state system.
 *
 * This is a subset of the Drupal 8 state for easier porting to Drupal 7
 */
interface StateInterface {

  /**
   * Returns the stored value for a given key.
   *
   * @param string $key
   *   The key of the data to retrieve.
   * @param mixed $default
   *   The default value to use if the key is not found.
   *
   * @return mixed
   *   The stored value, or NULL if no value exists.
   */
  public function get($key, $default = NULL);

  /**
   * Saves a value for a given key.
   *
   * @param string $key
   *   The key of the data to store.
   * @param mixed $value
   *   The data to store.
   */
  public function set($key, $value);

  /**
   * Deletes an item.
   *
   * @param string $key
   *   The item name to delete.
   */
  public function delete($key);

  /**
   * Resets the static cache.
   *
   * This is mainly used in testing environments.
   */
  public function resetCache();

}

