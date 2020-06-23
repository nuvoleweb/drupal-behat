<?php

namespace NuvoleWeb\Drupal\DrupalExtension\Context;

use Behat\Mink\Driver\Selenium2Driver;
use Behat\Behat\Context\Environment\InitializedContextEnvironment;
use Behat\Behat\Hook\Scope\AfterStepScope;
use Behat\Mink\Exception\DriverException;
use Behat\Testwork\Tester\Result\TestResult;

/**
 * Class ScreenShotContext.
 *
 * @package NuvoleWeb\Drupal\DrupalExtension\Context
 */
class ScreenShotContext extends RawMinkContext {

  /**
   * Get screenshots path.
   *
   * @return string
   *   Path to screenshots.
   */
  public function getScreenshotsPath() {
    return sys_get_temp_dir();
  }

  /**
   * Save screenshot with a specific name.
   *
   * @Then (I )take a screenshot :name
   */
  public function takeScreenshot($name = NULL) {
    $file_name = $this->getScreenshotsPath() . DIRECTORY_SEPARATOR . $name;
    $message = "Screenshot created in @file_name";
    $this->createScreenshot($file_name, $message, FALSE);
  }

  /**
   * Save screenshot.
   *
   * @Then (I )take a screenshot
   */
  public function takeScreenshotUnnamed() {
    $file_name = $this->getScreenshotsPath() . DIRECTORY_SEPARATOR . 'behat-screenshot';
    $message = "Screenshot created in @file_name";
    $this->createScreenshot($file_name, $message);
  }

  /**
   * Make sure there is no PHP notice on the screen during tests.
   *
   * @AfterStep
   */
  public function screenshotForPhpNotices(AfterStepScope $event) {
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
            if (function_exists('transliteration_clean_filename')) {
              $file_name = transliteration_clean_filename($step->getKeyword() . '_' . $step->getText());
            }
            else {
              $file_name = str_replace(' ', '_', $step->getKeyword() . '_' . $step->getText());
              $file_name = preg_replace('![^0-9A-Za-z_.-]!', '', $file_name);
            }
            $file_name = substr($file_name, 0, 30);
            $file_name = $this->getScreenshotsPath() . DIRECTORY_SEPARATOR . 'behat-notice__' . $file_name;
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
      if (function_exists('transliteration_clean_filename')) {
        $file_name = transliteration_clean_filename($step->getKeyword() . '_' . $step->getText());
      }
      else {
        $file_name = str_replace(' ', '_', $step->getKeyword() . '_' . $step->getText());
        $file_name = preg_replace('![^0-9A-Za-z_.-]!', '', $file_name);
      }
      $file_name = substr($file_name, 0, 30);
      $file_name = $this->getScreenshotsPath() . DIRECTORY_SEPARATOR . 'behat-failed__' . ' - ' . $event->getFeature()->getFile() . '-' . $file_name;
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

}
