<?php

namespace NuvoleWeb\Drupal\DrupalExtension\Context;

use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\ExpectationException;

/**
 * Select field context to work with select fields.
 */
class SelectFieldContext extends RawMinkContext {

  /**
   * Checks that the given select field has the options listed in the table.
   *
   * @Then I should have the following options for :select:
   */
  public function assertSelectOptions($select, TableNode $options) {
    // Retrieve the specified field.
    if (!$field = $this->getSession()->getPage()->findField($select)) {
      throw new ExpectationException("Field '$select' not found.", $this->getSession());
    }

    /** @var \Behat\Mink\Element\NodeElement $field */

    // Check that the specified field is a <select> field.
    $this->assertElementType($field, 'select');

    // Retrieve the options table from the test scenario and flatten it.
    $expected_options = $options->getColumnsHash();
    array_walk($expected_options, function (&$value) {
      $value = reset($value);
    });

    // Retrieve the actual options that are shown in the page.
    $actual_options = $field->findAll('css', 'option');

    // Convert into a flat list of option text strings.
    array_walk($actual_options, function (NodeElement &$value) {
      $value = $value->getText();
    });

    // Check that all expected options are present.
    foreach ($expected_options as $expected_option) {
      if (!in_array($expected_option, $actual_options)) {
        throw new ExpectationException("Option '$expected_option' is missing from select list '$select'.", $this->getSession());
      }
    }
  }

  /**
   * Checks that the given select field doesn't have the listed options.
   *
   * @Then I should not have the following options for :select:
   */
  public function assertNoSelectOptions($select, TableNode $options) {
    // Retrieve the specified field.
    if (!$field = $this->getSession()->getPage()->findField($select)) {
      throw new ExpectationException("Field '$select' not found.", $this->getSession());
    }

    /** @var \Behat\Mink\Element\NodeElement $field */

    // Check that the specified field is a <select> field.
    $this->assertElementType($field, 'select');

    // Retrieve the options table from the test scenario and flatten it.
    $expected_options = $options->getColumnsHash();
    array_walk($expected_options, function (&$value) {
      $value = reset($value);
    });

    // Retrieve the actual options that are shown in the page.
    $actual_options = $field->findAll('css', 'option');

    // Convert into a flat list of option text strings.
    array_walk($actual_options, function (NodeElement &$value) {
      $value = $value->getText();
    });

    // Check that none of the expected options are present.
    foreach ($expected_options as $expected_option) {
      if (in_array($expected_option, $actual_options)) {
        throw new ExpectationException("Option '$expected_option' is unexpectedly found in select list '$select'.", $this->getSession());
      }
    }
  }

  /**
   * Assert that given option field is selected.
   *
   * @Then the option :option_label from select :select is selected
   */
  public function theOptionFromIsSelected($select, $option_label) {
    $this->getSelectedOptionByLabel($select, $option_label);
  }

  /**
   * Assert that given option field is not selected.
   *
   * @Then the option :option_label from select :select is not selected
   */
  public function theOptionFromIsNotSelected($select, $option_label) {
    $this->getSelectedOptionByLabel($select, $option_label, FALSE);
  }

  /**
   * Returns an option from a specific select.
   *
   * @param string $select
   *   The select name.
   * @param string $option_label
   *   The option text.
   * @param bool $check_selected
   *   (optional) If TRUE, will check for selected option, if FALSE for
   *   unselected. Defaults to TRUE.
   *
   * @return \Behat\Mink\Element\NodeElement|null
   *   The node element or NULL.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  protected function getSelectedOptionByLabel($select, $option_label, $check_selected = TRUE) {
    if (!$field = $this->getSession()->getPage()->findField($select)) {
      throw new ExpectationException("Field '$select' not found.", $this->getSession());
    }

    /** @var \Behat\Mink\Element\NodeElement $field */

    // Check that the specified field is a <select> field.
    $this->assertElementType($field, 'select');

    $options = $field->findAll('css', 'option');
    $options = array_filter($options, function (NodeElement $option) use ($option_label) {
      return $option->getText() == $option_label ? $option : FALSE;
    });

    if (!($option = $options ? reset($options) : NULL)) {
      throw new ExpectationException("Option '$option_label' doesn't exist in '$select' select.", $this->getSession());
    }

    return $check_selected ? $option->isSelected() : !$option->isSelected();
  }

}
