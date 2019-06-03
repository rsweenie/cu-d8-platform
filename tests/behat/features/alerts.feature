@alerts
Feature: Alerts
  In order to verify that the header alerts are functioning
  As an Admin
  I should be able to enable/disable the Red, Orange and Orange (non-weather) alerts
  As a reader
  I should see the Red, Orange and Orange (non-weather) alerts on all pages when enabled. 

  @api @javascript
  Scenario: Red Alert
    Given I am logged in as a user with the "administrator" role
    And I am on "/node/add"
    And I click "Header Alert"
    Then I fill in "title[0][value]" with "Testing Red Header Alert"
    Then I select "Active" from "field_header_alert_activate"
    Then I select "dangerous" from "field_header_alertemergency_type"
    Then I fill in "field_emergency_headline[0][value]" with "Headline for red alert"
    Then I put "Red Alert copy" into CKEditor
    Then I press "edit-submit"
    #Then I select "Header Alert" from "edit-type"
    #Then I wait for AJAX to finish
    #Then I click "Filter"
    #And I take a screenshot
    Given I am an anonymous user
    And I am on "/"
    Then I should see "Red Alert copy"
    #Then I take a screenshot


  @api @javascript
  Scenario: Orange (Weather) Alert
    Given I am logged in as a user with the "administrator" role
    And I am on "/node/add"
    And I click "Header Alert"
    Then I fill in "title[0][value]" with "Testing Orange (Weather) Header Alert"
    Then I select "Active" from "field_header_alert_activate"
    Then I select "weather" from "field_header_alertemergency_type"
    Then I fill in "field_emergency_headline[0][value]" with "Headline for Orange (Weather) alert"
    Then I put "Orange (Weather) Alert copy" into CKEditor
    Then I press "edit-submit"
    Given I am an anonymous user
    And I am on "/"
    Then I should see "Orange (Weather) Alert copy"

  @api @javascript
  Scenario: Orange (Non-Weather) Alert
    Given I am logged in as a user with the "administrator" role
    And I am on "/node/add"
    And I click "Header Alert"
    Then I fill in "title[0][value]" with "Testing Orange (Non-Weather) Header Alert"
    Then I select "Active" from "field_header_alert_activate"
    Then I select "other" from "field_header_alertemergency_type"
    Then I fill in "field_emergency_headline[0][value]" with "Headline for Orange (Non-Weather) alert"
    Then I put "Orange (Non-Weather) Alert copy" into CKEditor
    Then I press "edit-submit"
    Given I am an anonymous user
    And I am on "/"
    Then I should see "Orange (Non-Weather) Alert copy"
    #And I take a screenshot
