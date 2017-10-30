@api @javascript
Feature: CKEditor Context
  In order to be able to use our custom CKEditorContext
  As a developer
  I want to be sure that all the steps defined within are working correctly.

  Background:

    Given users:
      | name       | mail              | roles         |
      | John Smith | john@example.com  | administrator |

  Scenario: Test steps that CKEditor steps are working correctly.

    Given I am logged in as a user with the "administrator" role

    When I am on "node/add/page"
    And I fill in "Title" with "My page title"
    And I fill in the rich text editor "Body" with "My <b>body</b>."
    And I press "Save and publish"

    Then I should see the heading "My page title"
    And I should see "My body."
