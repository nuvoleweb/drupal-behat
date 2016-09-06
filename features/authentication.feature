@api
Feature: Login
  In order to login to the website
  As a non-authenticated user
  I want to be able to login

  Background:
    Given users:
      | name    | mail              | pass |
      | test    | test@example.com  | pass |

  Scenario: User can see login page
    Given I am not logged in
    When I visit "/user"
    Then I should see the link "Log in"
    And I should see the link "Reset your password"
    And I should see the text "Username"
    And I should see the text "Password"
    And I should see the button "Log in"
    But I should see the link "Create new account"

  Scenario: User can login as admin
    Given I am not logged in
    When I visit "/user"
    Then I should see "Username"
    And I enter "test" for "Username"
    And I enter "pass" for "Password"
    And I press the "Log in" button
    And I should see the link "View"
    And I should see the link "Edit"
