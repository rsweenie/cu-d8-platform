@example
Feature: Web drivers
  In order to verify that web drivers are working
  As a user
  I should be able to load the homepage
  With and without Javascript

  @javascript
  Scenario: Load a page with Javascript
    Given I am on "/"
    Then I should be on "/"

  Scenario: Load a page without Javascript
    Given I am on "/"
    Then the response status code should be 200

  @api
  Scenario: Load page as authenticated user
    Given I am logged in as a user with the "authenticated user" role
    And I am on "/"
    Then the response status code should be 200

  Scenario: Content loaded
    Given I am an anonymous user
    And I am on "/" 
    Then I should see "Classic Front Page Headline"
    And all the links should be valid
