@data_transform
Feature: Data Transform
  In order to verify that the data transformation module if functioning
  As an Admin
  I should see the link in config Menu
  I should be able to run the transformation
  and I should see a report in the recent reports when finished 

  @api
  Scenario: User Config Menu Item
    Given I am logged in as a user with the "authenticated user" role
    And I am on "/"
    Then I should not see "CU Data Transform"

  @api
  Scenario: User Config Menu Item
    Given I am an anonymous user
    And I am on "/"
    Then I should not see "CU Data Transform"
    And all the links should be valid

  @api
  Scenario: Admin Config Menu Item
    Given I am logged in as a user with the "administrator" role
    And I am on "/"
    Then I should see "CU Data Transform"
    When I click "CU Data Transform"
    Then I should see "Run Data Transformations"
  
  @api @javascript
  Scenario: Admin Links Transformation Button
    Given I am logged in as a user with the "administrator" role
    And I am on "/admin/config/cu_data_transform/settings"
    Then I should see "Run Data Transformations"
    When I press "Run paragraph links transformation"
    # wait for the script to finish
    And I wait for "5" seconds
    Then I should see "Links Transformed"