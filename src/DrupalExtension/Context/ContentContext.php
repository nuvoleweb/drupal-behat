<?php

namespace NuvoleWeb\Drupal\DrupalExtension\Context;

use Behat\Mink\Exception\ExpectationException;
use Behat\Gherkin\Node\PyStringNode;
use Webmozart\Assert\Assert;

/**
 * Class ContentContext.
 *
 * @package NuvoleWeb\Drupal\DrupalExtension\Context
 */
class ContentContext extends RawDrupalContext {

  /**
   * Assert viewing content given its type and title.
   *
   * @param string $type
   *   Content type machine name.
   * @param string $title
   *   Content title.
   *
   * @Given I am visiting the :type content :title
   * @Given I visit the :type content :title
   */
  public function iAmViewingTheContent($type, $title) {
    $this->visitContentPage('view', $type, $title);
  }

  /**
   * Assert editing content given its type and title.
   *
   * @param string $type
   *   Content type machine name.
   * @param string $title
   *   Content title.
   *
   * @Given I am editing the :type content :title
   * @Given I edit the :type content :title
   */
  public function iAmEditingTheContent($type, $title) {
    $this->visitContentPage('edit', $type, $title);
  }

  /**
   * Assert deleting content given its type and title.
   *
   * @param string $type
   *   Content type machine name.
   * @param string $title
   *   Content title.
   *
   * @Given I am deleting the :type content :title
   * @Given I delete the :type content :title
   */
  public function iAmDeletingTheContent($type, $title) {
    $this->visitContentPage('delete', $type, $title);
  }

  /**
   * Provides a common step definition callback for node pages.
   *
   * @param string $op
   *   The operation being performed: 'view', 'edit', 'delete'.
   * @param string $type
   *   The node type either as id or as label.
   * @param string $title
   *   The node title.
   */
  protected function visitContentPage($op, $type, $title) {
    $nid = $this->getCore()->getEntityIdByLabel('node', $type, $title);
    $path = [
      'view' => "node/$nid",
      'edit' => "node/$nid/edit",
      'delete' => "node/$nid/delete",
    ];
    $this->visitPath($path[$op]);
  }

  /**
   * Assert that given user can perform given operation on given content.
   *
   * @param string $name
   *   User name.
   * @param string $op
   *   Operation: view, edit or delete.
   * @param string $title
   *   Content title.
   *
   * @throws \Exception
   *   If user cannot perform given operation on given content.
   *
   * @Then :name can :op content :content
   * @Then :name can :op :content content
   */
  public function userCanContent($name, $op, $title) {
    $op = strtr($op, ['edit' => 'update']);
    $node = $this->getCore()->loadNodeByName($title);
    $access = $this->getCore()->nodeAccess($op, $name, $node);
    if (!$access) {
      throw new \Exception("{$name} cannot {$op} '{$title}' but it is supposed to.");
    }
  }

  /**
   * Assert that given user cannot perform given operation on given content.
   *
   * @param string $name
   *   User name.
   * @param string $op
   *   Operation: view, edit or delete.
   * @param string $title
   *   Content title.
   *
   * @throws \Exception
   *   If user can perform given operation on given content.
   *
   * @Then :name can not :op content :content
   * @Then :name cannot :op content :content
   * @Then :name cannot :op :content content
   */
  public function userCanNotContent($name, $op, $title) {
    $op = strtr($op, ['edit' => 'update']);
    $node = $this->getCore()->loadNodeByName($title);
    $access = $this->getCore()->nodeAccess($op, $name, $node);
    if ($access) {
      throw new \Exception("{$name} can {$op} '{$title}' but it is not supposed to.");
    }
  }

  /**
   * Assert presence of content edit link given its name and content title.
   *
   * @param string $link
   *   Link "name" HTML attribute.
   * @param string $title
   *   Content title.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   *    If no edit link for given content has been found.
   *
   * @Then I should see the link :link to edit content :content
   * @Then I should see a link :link to edit content :content
   */
  public function assertContentEditLink($link, $title) {
    if (!$this->getContentEditLink($link, $title)) {
      throw new ExpectationException("No '$link' link to edit '$title' has been found.", $this->getSession());
    }
  }

  /**
   * Assert absence of content edit link given its content title.
   *
   * @param string $title
   *   Content title.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   *    If edit link for given content has been found.
   *
   * @Then I should not see a link to edit content :content
   * @Then I should not see the link to edit content :content
   */
  public function assertNoContentEditLink($title) {
    if ($this->getContentEditLink(NULL, $title)) {
      throw new ExpectationException("link to edit '$title' has been found.", $this->getSession());
    }
  }

  /**
   * Create content defined in YAML format.
   *
   * @param \Behat\Gherkin\Node\PyStringNode $string
   *   The text in yaml format that represents the content.
   *
   * @Given the following content:
   */
  public function assertContent(PyStringNode $string) {
    $values = $this->getYamlParser()->parse($string);
    $message = __METHOD__ . ": Required fields 'type', 'title' and 'langcode' not found.";
    Assert::keyExists($values, 'type', $message);
    Assert::keyExists($values, 'title', $message);
    Assert::keyExists($values, 'langcode', $message);
    $node = $this->getCore()->entityCreate('node', $values);
    $this->nodes[] = $node;
  }

  /**
   * Assert translation for given content.
   *
   * @param string $content_type
   *   The node type for which to add the translation.
   * @param string $title
   *   The title to identify the content by.
   * @param \Behat\Gherkin\Node\PyStringNode $string
   *   The text in yaml format that represents the translation.
   *
   * @Given the following translation for :content_type content :title:
   */
  public function assertTranslation($content_type, $title, PyStringNode $string) {
    $values = $this->getYamlParser()->parse($string);
    Assert::keyExists($values, 'langcode', __METHOD__ . ": Required field 'langcode' not found.");

    $nid = $this->getCore()->getEntityIdByLabel('node', $content_type, $title);
    $source = $this->getCore()->entityLoad('node', $nid);

    $this->getCore()->entityAddTranslation($source, $values['langcode'], $values);
  }

  /**
   * Get the edit link for a node.
   *
   * @param string $link
   *   The link name.
   * @param string $title
   *   The node title.
   *
   * @return \Behat\Mink\Element\NodeElement|null
   *   The link if found.
   *
   * @throws \Exception
   */
  protected function getContentEditLink($link, $title) {
    $node = $this->getCore()->loadNodeByName($title);

    /** @var \Behat\Mink\Element\DocumentElement $element */
    $element = $this->getSession()->getPage();

    $locator = ($link ? array('link', sprintf("'%s'", $link)) : array('link', "."));

    /** @var \Behat\Mink\Element\NodeElement[] $links */
    $links = $element->findAll('named', $locator);

    // Loop over all the links on the page and check for the node edit path.
    foreach ($links as $result) {
      $target = $result->getAttribute('href');
      if (strpos($target, 'node/' . $this->getCore()->getNodeId($node) . '/edit') !== FALSE) {
        return $result;
      }
    }
    return NULL;
  }

  /**
   * Get the yaml parser from the behat container.
   *
   * @return \NuvoleWeb\Drupal\DrupalExtension\Component\PyStringYamlParser
   *   The parser.
   */
  protected function getYamlParser() {
    return $this->getContainer()->get('drupal.behat.component.py_string_yaml_parser');
  }

}
