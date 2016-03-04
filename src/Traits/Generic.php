<?php
/**
 * @file
 * Contains trait class.
 */

namespace NuvoleWeb\Drupal\Behat\Traits;

use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Element\NodeElement;
use Behat\Mink\Element\DocumentElement;
use Behat\Mink\Exception\ExpectationException;
use Behat\Behat\Context\Environment\InitializedContextEnvironment;
use Behat\Behat\Hook\Scope\AfterStepScope;
use Behat\Mink\Exception\UnsupportedDriverActionException;
use Behat\Mink\Exception\DriverException;
use Drupal\Component\Render\FormattableMarkup;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;

/**
 * Trait Generic.
 *
 * @package Nuvole\Drupal\Behat\Traits
 */
trait Generic {

  /**
   * @Given I visit the :type content :title
   */
  public function iAmViewingTheContent($type, $title) {

    $result = \Drupal::entityQuery('node')
      ->condition('type', $type)
      ->condition('title', $title)
      ->execute();

    if (!empty($result)) {
      $nid = array_shift($result);
      $this->visitPath("node/$nid");
    }
    else {
      throw new ExpectationException("No node with type '$type' and title '$title' has been found.", $this->getSession());
    }
  }

  /**
   * @Given I edit the :type content :title
   */
  public function iAmEditingTheContent($type, $title) {

    $result = \Drupal::entityQuery('node')
      ->condition('type', $type)
      ->condition('title', $title)
      ->execute();

    if (!empty($result)) {
      $nid = array_shift($result);
      $this->visitPath("node/$nid/edit");
    }
    else {
      throw new ExpectationException("No node with type '$type' and title '$title' has been found.", $this->getSession());
    }
  }

  /**
   * @Then I should get an access denied error
   */
  public function assertAccessDenied() {
    $this->assertSession()->statusCodeEquals(403);
  }

  /**
   * @Then I wait :seconds seconds
   */
  public function iWaitSeconds($seconds) {
    sleep((int) $seconds);
  }

  /**
   * @Then :name can :op content :content
   *
   * @throws \Exception
   *   If the user can not edit the node.
   */
  public function userCanContent($name, $op, $content) {

    $op = strtr($op, array('edit' => 'update'));
    $node = $this->loadNodeByName($content);
    $account = user_load_by_name($name);
    $access = $node->access($op, $account);

    if (!$access) {
      $params = array(
        '@name' => $name,
        '@op' => $op,
        '@content' => $content,
      );
      throw new \Exception(format_string("@name can not @op @content.", $params));
    }
  }

  /**
   * @Then :name can not :op content :content
   *
   * @throws \Exception
   *   If the user can edit the node.
   */
  public function userCanNotContent($name, $op, $content) {
    $op = strtr($op, array('edit' => 'update'));
    $node = $this->loadNodeByName($content);
    $account = user_load_by_name($name);
    $access = $node->access($op, $account);

    if ($access) {
      $params = array(
        '@name' => $name,
        '@op' => $op,
        '@content' => $content,
      );
      throw new \Exception(format_string("@name can @op @content but should not.", $params));
    }
  }

  /**
   * @Then I should see the link :link to edit content :content
   */
  public function assertContentEditLink($link, $content) {
    if (!$this->getContentEditLink($link, $content)) {
      throw new ExpectationException("No '$link' link to edit '$content' has been found.", $this->getSession());
    }
  }

  /**
   * @Then I should not see a link to edit content :content
   */
  public function assertNoContentEditLink($content) {
    if ($this->getContentEditLink(NULL, $content)) {
      throw new ExpectationException("link to edit '$content' has been found.", $this->getSession());
    }
  }

  /**
   * @Then I should see in the header :header::value
   */
  public function iShouldSeeInTheHeader($header, $value)
  {
    $headers = $this->getSession()->getResponseHeaders();
    if ($headers[$header] != $value) {
      throw new \Exception(sprintf("Did not see %s with value %s.", $header, $value));
    }
  }

  /**
   * Creates content of a given type provided in the form:
   * | Title    | Author     | Label | of the field      |
   * | My title | Joe Editor | 1     | 2014-10-17 8:00am |
   * | ...      | ...        | ...   | ...               |
   *
   * Requires DrupalContext::assertLoggedInByName()
   *
   * @Given :user created :type content:
   */
  public function manuallyCreateNodes($user, $type, TableNode $nodesTable) {
    // Log in with the user.
    $this->assertLoggedInByName($user);
    foreach ($nodesTable->getHash() as $nodeHash) {
      $this->getSession()->visit($this->locatePath("/node/add/$type"));
      $element = $this->getSession()->getPage();
      // Fill in the form.
      foreach ($nodeHash as $field => $value) {
        $element->fillField($field, $value);
      }
      $submit = $element->findButton('edit-submit');
      if (empty($submit)) {
        throw new \Exception(sprintf("No submit button at %s", $this->getSession()->getCurrentUrl()));
      }
      // Submit the form.
      $submit->click();
    }

  }

  /**
   * Checks that the given element is of the given type.
   *
   * @param NodeElement $element
   *   The element to check.
   * @param string $type
   *   The expected type.
   *
   * @throws ExpectationException
   *   Thrown when the given element is not of the expected type.
   */
  public function assertElementType(NodeElement $element, $type) {
    if ($element->getTagName() !== $type) {
      throw new ExpectationException("The element is not a '$type'' field.", $this->getSession());
    }
  }

  /**
   * Loads a node by name.
   *
   * @param string $title
   *   The title of the node to load.
   *
   * @return Node
   *   The loaded node.
   *
   * @throws \Exception
   *   Thrown when no node with the given title can be loaded.
   */
  public function loadNodeByName($title) {

    $result = \Drupal::entityQuery('node')
      ->condition('title', $title)
      ->condition('status', NODE_PUBLISHED)
      ->range(0, 1)
      ->execute();

    if (empty($result)) {
      $params = array(
        '@title' => $title,
      );
      throw new \Exception(new FormattableMarkup("Node @title not found.", $params));
    }

    $nid = current($result);
    return Node::load($nid);
  }

  /**
   *  Get the edit link for a node.
   *
   * @param $link
   *   The link name.
   * @param $content
   *   The name of the node.
   *
   * @return \Behat\Mink\Element\NodeElement|null
   *   The link if found.
   * @throws \Exception
   */
  public function getContentEditLink($link, $content) {
    $node = $this->loadNodeByName($content);

    /** @var DocumentElement $element */
    $element = $this->getSession()->getPage();

    $locator = ($link ? array('link', sprintf("'%s'", $link)) : array('link', "."));

    /** @var NodeElement[] $links */
    $links = $element->findAll('named', $locator);

    // Loop over all the links on the page and check for the node edit path.
    foreach ($links as $result) {
      $target = $result->getAttribute('href');
      if (strpos($target,'node/' . $node->id() . '/edit') !== false) {
        return $result;
      }
    }
    return NULL;
  }

  /**
   * @BeforeStep
   *
   * Resize the browser window to some desktop size.
   */
  public function beforeStep() {
    // @TODO: make this conditional.
    try {
      // We make sure the the PhantonJS browser uses the desktop version.
      $this->getSession()->resizeWindow(1024, 768, 'current');
    } catch (UnsupportedDriverActionException $e) { }
  }

}
