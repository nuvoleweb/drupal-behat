@wip
@api @javascript
Feature: Chosen Context
  In order to be able to use our custom ChosenFieldContext class
  As a developer
  I want to be sure that all the steps defined within are working correctly.

  Background:

    Given "category" terms:
      | name       |
      | Category 1 |
      | Category 2 |
      | Category 3 |
      | Category 4 |
      | Category 5 |

  Scenario: Test Chosen steps.

    Given I am logged in as a user with the "administrator" role

    When I am on "node/add/article"
    And I fill in "Title" with "My first article"
    And I add "Category 1" to the chosen element "Category"
    And I select "Category 2" on the Chosen element "Category"

    When I press "Save and publish"
    Then I should see the link "Category 1"
    And I should see the link "Category 2"

    When I click "Edit"
    And I remove "Category 2" from the Chosen element "Category"
    And I press "Save and keep published"

    Then I should see the link "Category 1"
    And I should not see the link "Category 2"

    Given I am on "node/add/article"
    And I fill in "Title" with "My second article"
    And I fill in the following chosen fields:
      | Category | Category 1 |
    When I press "Save and publish"
    Then I should see the link "Category 1"

    When I click "Edit"
    And I unset the following chosen fields:
      | Category | Category 1 |
    And I press "Save and keep published"

    Then I should not see the link "Category 1"
