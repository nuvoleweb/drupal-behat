<?php

namespace NuvoleWeb\Drupal\DrupalExtension\Traits;

use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\ExpectationException;
use Behat\Mink\Exception\UnsupportedDriverActionException;

/**
 * Trait Generic.
 *
 * @package Nuvole\Drupal\Behat\Traits
 */
trait Generic {

  /**
   * Default screen size.
   */
  protected $defaultScreenSize = ['width' => 1024, 'height' => 768];

  /**
   * Screen size in use.
   */
  protected $screenSize = ['width' => 1024, 'height' => 768];

  /**
   * Assert viewing content given its type and title.
   *
   * @param string $type
   *    Content type machine name.
   * @param string $title
   *    Content title.
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
   *    Content type machine name.
   * @param string $title
   *    Content title.
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
   *    Content type machine name.
   * @param string $title
   *    Content title.
   *
   * @Given I am deleting the :type content :title
   * @Given I delete the :type content :title
   */
  public function iAmDeletingTheContent($type, $title) {
    $this->visitContentPage('delete', $type, $title);
  }

  /**
   * Assert that given user can perform given operation on given content.
   *
   * @param string $name
   *    User name.
   * @param string $op
   *    Operation: view, edit or delete.
   * @param string $title
   *    Content title.
   *
   * @throws \Exception
   *   If user cannot perform given operation on given content.
   *
   * @Then :name can :op content :content
   */
  public function userCanContent($name, $op, $title) {

    $op = strtr($op, array('edit' => 'update'));
    $node = $this->loadNodeByName($title);
    $account = user_load_by_name($name);
    $access = $node->access($op, $account);

    if (!$access) {
      $params = array(
        '@name' => $name,
        '@op' => $op,
        '@content' => $title,
      );
      throw new \Exception(format_string("@name can not @op @content.", $params));
    }
  }

  /**
   * Assert that given user cannot perform given operation on given content.
   *
   * @param string $name
   *    User name.
   * @param string $op
   *    Operation: view, edit or delete.
   * @param string $title
   *    Content title.
   *
   * @throws \Exception
   *   If user can perform given operation on given content.
   *
   * @Then :name can not :op content :content
   * @Then :name cannot :op content :content
   */
  public function userCanNotContent($name, $op, $title) {
    $op = strtr($op, array('edit' => 'update'));
    $node = $this->loadNodeByName($title);
    $account = user_load_by_name($name);
    $access = $node->access($op, $account);

    if ($access) {
      $params = array(
        '@name' => $name,
        '@op' => $op,
        '@content' => $title,
      );
      throw new \Exception(format_string("@name can @op @content but should not.", $params));
    }
  }

  /**
   * Assert presence of content edit link given its name and content title.
   *
   * @param string $link
   *    Link "name" HTML attribute.
   * @param string $title
   *    Content title.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   *    If no edit link for given content has been found.
   *
   * @Then I should see the link :link to edit content :content
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
   *    Content title.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   *    If edit link for given content has been found.
   *
   * @Then I should not see a link to edit content :content
   */
  public function assertNoContentEditLink($title) {
    if ($this->getContentEditLink(NULL, $title)) {
      throw new ExpectationException("link to edit '$title' has been found.", $this->getSession());
    }
  }

  /**
   * Assert string in HTTP response header.
   *
   * @Then I should see in the header :header::value
   */
  public function iShouldSeeInTheHeader($header, $value) {
    $headers = $this->getSession()->getResponseHeaders();
    if ($headers[$header] != $value) {
      throw new \Exception(sprintf("Did not see %s with value %s.", $header, $value));
    }
  }

