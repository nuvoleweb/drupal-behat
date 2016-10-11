@api
Feature: Service Container Context

  Scenario: Service parameters can be overridden via Behat
    Given I override the following service parameters:
      | parameter1  | value |
    Then the service parameter "parameter1" should be set to "value"

  Scenario: Service parameters are restored after evey scenario
    Then the service parameter "parameter1" should not be set to "value"
