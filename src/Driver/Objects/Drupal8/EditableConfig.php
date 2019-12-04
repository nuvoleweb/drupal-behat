<?php

namespace NuvoleWeb\Drupal\Driver\Objects\Drupal8;

use NuvoleWeb\Drupal\Driver\Objects\EditableConfigInterface;

/**
 * Class EditableConfig.
 *
 * @package NuvoleWeb\Drupal\Driver\Objects\Drupal8
 */
class EditableConfig implements EditableConfigInterface {

  /**
   * The mutable configuration object.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * EditableConfig constructor.
   *
   * @param string $name
   *   The config name.
   */
  public function __construct($name) {
    $this->config = \Drupal::configFactory()->getEditable($name);
  }

  /**
   * {@inheritdoc}
   */
  public function get($key = '') {
    return $this->config->get($key);
  }

  /**
   * {@inheritdoc}
   */
  public function set($key, $value) {
    return $this->config->set($key, $value);
  }

  /**
   * {@inheritdoc}
   */
  public function setData(array $data) {
    return $this->config->setData($data);
  }

  /**
   * {@inheritdoc}
   */
  public function getData() {
    return $this->config->getRawData();
  }

  /**
   * {@inheritdoc}
   */
  public function save() {
    return $this->config->save();
  }

}
