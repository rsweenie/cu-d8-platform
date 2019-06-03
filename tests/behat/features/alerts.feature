@alerts
Feature: Alerts
  In order to verify that the header alerts are functioning
  As an Admin
  I should be able to enable/disable the Red, Orange and Orange (non-weather) alerts
  As a reader
  I should see the Red, Orange and Orange (non-weather) alerts on all pages when enabled. 

  @api
  Scenario: Red Alert
    Given I am logged in as a user with the "administrator" role
    And I am on "/"
    Then I should not see "CU Data Transform"