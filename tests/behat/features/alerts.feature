@alerts
Feature: Alerts
  In order to verify that the header alerts are functioning
  As an Admin
  I should be able to enable, disable and add the Red, Orange and Orange (non-weather) alerts
  As a reader
  I should see the Red, Orange and Orange (non-weather) alerts on pages when enabled. 

  @api @javascript
  Scenario: Red Alert
    #Adding Alert
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
    #Editing Alert
    Given I am on "/admin/content"
    And I select "header_alert" from "edit-type"
    And I press "Filter"
    And I click "Testing Red Header Alert"
    And I click "edit-form"
    And I put "Red Alert copy 2" into CKEditor
    And I press "edit-submit"
    Then I should see "Red Alert copy 2"
    #Anonymous user
    Given I am an anonymous user
    And I am on "/"
    Then I should see "Red Alert copy 2"
    #Disabling Alert
    Given I am logged in as a user with the "administrator" role
    Given I am on "/admin/content?title=&type=header_alert&status=All&langcode=All"
    And I click "Testing Red Header Alert"
    And I click "edit-form"
    And I select "No Alert" from "field_header_alert_activate"
    And I press "edit-submit"
    And I am on "/"
    Then I should not see "Headline for red alert"
    Then I take a screenshot
    #Deleting Alert
    Given I am on "/admin/content?title=&type=header_alert&status=All&langcode=All"
    And I click "Testing Red Header Alert"
    And I click "edit-form"
    And I click "edit-delete"
    And I press "edit-submit"
    And I am on "/"
    Then I should not see "Headline for red alert"

  @api @javascript
  Scenario: Orange (Weather) Alert
    #Adding Alert
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
    #Editing Alert
    Given I am on "/admin/content"
    And I select "header_alert" from "edit-type"
    And I press "Filter"
    And I click "Testing Orange (Weather) Header Alert"
    And I click "edit-form"
    And I put "Orange (Weather) Alert copy 2" into CKEditor
    And I press "edit-submit"
    Then I should see "Orange (Weather) Alert copy 2"
    #Anonymous user
    Given I am an anonymous user
    And I am on "/"
    Then I should see "Orange (Weather) Alert copy 2"
    #Deleting Alert
    Given I am logged in as a user with the "administrator" role
    And I am on "/admin/content"
    And I select "header_alert" from "edit-type"
    And I press "Filter"
    And I click "Testing Orange (Weather) Header Alert"
    And I click "edit-form"
    And I click "edit-delete"
    And I press "edit-submit"
    And I am on "/"
    Then I should not see "Headline for Orange (Weather) alert"

  @api @javascript
  Scenario: Orange (Non-Weather) Alert
    #Adding Alert
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
    #Editing Alert
    Given I am on "/admin/content"
    And I select "header_alert" from "edit-type"
    And I press "Filter"
    And I click "Testing Orange (Non-Weather) Header Alert"
    And I click "edit-form"
    And I put "Orange (Non-Weather) Alert copy 2" into CKEditor
    And I press "edit-submit"
    Then I should see "Orange (Non-Weather) Alert copy 2"
    #Anonymous user
    Given I am an anonymous user
    And I am on "/"
    Then I should see "Orange (Non-Weather) Alert copy 2"
    #Deleting Alert
    Given I am logged in as a user with the "administrator" role
    And I am on "/admin/content"
    And I select "header_alert" from "edit-type"
    And I press "Filter"
    And I click "Testing Orange (Non-Weather) Header Alert"
    And I click "edit-form"
    And I click "edit-delete"
    And I press "edit-submit"
    And I am on "/"
    Then I should not see "Headline for Orange (Non-Weather) alert"

