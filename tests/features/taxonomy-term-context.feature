@api
Feature: Taxonomy Term Context
  In order to be able to use our custom TaxonomyTermContext class
  As a developer
  I want to be sure that all the steps defined within are working correctly.

  Background:

    Given "category" terms:
      | name       |
      | Category 1 |

  Scenario: Test steps that allow to visit, edit and delete a taxonomy term page.

    Given I am logged in as a user with the "administrator" role
    
    And I am visiting the "category" term "Category 1"
    Then I should see the heading "Category 1"

    Given I am editing the "category" term "Category 1"
    Then I should see the heading "Edit term"

    Given I am deleting the "category" term "Category 1"
    Then I should see the heading "Are you sure you want to delete the taxonomy term Category 1?"

