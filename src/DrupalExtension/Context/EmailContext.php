<?php

namespace NuvoleWeb\Drupal\DrupalExtension\Context;

use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\TableNode;
use function bovigo\assert\assert;
use function bovigo\assert\predicate\hasKey;
use function bovigo\assert\predicate\equals;

/**
 * Email step definitions.
 *
 * To use the email steps you need to have the mailsystem module enabled.
 */
class EmailContext extends RawDrupalContext {

  /**
   * Current mailsystem settings.
   *
   * @var string
   *    Email address.
   *
   * @see FeatureContext::beforeScenarioEmail()
   * @see FeatureContext::afterScenarioEmail()
   */
  protected $mailsystem = '';

  /**
   * Current contact settings.
   *
   * @var array
   *    Contact settings.
   *
   * @see FeatureContext::beforeScenarioNoContactFlood()
   * @see FeatureContext::beforeScenarioNoContactFlood()
   */
  protected $contactSettings = [];

  /**
   * Assert that an email has been sent to the given recipient.
   *
   * @param string $recipient
   *   Email address.
   *
   * @throws \Exception
   *    Throws an exception if no email has been sent or email is invalid.
   *
   * @Then an email should be sent to :recipient
   */
  public function assertEmailSentToRecipient($recipient) {
    $last_mail = $this->getLastEmail();
    if ($last_mail['to'] != $recipient) {
      throw new \Exception("Unexpected recipient: " . $last_mail['to']);
    }
  }

  /**
   * Assert that the email that has been sent has the given properties.
   *
   * @Then an email with the following properties should have been sent:
   */
  public function assertEmailSentWithProperties(TableNode $table) {
    $last_mail = $this->getLastEmail();
    foreach ($table->getRowsHash() as $name => $value) {
      assert($last_mail, hasKey($name));
      assert($last_mail[$name], equals($value));
    }
  }

  /**
   * Switch to Drupal test mail system for scenarios tagged with @email.
   *
   * @BeforeScenario @email
   */
  public function beforeScenarioEmail(BeforeScenarioScope $scope) {
    $mailsystem = $this->getCore()->getEditableConfig('mailsystem.settings');
    $this->mailsystem = $mailsystem->get('defaults');
    $mailsystem->set('defaults.sender', 'test_mail_collector')->save();
    $this->getCore()->state()->set('system.test_mail_collector', []);
  }

  /**
   * Switch back to original mail system for scenarios tagged with @email.
   *
   * @AfterScenario @email
   */
  public function afterScenarioEmail(AfterScenarioScope $scope) {
    $mailsystem = $this->getCore()->getEditableConfig('mailsystem.settings');
    $mailsystem->set('defaults.sender', $this->mailsystem['sender'])->save();
  }

  /**
   * Increase value of contact form flooding.
   *
   * @BeforeScenario @no_contact_flood
   */
  public function beforeScenarioNoContactFlood(BeforeScenarioScope $scope) {
    $config = $this->getCore()->getEditableConfig('contact.settings');
    $this->contactSettings = $config->getData();
    $config->set('flood.limit', 100000);
    $config->set('flood.interval', 100000);
    $config->save();
  }

  /**
   * Restore contact form flooding settings.
   *
   * @AfterScenario @no_contact_flood
   */
  public function afterScenarioNoContactFlood(AfterScenarioScope $scope) {
    $config = $this->getCore()->getEditableConfig('contact.settings');
    $config->setData($this->contactSettings)->save();
  }

  /**
   * Get collected emails.
   *
   * @return array
   *   Array of collected emails.
   */
  protected function getCollectedEmails() {
    $this->getCore()->state()->resetCache();
    $test_mail_collector = $this->getCore()->state()->get('system.test_mail_collector');
    if (!$test_mail_collector) {
      $test_mail_collector = [];
    }

    return $test_mail_collector;
  }

  /**
   * Get last sent email.
   *
   * @return string
   *   Last sent email.
   *
   * @throws \Exception
   */
  protected function getLastEmail() {
    $emails = $this->getCollectedEmails();
    if (!$emails) {
      throw new \Exception('No mail was sent.');
    }

    return end($emails);
  }

}
