@javascript
Feature: Responsive Context
  In order to be able to use our custom ResponsiveContext class
  As a developer
  I want to be sure that all the steps defined within are working correctly.

  Scenario Outline: Test responsive context steps.

    Given I am on "/"
    And I view the site on a "<device>" device
    Then the browser window width should be "<width>"
    And the browser window height should be "<height>"

    Examples:
      | device  | width | height |
      | laptop  | 1280  | 800    |
      | desktop | 2560  | 1440   |

