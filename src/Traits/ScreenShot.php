<?php
/**
 * @file
 * Contains trait class.
 */

namespace NuvoleWeb\Drupal\Behat\Traits;

use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Element\NodeElement;
use Behat\Mink\Element\DocumentElement;
use Behat\Mink\Exception\ExpectationException;
use Behat\Behat\Context\Environment\InitializedContextEnvironment;
use Behat\Behat\Hook\Scope\AfterStepScope;
use Behat\Mink\Exception\UnsupportedDriverActionException;
use Behat\Mink\Exception\DriverException;

/**
 * Trait ScreenShot.
 *
 * @package Nuvole\Drupal\Behat\Traits
 */
trait ScreenShot {

  /**
   * @AfterStep
   *
   * Make sure there is no PHP notice on the screen during tests.
   *
   * @param $event
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
            $file_name = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'behat-notice__' . $file_name;
            $message = "Screenshot for behat notice in step created in @file_name";
            $this->createScreenshot($file_name, $message);
            // We don't throw $e any more because we don't fail on the notice.
          }
        }
      } catch (DriverException $driver_exception) { }
    }
  }

  /**
   * @AfterStep
   *
   * Take a screen shot after failed steps for Selenium drivers or save the html
   * for non js drivers.
   */
  public function takeScreenshotAfterFailedStep(AfterStepScope $event) {
    if ($event->getTestResult()->isPassed()) {
      // Not a failed step.
      return;
    }
    $file_name = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'behat-failed-step';
    $message = "Screenshot for failed step created in @file_name";
    $this->createScreenshot($file_name, $message);
  }

  /**
   * Create a screenshot or save the html
   *
   * @param string $file_name
   *   The filename of the screenshot (complete).
   * @param string $message
   *   The message to be printed. @file_name will be replaced with $file name.
   * @param bool|TRUE $ext
   *   Whether to add .png or .html to the name of the screenshot.
   */
  public function createScreenshot($file_name, $message, $ext = TRUE) {
    if ($this->getSession()->getDriver() instanceof \Behat\Mink\Driver\Selenium2Driver) {
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
      print strtr($message, ['@file_name' => $file_name]);
    }
  }

}