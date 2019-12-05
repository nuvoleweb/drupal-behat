@api
Feature: Content Context
  In order to be able to use our custom ContentContext class
  As a developer
  I want to be sure that all the steps defined within are working correctly.

  Background:

    Given users:
      | name           | mail                       | roles          |
      | administrator  | john@example.com           | administrator  |
      | page_editor    | page_editor@example.com    | page_editor    |
      | article_editor | article_editor@example.com | article_editor |

    Given page content:
      | title          | body             |
      | My first page  | First page body  |
      | My second page | Second page body |

    Given article content:
      | title             | body                |
      | My first article  | First article body  |
      | My second article | Second article body |

  Scenario: Test steps that allow to visit, edit and delete a node page.

    Given I am logged in as a user with the "administrator" role
    And I am visiting the "page" content "My first page"
    Then I should see the heading "My first page"
    And I should see "First page body"

    Given I am editing the "page" content "My second page"
    Then I should see the heading "Edit Basic page My second page"

    Given I am deleting the "page" content "My second page"
    Then I should see the heading "Are you sure you want to delete the content item My second page?"

  Scenario Outline: Test content access steps.

    Then "<name>" can "<op>" "<title>" content
    Examples:
      | name            | op     | title         |
      | page_editor     | create | My first page |
      | page_editor     | edit   | My first page |
      | page_editor     | delete | My first page |

  Scenario Outline: Test content access steps.

    Then "<name>" cannot "<op>" "<title>" content
    Examples:
      | name            | op     | title         |
      | article_editor  | create | My first page |
      | article_editor  | edit   | My first page |
      | article_editor  | delete | My first page |

  Scenario: Test content edit link steps.

    Given I am logged in as "article_editor"
    Then I should not see a link to edit content "My first page"


  Scenario: Test yaml content creation and translation.

    Given the following content:
      """
      title: English example page
      type: page
      langcode: en
      body: Behat is very awesome
      """
    And the following translation for "page" content "English example page":
      """
      title: Arrr example
      type: page
      langcode: en-x-pirate
      body: Behat be extra full 'o awe
      """
    When I am an anonymous user
    And I am visiting the "page" content "English example page"
    Then I should see the heading "English example page"
    And I should see the text "Behat is very awesome"
    # The language switcher is in the sidebar.
    When I click "Pirate"
    Then I should see the heading "Arrr example"
    And I should see the text "Behat be extra full 'o awe"


  Scenario: Test yaml content creation with paragraphs.

    Given "category" terms:
      | name       |
      | Category A |
      | Category B |
    Given the following content:
      """
      title: Example paragraph page
      type: page
      langcode: en
      field_paragraphs:
        -
          type: title_paragraph
          field_title: Simple paragraph
        -
          type: complex
          field_category: Category A
          field_link:
            title: Single link example
            uri: http://example.com
          field_sub_paragraph:
            -
              type: complex
              field_category: Category B
              field_link:
                -
                  title: Multiple links example
                  uri: http://example.com
                -
                  title: Second example
                  uri: http://example.com
            -
              type: title_paragraph
              field_title: Complex paragraph with sub paragraphs

      """

    When I am an anonymous user
    And I am visiting the "page" content "Example paragraph page"
    And I should see the text "Simple paragraph"
    And I should see the link "Single link example"
    And I should see the link "Multiple links example"
    And I should see the link "Second example"
    And I should see the text "Complex paragraph with sub paragraphs"
    And I should see the text "Category A"
    And I should see the text "Category B"
