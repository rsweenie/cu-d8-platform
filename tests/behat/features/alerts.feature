@alerts
Feature: Alerts
  In order to verify that the header alerts are functioning
  As an Admin
  I should be able to enable, disable, add and edit the Red, Orange and Orange (non-weather) alerts
  As a reader
  I should see the Red, Orange and Orange (non-weather) alerts on pages when enabled. 

  @api @javascript
  Scenario: Adding Red Alert
    Given I am logged in as a user with the "administrator" role
    And I am on "/node/add"
    And I click "Header Alert"
    And I fill in "title[0][value]" with "Testing Red Header Alert"
    And I select "Active" from "field_header_alert_activate"
    And I select "dangerous" from "field_header_alertemergency_type"
    And I fill in "field_emergency_headline[0][value]" with "Headline for red alert"
    And I put "Red Alert copy" into CKEditor
    And I press "edit-submit"
    Then I should see "Red Alert copy"
    #Anonymous user
    Given I am an anonymous user
    And I am on "/"
    Then I should see "Red Alert copy"

  @api @javascript
  Scenario: Adding Orange (Weather) Alert
    Given I am logged in as a user with the "administrator" role
    And I am on "/node/add"
    And I click "Header Alert"
    And I fill in "title[0][value]" with "Testing Orange (Weather) Header Alert"
    And I select "Active" from "field_header_alert_activate"
    And I select "weather" from "field_header_alertemergency_type"
    And I fill in "field_emergency_headline[0][value]" with "Headline for Orange (Weather) alert"
    And I put "Orange (Weather) Alert copy" into CKEditor
    And I press "edit-submit"
    Then I should see "Orange (Weather) Alert copy"
    #Anonymous user
    Given I am an anonymous user
    And I am on "/"
    Then I should see "Orange (Weather) Alert copy"

  @api @javascript
  Scenario: Adding Orange (Non-Weather) Alert
    Given I am logged in as a user with the "administrator" role
    And I am on "/node/add"
    And I click "Header Alert"
    Then I fill in "title[0][value]" with "Testing Orange (Non-Weather) Header Alert"
    Then I select "Active" from "field_header_alert_activate"
    Then I select "other" from "field_header_alertemergency_type"
    Then I fill in "field_emergency_headline[0][value]" with "Headline for Orange (Non-Weather) alert"
    Then I put "Orange (Non-Weather) Alert copy" into CKEditor
    Then I press "edit-submit"
    Then I should see "Orange (Non-Weather) Alert copy"
    #Anonymous user
    Given I am an anonymous user
    And I am on "/"
    Then I should see "Orange (Non-Weather) Alert copy"

  @api @javascript
  Scenario: Editing Red Alert
    Given I am logged in as a user with the "administrator" role
    And I am on "/admin/content"
    And I select "header_alert" from "edit-type"
    And I wait for AJAX to finish
    And I press "Filter"
    And I click "HA - Red alert"
    And I click "edit-form"
    And I select "Active" from "field_header_alert_activate"
    And I press "edit-submit"
    Then I should see "Red Alert"
    Then I click "edit-form"
    And I select "No Alert" from "field_header_alert_activate"
    And I press "edit-submit"
    #Anonymous user
    Given I am an anonymous user
    And I am on "/"
    Then I should not see "Red Alert"

  @api @javascript
  Scenario: Editing Orange (Weather) Alert
    Given I am logged in as a user with the "administrator" role
    And I am on "/admin/content"
    And I select "header_alert" from "edit-type"
    And I wait for AJAX to finish
    And I press "Filter"
    And I click "HA - Orange alert"
    And I click "edit-form"
    And I select "Active" from "field_header_alert_activate"
    And I press "edit-submit"
    Then I should see "WEATHER RELATED ALERT"
    Then I click "edit-form"
    And I select "No Alert" from "field_header_alert_activate"
    And I press "edit-submit"
    #Anonymous user
    Given I am an anonymous user
    And I am on "/"
    Then I should not see "WEATHER RELATED ALERT"
  
  @api @javascript
  Scenario: Editing Orange (Non-Weather) Alert
    Given I am logged in as a user with the "administrator" role
    And I am on "/admin/content"
    And I select "header_alert" from "edit-type"
    And I wait for AJAX to finish
    And I press "Filter"
    And I click "HA - orange alert - non weather"
    And I click "edit-form"
    And I select "Active" from "field_header_alert_activate"
    And I press "edit-submit"
    Then I should see "CAMPUS ALERT"
    Then I click "edit-form"
    And I select "No Alert" from "field_header_alert_activate"
    And I press "edit-submit"
    #Anonymous user
    Given I am an anonymous user
    And I am on "/"
    Then I should not see "CAMPUS ALERT"



