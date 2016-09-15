<?php

namespace NuvoleWeb\Drupal\DrupalExtension\Context;

use Behat\MinkExtension\Context\RawMinkContext;

/**
 * Class CKEditorContext.
 *
 * @package NuvoleWeb\Drupal\DrupalExtension\Context
 */
class CKEditorContext extends RawMinkContext {

  /**
   * Fill CKEditor with given value.
   *
   * @Given I fill in the rich text editor :label with :text
   */
  public function iFillInTheRichTextEditorWith($label, $text) {
    /** @var \Behat\Mink\Element\NodeElement $field */
    $field = $this->getSession()->getPage()->findField($label);

    if (NULL === $field) {
      throw new \Exception(sprintf('Field "%s" not found.', $label));
    }

    $args_as_js_object = json_encode([
      'ckeditor_instance_id' => $field->getAttribute('id'),
      'value' => $text,
    ]);

    $this->getSession()->executeScript(
      "args = {$args_as_js_object};" .
      "CKEDITOR.instances[args.ckeditor_instance_id].setData(args.value);"
    );
  }

}
