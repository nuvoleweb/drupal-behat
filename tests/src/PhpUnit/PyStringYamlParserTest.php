<?php

namespace NuvoleWeb\Drupal\Tests\PhpUnit;

use Behat\Gherkin\Node\PyStringNode;
use NuvoleWeb\Drupal\DrupalExtension\Component\PyStringYamlParser;
use Webmozart\Assert\Assert;

/**
 * Class PyStringYamlParserTest.
 *
 * @coversDefaultClass \NuvoleWeb\Drupal\DrupalExtension\Component\PyStringYamlParser
 */
class PyStringYamlParserTest extends AbstractTest {

  /**
   * Test parsing.
   *
   * @covers ::parse
   */
  public function testParse() {
    $yaml = <<<YAML
      mail: info@example.com
      slogan: ''
      page:
        403: ''
        404: ''
        front: /node
      admin_compact_mode: false
      weight_select_max: 100
      langcode: en
      default_langcode: en
YAML;
    $node = new PyStringNode(explode(PHP_EOL, $yaml), 0);
    $parser = new PyStringYamlParser();
    $result = $parser->parse($node);
    Assert::keyExists($result, 'mail');
    Assert::keyExists($result, 'slogan');
    Assert::keyExists($result, 'page');
    Assert::keyExists($result, 'admin_compact_mode');
    Assert::keyExists($result, 'weight_select_max');
    Assert::keyExists($result, 'weight_select_max');
    Assert::keyExists($result, 'langcode');
    Assert::keyExists($result, 'default_langcode');

    Assert::keyExists($result['page'], '403');
    Assert::keyExists($result['page'], '404');
    Assert::keyExists($result['page'], 'front');
  }

}
