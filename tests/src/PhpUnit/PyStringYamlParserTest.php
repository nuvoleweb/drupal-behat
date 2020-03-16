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

    static::assertArrayHasKey('mail', $result);
    static::assertArrayHasKey('slogan', $result);
    static::assertArrayHasKey('page', $result);
    static::assertArrayHasKey('admin_compact_mode', $result);
    static::assertArrayHasKey('weight_select_max', $result);
    static::assertArrayHasKey('weight_select_max', $result);
    static::assertArrayHasKey('langcode', $result);
    static::assertArrayHasKey('default_langcode', $result);

    static::assertArrayHasKey('403', $result['page']);
    static::assertArrayHasKey('404', $result['page']);
    static::assertArrayHasKey('front', $result['page']);
  }

}
