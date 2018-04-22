<?php

namespace NuvoleWeb\Drupal\Tests\PhpUnit;

use function \bovigo\assert\assert;
use function \bovigo\assert\predicate\hasKey;
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
    assert($result, hasKey('mail')
      ->and(hasKey('slogan'))
      ->and(hasKey('page'))
      ->and(hasKey('admin_compact_mode'))
      ->and(hasKey('weight_select_max'))
      ->and(hasKey('weight_select_max'))
      ->and(hasKey('langcode'))
      ->and(hasKey('default_langcode')));
    assert($result['page'], hasKey('403')
      ->and(hasKey('404'))
      ->and(hasKey('front')));
  }

}
