<?php

namespace NuvoleWeb\Drupal\DrupalExtension\Context;

use Behat\Mink\Exception\ExpectationException;
use function bovigo\assert\predicate\isOfType;
use function bovigo\assert\predicate\hasKey;
use function bovigo\assert\assert;
use Behat\MinkExtension\Context\RawMinkContext;
use NuvoleWeb\Drupal\DrupalExtension\Component\ResolutionComponent;

/**
 * Class ResponsiveContext.
 *
 * @package NuvoleWeb\Drupal\DrupalExtension\Context
 */
class ResponsiveContext extends RawMinkContext {

  /**
   * Default list of device definitions.
   *
   * @var array
   */
  protected $defaultDevices = [
    'mobile_portrait' => '360x640',
    'mobile_landscape' => '640x360',
    'tablet_portrait' => '768x1024',
    'tablet_landscape' => '1024x768',
    'laptop' => '1280x800',
    'desktop' => '2560x1440',
  ];

  /**
   * Contains list of processed devices.
   *
   * @var array
   */
  protected $devices = [];

  /**
   * ResponsiveContext constructor.
   *
   * @param array $devices
   *    List of devices.
   */
  public function __construct($devices = []) {
    assert($devices, isOfType('array'));
    $devices = $devices + $this->defaultDevices;
    $this->processDevices($devices);
  }

  /**
   * Process devices.
   *
   * @param array $devices
   *    List of devices.
   */
  private function processDevices(array $devices) {
    foreach ($devices as $name => $resolution) {
      $resolution_component = new ResolutionComponent();
      $resolution_component->parse($resolution);
      $this->devices[$name] = $resolution_component;
    }
  }

  /**
   * Get device resolution.
   *
   * @param string $name
   *    Device name.
   *
   * @return ResolutionComponent
   *    Resolution object.
   */
  protected function getDeviceResolution($name) {
    assert($this->devices, hasKey($name), "Device '{$name}' not found.");
    return $this->devices[$name];
  }

  /**
   * Resize browser window according to the specified device.
   *
   * @param string $device
   *    Device name as specified in behat.yml.
   *
   * @Given I view the site on a :device device
   */
  public function assertDeviceScreenResize($device) {
    $resolution = $this->getDeviceResolution($device);
    $this->getSession()->resizeWindow($resolution->getWidth(), $resolution->getHeight(), 'current');
  }

  /**
   * Resize browser window width.
   *
   * @param string $size
   *    Size in pixel.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   *
   * @Then the browser window width should be :size
   */
  public function assertBrowserWindowWidth($size) {
    $actual = $this->getSession()->evaluateScript('return window.innerWidth;');
    if ($actual != $size) {
      throw new ExpectationException("Browser window width expected to be {$size} but it is {$actual} instead.", $this->getSession());
    }
  }

  /**
   * Resize browser window height.
   *
   * @param string $size
   *    Size in pixel.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   *
   * @Then the browser window height should be :size
   */
  public function assertBrowserWindowHeight($size) {
    $actual = $this->getSession()->evaluateScript('return window.innerHeight;');
    if ($actual != $size) {
      throw new ExpectationException("Browser window height expected to be {$size} but it is {$actual} instead.", $this->getSession());
    }
  }

}
