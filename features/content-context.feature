@api
Feature: Content Context
  In order to be able to use our custom ContentContext class
  As a developer
  I want to be sure that all the steps defined within are working correctly.

  Background:
    Given page content:
      | title          | body            |
      | My first page  | First page body |
      | My second page | Second page body |

  Scenario: Steps that allow to visit, edit and delete a node page work as expected.

    Given I am logged in as a user with the "administrator" role
    And I am visiting the "page" content "My first page"
    Then I should see the heading "My first page"
    And I should see "First page body"

    Given I am editing the "page" content "My second page"
    Then I should see the heading "Edit Basic page My second page"

    Given I am deleting the "page" content "My second page"
    Then I should see the heading "Are you sure you want to delete the content My second page?"
