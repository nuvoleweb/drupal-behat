<?php

namespace NuvoleWeb\Drupal\DrupalExtension\Component;

use function bovigo\assert\predicate\isNotEmpty;
use function bovigo\assert\predicate\hasKey;
use function bovigo\assert\assert;

/**
 * Class ResolutionComponent.
 *
 * @package NuvoleWeb\Drupal\DrupalExtension\Component
 */
class ResolutionComponent {

  /**
   * Resolution format.
   */
  const RESOLUTION_FORMAT = '/(\d*)x(\d*)/';

  /**
   * Resolution width.
   *
   * @var int
   */
  private $width = 0;

  /**
   * Resolution height.
   *
   * @var int
   */
  private $height = 0;

  /**
   * Get width.
   *
   * @return int
   *    Resolution width.
   */
  public function getWidth() {
    return $this->width;
  }

  /**
   * Set width.
   *
   * @param int $width
   *    Resolution width.
   */
  public function setWidth($width) {
    $this->width = $width;
  }

  /**
   * Get height.
   *
   * @return int
   *    Resolution height.
   */
  public function getHeight() {
    return $this->height;
  }

  /**
   * Set height.
   *
   * @param int $height
   *    Resolution height.
   */
  public function setHeight($height) {
    $this->height = $height;
  }

  /**
   * Parse resolution.
   *
   * @param string $resolution
   *    Resolution string, i.e. "360x640".
   *
   * @return $this
   */
  public function parse($resolution) {
    preg_match_all(self::RESOLUTION_FORMAT, $resolution, $matches);
    $message = "Cannot parse provided resolution '{$resolution}'. It must be in the following format: 360x640";
    assert($matches, isNotEmpty()->and(hasKey(0))->and(hasKey(1))->and(hasKey(2)), $message);
    assert($matches[1][0], isNotEmpty(), $message);
    assert($matches[2][0], isNotEmpty(), $message);
    $this->setWidth($matches[1][0]);
    $this->setHeight($matches[2][0]);
    return $this;
  }

}
