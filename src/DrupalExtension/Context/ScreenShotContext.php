<?php

namespace NuvoleWeb\Drupal\DrupalExtension\Context;

use Behat\Mink\Driver\Selenium2Driver;
use Behat\Behat\Context\Environment\InitializedContextEnvironment;
use Behat\Behat\Hook\Scope\AfterStepScope;
use Behat\Mink\Exception\DriverException;
use Behat\Testwork\Tester\Result\TestResult;

/**
 * Screenshot context for taking screenshots.
 */
class ScreenShotContext extends RawMinkContext {

  /**
   * Contains list of processed devices.
   *
   * @var string
   */
  protected $directory;

  /**
   * Whether or not to create screenshots for notices.
   *
   * @var bool
   */
  protected $notices;

  /**
   * ResponsiveContext constructor.
   *
   * @param string $directory
   *   The directory for screenshots absolute or relative to the drupal root.
   * @param bool $notices
   *   Whether or not to check for notices.
   */
  public function __construct($directory = '', $notices = FALSE) {
    $this->directory = $directory;
    $this->notices = $notices;
  }

  /**
   * Get screenshots path.
   *
   * @return string
   *   Path to screenshots.
   */
  public function getScreenshotsPath() {
    if ($this->directory) {
      $screenshot_dir = $this->directory;
    }
    else {
      $screenshot_dir = getenv('BEHAT_SCREENSHOT_DEBUG') ?: sys_get_temp_dir();
    }
    if (!file_exists($screenshot_dir)) {
      @mkdir($screenshot_dir);
    }

    return $screenshot_dir;
  }

  /**
   * Save screenshot with a specific name.
   *
   * @Then (I )take a screenshot :name
   */
  public function takeScreenshot($name = NULL) {
    $file_name = $this->escapeFilename($name);
    $message = "Screenshot created in @file_name";
    $this->createScreenshot($file_name, $message, FALSE);
  }

  /**
   * Save screenshot.
   *
   * @Then (I )take a screenshot
   */
  public function takeScreenshotUnnamed() {
    $file_name = 'behat-screenshot';
    $message = "Screenshot created in @file_name";
    $this->createScreenshot($file_name, $message);
  }

  /**
   * Make sure there is no PHP notice on the screen during tests.
   *
   * @AfterStep
   */
  public function screenshotForPhpNotices(AfterStepScope $event) {
    if (!$this->notices) {
      return;
    }
    $environment = $event->getEnvironment();
    // Make sure the environment has the MessageContext.
    $class = 'Drupal\DrupalExtension\Context\MessageContext';
    if ($environment instanceof InitializedContextEnvironment && $environment->hasContextClass($class)) {
      /** @var \Drupal\DrupalExtension\Context\MessageContext $context */
      $context = $environment->getContext($class);
      // Only check if the session is started.
      try {
        if ($context->getMink()->isSessionStarted()) {
          try {
            $context->assertNotWarningMessage('Notice:');
            $context->assertNotErrorVisible('Notice:');
          }
          catch (\Exception $e) {
            // Use the step test in the filename.
            $step = $event->getStep();
            $file_name = $step->getKeyword() . '_' . $step->getText();
            $file_name = 'behat-notice__' . $file_name;
            $message = "Screenshot for behat notice in step created in @file_name";
            $this->createScreenshot($file_name, $message);
            // We don't throw $e any more because we don't fail on the notice.
          }
        }
      }
      catch (DriverException $driver_exception) {

      }
    }
  }

  /**
   * Take a screenshot after failed steps or save the HTML for non js drivers.
   *
   * @AfterStep
   */
  public function takeScreenshotAfterFailedStep(AfterStepScope $event) {
    if ($event->getTestResult()->getResultCode() !== TestResult::FAILED) {
      // Not a failed step.
      return;
    }
    try {
      $step = $event->getStep();
      $file_name = str_replace('/', '-', $event->getFeature()->getFile()) . '-' . $step->getKeyword() . '_' . $step->getText();
      $file_name = 'behat-failed__' . $file_name;
      $message = "Screenshot for failed step created in @file_name";
      $this->createScreenshotsForErrors($file_name, $message, $event->getTestResult());
    }
    catch (DriverException $e) {
    }
  }

  /**
   * Create screenshots for errors.
   *
   * @param string $file_name
   *   File name where the error will be saved.
   * @param string $message
   *   Error message.
   * @param \Behat\Testwork\Tester\Result\TestResult $result
   *   Test result.
   */
  public function createScreenshotsForErrors($file_name, $message, TestResult $result) {
    $this->createScreenshot($file_name, $message);
  }

  /**
   * Create a screenshot or save the html.
   *
   * @param string $file_name
   *   The filename of the screenshot (complete).
   * @param string $message
   *   The message to be printed. @file_name will be replaced with $file name.
   * @param bool|true $ext
   *   Whether to add .png or .html to the name of the screenshot.
   */
  public function createScreenshot($file_name, $message, $ext = TRUE) {
    $file_name = $this->escapeFilename($file_name);
    $file_name = $this->getScreenshotsPath() . DIRECTORY_SEPARATOR . $file_name;
    if ($this->getSession()->getDriver() instanceof Selenium2Driver) {
      if ($ext) {
        $file_name .= '.png';
      }
      $screenshot = $this->getSession()->getDriver()->getScreenshot();
      file_put_contents($file_name, $screenshot);
    }
    else {
      if ($ext) {
        $file_name .= '.html';
      }
      $html_data = $this->getSession()->getDriver()->getContent();
      file_put_contents($file_name, $html_data);
    }
    if ($message) {
      print strtr($message, ['@file_name' => $file_name]) . "\n";
    }

    return $file_name;
  }

  /**
   * Escape the filename by removing characters that are problematic for files.
   *
   * @param string $name
   *   The name of the file.
   *
   * @return string
   *   The escaped name of the file.
   */
  protected function escapeFilename($name) {
    if (function_exists('transliteration_clean_filename')) {
      return transliteration_clean_filename($name);
    }

    $name = str_replace(' ', '_', $name);
    return preg_replace('![^0-9A-Za-z_.-]!', '', $name);
  }

}
