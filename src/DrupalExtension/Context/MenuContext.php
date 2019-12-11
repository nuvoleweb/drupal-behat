<?php

namespace NuvoleWeb\Drupal\DrupalExtension\Context;

use Behat\Gherkin\Node\TableNode;
use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Mink\Exception\ExpectationException;

/**
 * Class MenuContext.
 *
 * @package NuvoleWeb\Drupal\DrupalExtension\Context
 */
class MenuContext extends RawDrupalContext {


  /**
   * Menu links created during test execution.
   *
   * @var \Drupal\menu_link_content\Entity\MenuLinkContent[]
   */
  private $menuLinks = [];

  /**
   * Create menu structure for nodes.
   *
   * @param string $menu_name
   *   Menu machine name.
   * @param \Behat\Gherkin\Node\TableNode $table
   *   Table representing the menu structure to be specified as follows:
   *      | title  | parent |
   *      | Page 1 |        |
   *      | Page 2 | Page 1 |
   *      | Page 3 | Page 2 |.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   *    Throws exception if menu not found.
   *
   * @Given the following :menu_name menu structure for content:
   */
  public function assertMenuStructureForContent($menu_name, TableNode $table) {
    $menu_items = $table->getColumnsHash();
    foreach ($menu_items as $key => $menu_item) {
      $node = $this->getCore()->loadNodeByName($menu_item['title']);
      $menu_items[$key]['uri'] = "entity:node/{$this->getCore()->getNodeId($node)}";
    }

    try {
      $this->menuLinks = array_merge($this->menuLinks, $this->getCore()->createMenuStructure($menu_name, $menu_items));
    }
    catch (\InvalidArgumentException $e) {
      throw new ExpectationException($e->getMessage(), $this->getSession());
    }
  }

  /**
   * Create menu structure my adding menu links.
   *
   * @param string $menu_name
   *   Menu machine name.
   * @param \Behat\Gherkin\Node\TableNode $table
   *   Table representing the menu structure to be specified as follows:
   *       | title   | uri        | parent |
   *       | Link 1  | internal:/ |        |
   *       | Link 2  | internal:/ | Link 1 |
   *       | Link 3  | internal:/ | Link 1 |.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   *    Throws exception if menu not found.
   *
   * @Given the following :menu_name menu structure:
   */
  public function assertMenuStructure($menu_name, TableNode $table) {
    try {
      $this->menuLinks = array_merge($this->menuLinks, $this->getCore()->createMenuStructure($menu_name, $table->getColumnsHash()));
    }
    catch (\InvalidArgumentException $e) {
      throw new ExpectationException($e->getMessage(), $this->getSession());
    }

  }

  /**
   * Assert clean Watchdog after every step.
   *
   * @param \Behat\Behat\Hook\Scope\AfterScenarioScope $event
   *   Event object.
   *
   * @AfterScenario
   */
  public function deleteMenuLinks(AfterScenarioScope $event) {
    if ($this->menuLinks) {
      foreach ($this->menuLinks as $menu_link) {
        $this->getCore()->entityDelete($menu_link->getEntityTypeId(), $menu_link);
      }

      $this->getCore()->clearMenuCache();
    }
  }

}
