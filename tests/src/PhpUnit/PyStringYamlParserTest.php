<?php

namespace NuvoleWeb\Drupal\Tests\PhpUnit;

use Behat\Gherkin\Node\PyStringNode;
use NuvoleWeb\Drupal\DrupalExtension\Component\PyStringYamlParser;

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
    $this->assertArrayHasKey('mail', $result);
    $this->assertArrayHasKey('slogan', $result);
    $this->assertArrayHasKey('page', $result);
    $this->assertArrayHasKey('admin_compact_mode', $result);
    $this->assertArrayHasKey('weight_select_max', $result);
    $this->assertArrayHasKey('weight_select_max', $result);
    $this->assertArrayHasKey('langcode', $result);
    $this->assertArrayHasKey('default_langcode', $result);

    $this->assertArrayHasKey('403', $result['page']);
    $this->assertArrayHasKey('404', $result['page']);
    $this->assertArrayHasKey('front', $result['page']);
  }

}
