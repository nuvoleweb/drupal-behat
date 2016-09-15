@api
Feature: Drupal Context
  In order to be able to use our custom DrupalContext
  As a developer
  I want to be sure that all the steps defined within are working correctly.

  Background:

    Given users:
      | name       | mail              | roles         |
      | John Smith | john@example.com  | administrator |

  Scenario: Additional DrupalContext steps work as expected.

    Given I am logged in as "John Smith"
    Given I create "page" content:
      | Title                      |
      | Page created by John Smith |
    And I am visiting the "page" content "Page created by John Smith"

    Then I should see the heading "Page created by John Smith"
