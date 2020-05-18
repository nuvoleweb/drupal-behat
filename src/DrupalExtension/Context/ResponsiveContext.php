<?php

namespace NuvoleWeb\Drupal\DrupalExtension\Context;

use Behat\Mink\Exception\ExpectationException;
use Webmozart\Assert\Assert;

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
   *   List of devices.
   */
  public function __construct(array $devices = []) {
    $this->devices = $devices + $this->defaultDevices;
  }

  /**
   * {@inheritDoc}
   */
  public function getSession($name = null)
  {
    $session = parent::getSession($name);
    if (!$session->isStarted()) {
      $session->start();
    }
    return $session;
  }

  /**
   * Get device resolution.
   *
   * @param string $name
   *   Device name.
   *
   * @return \NuvoleWeb\Drupal\DrupalExtension\Component\ResolutionComponent
   *   Resolution object.
   */
  protected function getDeviceResolution($name) {
    Assert::keyExists($this->devices, $name, "Device '{$name}' not found.");
    $service = $this->getContainer()->get('drupal.behat.component.resolution');
    $service->parse($this->devices[$name]);
    return $service;
  }

  /**
   * Resize browser window according to the specified device.
   *
   * @param string $device
   *   Device name as specified in behat.yml.
   *
   * @Given I view the site on a :device device
   */
  public function assertDeviceScreenResize($device) {
    $resolution = $this->getDeviceResolution($device);
    $this->getSession()->resizeWindow((int) $resolution->getWidth(), (int) $resolution->getHeight(), 'current');
  }

  /**
   * Resize browser window width.
   *
   * @param string $size
   *   Size in pixel.
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
   *   Size in pixel.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   *
   * @Then the browser window height should be :size
   */
  public function assertBrowserWindowHeight($size) {
    $actual = $this->getSession()->evaluateScript('return window.outerHeight;');
    if ($actual != $size) {
      throw new ExpectationException("Browser window height expected to be {$size} but it is {$actual} instead.", $this->getSession());
    }
  }

}
