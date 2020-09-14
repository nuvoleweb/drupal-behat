<?php

namespace NuvoleWeb\Drupal\Driver\Objects;

/**
 * This is in essence an interface for Drupal 8s \Drupal\Core\Config\Config.
 */
interface EditableConfigInterface {

  /**
   * Gets data from this configuration object.
   *
   * @param string $key
   *   A string that maps to a key within the configuration data.
   *   For instance in the following configuration array:.
   *
   * @code
   *   array(
   *     'foo' => array(
   *       'bar' => 'baz',
   *     ),
   *   );
   * @endcode
   *   A key of 'foo.bar' would return the string 'baz'. However, a key of 'foo'
   *   would return array('bar' => 'baz').
   *   If no key is specified, then the entire data array is returned.
   *
   * @return mixed
   *   The data that was requested.
   */
  public function get($key = '');

  /**
   * Sets a value in this configuration object.
   *
   * @param string $key
   *   Identifier to store value in configuration.
   * @param mixed $value
   *   Value to associate with identifier.
   *
   * @return $this
   *   The configuration object.
   */
  public function set($key, $value);

  /**
   * Replaces the data of this configuration object.
   *
   * @param array $data
   *   The new configuration data.
   *
   * @return $this
   *   The configuration object.
   */
  public function setData(array $data);

  /**
   * Gets the raw data without overrides.
   *
   * @return array
   *   The raw data.
   */
  public function getData();

  /**
   * Saves the configuration object.
   */
  public function save();

}
