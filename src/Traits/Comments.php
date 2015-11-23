<?php
/**
 * @file
 * Contains trait class.
 */

namespace NuvoleWeb\Drupal\Behat\Traits;

use Behat\Gherkin\Node\TableNode;

/**
 * Trait OrganicGroups.
 *
 * @package Nuvole\Drupal\Behat\Traits
 */
trait Comments {

  /**
   * The comments which are added in the scenario.
   *
   * @var array
   */
  protected $comments = array();

  /**
   * Creates comments provided in the form:
   * | comment  | author     | node |
   * | My title | Joe Editor | Post |
   * | ...      | ...        | ...  |
   *
   * @Given comments:
   */
  public function createComments(TableNode $commentsTable) {
    foreach ($commentsTable->getHash() as $commentHash) {
      $comment = (object) $commentHash;
      $this->commentCreate($comment);
    }
  }


  /**
   * Create a comment.
   *
   * @return object
   *   The created comment.
   */
  public function commentCreate($comment) {

    $this->parseEntityFields('comment', $comment);

    // Assign authorship if none exists and `author` is passed.
    if (!isset($comment->uid) && !empty($comment->author) && ($user = user_load_by_name($comment->author))) {
      $comment->uid = $user->uid;
    }
    if (!isset($comment->nid) && isset($comment->node)) {
      $node = $this->loadNodeByName($comment->node);
      $comment->nid = $node->nid;
    }
    if (!isset($comment->cid)) {
      $comment->cid = NULL;
    }
    if (!isset($comment->pid)) {
      $comment->pid = NULL;
    }
    if (!isset($comment->status)) {
      $comment->status = user_access('skip comment approval', user_load($comment->uid)) ? COMMENT_PUBLISHED : COMMENT_NOT_PUBLISHED;
    }
    if (!isset($comment->comment_body) && isset($comment->comment)) {
      $comment->comment_body[LANGUAGE_NONE][0]['value'] = $comment->comment;
    }
    if (!isset($comment->subject) && isset($comment->comment)) {
      $comment->subject = $comment->comment;
    }

    comment_save($comment);
    $this->comments[] = $comment;
    return $comment;
  }

  /**
   * Remove any created nodes.
   *
   * @AfterScenario
   */
  public function cleanComments() {
    // Remove any nodes that were created.
    foreach ($this->comments as $comment) {
      comment_delete($comment->cid);
    }
    $this->comments = array();
  }



}