  /**
   * Creates content by filling specified form fields.
   *
   * Use as follow:
   *
   *  | Title    | Author     | Label | of the field      |
   *  | My title | Joe Editor | 1     | 2014-10-17 8:00am |
   *  | ...      | ...        | ...   | ...               |
   *
   * Requires DrupalContext::assertLoggedInByName()
   *
   * @see DrupalContext::assertLoggedInByName()
   *
   * @Given :user created :type content:
   */
  public function manuallyCreateNodes($user, $type, TableNode $nodesTable) {
    $type = $this->convertLabelToNodeTypeId($type);

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
  public function getContentEditLink($link, $title) {
    $node = $this->loadNodeByName($title);

    /** @var DocumentElement $element */
    $element = $this->getSession()->getPage();

    $locator = ($link ? array('link', sprintf("'%s'", $link)) : array('link', "."));

    /** @var NodeElement[] $links */
    $links = $element->findAll('named', $locator);

    // Loop over all the links on the page and check for the node edit path.
    foreach ($links as $result) {
      $target = $result->getAttribute('href');
      if (strpos($target, 'node/' . $node->id() . '/edit') !== FALSE) {
        return $result;
      }
    }
    return NULL;
  }

  /**
   * Set browser size to mobile.
   *
   * @BeforeScenario @javascript&&@mobile
   */
  public function beforeMobileScenario() {
    $this->screenSize = ['width' => 450, 'height' => 768];
  }

  /**
   * Reset browser size.
   *
   * @AfterScenario @javascript
   */
  public function afterJavascriptScenario() {
    $this->screenSize = $this->defaultScreenSize;
  }

  /**
   * Resize the browser window.
   *
   * @BeforeStep
   */
  public function adjustScreenSizeBeforeStep() {
    try {
      // We make sure all selenium drivers use the same screen size.
      $this->getSession()->resizeWindow($this->screenSize['width'], $this->screenSize['height'], 'current');
    }
    catch (UnsupportedDriverActionException $e) {
    }
  }

  /**
   * Assert presence of given field on the page.
   *
   * @Then I should see the field :field
   */
  public function iShouldSeeTheField($field) {
    $element = $this->getSession()->getPage();
    $result = $element->findField($field);
    try {
      if ($result && !$result->isVisible()) {
        throw new \Exception(sprintf("No field '%s' on the page %s", $field, $this->getSession()->getCurrentUrl()));
      }
    }
    catch (UnsupportedDriverActionException $e) {
      // We catch the UnsupportedDriverActionException exception in case
      // this step is not being performed by a driver that supports javascript.
      // All other exceptions are valid.
    }
    if (empty($result)) {
      throw new \Exception(sprintf("No field '%s' on the page %s", $field, $this->getSession()->getCurrentUrl()));
    }
  }

  /**
   * Assert absence of given field.
   *
   * @Then I should not see the field :field
   */
  public function iShouldNotSeeTheField($field) {
    $element = $this->getSession()->getPage();
    $result = $element->findField($field);
    try {
      if ($result && $result->isVisible()) {
        throw new \Exception(sprintf("The field '%s' was present on the page %s and was not supposed to be", $field, $this->getSession()->getCurrentUrl()));
      }
    }
    catch (UnsupportedDriverActionException $e) {
      // We catch the UnsupportedDriverActionException exception in case
      // this step is not being performed by a driver that supports javascript.
      // All other exceptions are valid.
      if ($result) {
        throw new \Exception(sprintf("The field '%s' was present on the page %s and was not supposed to be", $field, $this->getSession()->getCurrentUrl()));
      }
    }
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
   *
   * @throws ExpectationException
   *   When the node does not exist.
   */
  protected function visitContentPage($op, $type, $title) {
    $type = $this->convertLabelToNodeTypeId($type);
    $result = \Drupal::entityQuery('node')
      ->condition('type', $type)
      ->condition('title', $title)
      ->execute();

    if (!empty($result)) {
      $nid = array_shift($result);
      $path = [
        'view' => "node/$nid",
        'edit' => "node/$nid/edit",
        'delete' => "node/$nid/delete",
      ];
      $this->visitPath($path[$op]);
    }
    else {
      throw new ExpectationException("No node with type '$type' and title '$title' has been found.", $this->getSession());
    }
  }

  /**
   * Visit taxonomy term page given its type and name.
   *
   * @Given I am visiting the :type term :title
   * @Given I visit the :type term :title
   */
  public function iAmViewingTheTerm($type, $title) {
    $this->visitTermPage('view', $type, $title);
  }

  /**
   * Visit taxonomy term edit page given its type and name.
   *
   * @Given I am editing the :type term :title
   * @Given I edit the :type term :title
   */
  public function iAmEditingTheTerm($type, $title) {
    $this->visitTermPage('edit', $type, $title);
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
   *
   * @throws ExpectationException
   *   When the node does not exist.
   */
  protected function visitTermPage($op, $type, $title) {
    $type = $this->convertLabelToTermTypeId($type);
    $result = \Drupal::entityQuery('taxonomy_term')
      ->condition('vid', $type)
      ->condition('name', $title)
      ->execute();

    if (!empty($result)) {
      $tid = array_shift($result);
      $path = [
        'view' => "taxonomy/term/$tid",
        'edit' => "taxonomy/term/$tid/edit",
        'delete' => "taxonomy/term/$tid/delete",
      ];
      $this->visitPath($path[$op]);
    }
    else {
      throw new ExpectationException("No term with vocabulary '$type' and title '$title' has been found.", $this->getSession());
    }
  }

  /**
   * Assert first element precedes second one.
   *
   * @Then :first should precede :second
   */
  public function shouldPrecede($first, $second) {
    $page = $this->getSession()->getPage()->getText();
    $pos1 = strpos($page, $first);
    if ($pos1 === FALSE) {
      throw new ExpectationException("Text not found: '$first'.", $this->getSession());
    }
    $pos2 = strpos($page, $second);
    if ($pos2 === FALSE) {
      throw new ExpectationException("Text not found: '$second'.", $this->getSession());
    }
    if ($pos2 < $pos1) {
      throw new \Exception("Text '$first' does not precede text '$second'.");
    }
  }

}
