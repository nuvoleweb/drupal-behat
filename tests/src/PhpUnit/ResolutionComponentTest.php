<?php

namespace NuvoleWeb\Drupal\Tests\PhpUnit;

use NuvoleWeb\Drupal\DrupalExtension\Component\ResolutionComponent;

/**
 * Class ResolutionComponentTest.
 *
 * @coversDefaultClass \NuvoleWeb\Drupal\DrupalExtension\Component\ResolutionComponent
 */
class ResolutionComponentTest extends AbstractTest {

  /**
   * Test resolution parsing.
   *
   * @covers ::parse
   * @covers ::getWidth
   * @covers ::getHeight
   */
  public function testParse() {
    $resolution = new ResolutionComponent();
    $resolution->parse('360x640');
    $this->assertEquals('360', $resolution->getWidth());
    $this->assertEquals('640', $resolution->getHeight());
  }

  /**
   * Test invalid resolution.
   *
   * @expectedException \bovigo\assert\AssertionFailure
   * @covers ::parse
   * @covers ::getWidth
   * @covers ::getHeight
   */
  public function testInvalidResolution() {
    $resolution = new ResolutionComponent();
    $resolution->parse('absdx123');
  }

}
