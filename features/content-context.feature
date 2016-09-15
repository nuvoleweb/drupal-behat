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
    Then I should see the heading "Are you sure you want to delete the content My second page?"

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
