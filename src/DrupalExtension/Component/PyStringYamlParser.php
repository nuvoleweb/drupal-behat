<?php

namespace NuvoleWeb\Drupal\DrupalExtension\Component;

use Behat\Gherkin\Node\PyStringNode;
use Symfony\Component\Yaml\Yaml;

/**
 * Class PyStringYamlParser.
 *
 * @package NuvoleWeb\Drupal\DrupalExtension\Component
 */
class PyStringYamlParser {

  /**
   * Parse YAML contained in a PyString node.
   *
   * @param \Behat\Gherkin\Node\PyStringNode $node
   *   PyString containing text in YAML format.
   *
   * @return array
   *   Parsed YAML.
   */
  public function parse(PyStringNode $node) {
    // Sanitize PyString test by removing initial indentation spaces.
    $strings = $node->getStrings();
    if ($strings) {
      preg_match('/^(\s+)/', $strings[0], $matches);
      $indentation_size = isset($matches[1]) ? strlen($matches[1]) : 0;
      foreach ($strings as $key => $string) {
        $strings[$key] = substr($string, $indentation_size);
      }
    }
    $raw = implode("\n", $strings);
    return Yaml::parse($raw);
  }

}
