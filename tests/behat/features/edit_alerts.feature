@edit
Feature: Edit
  In order to verify that the header alerts are editable
  As an Admin
  I should be able to edit the Red, Orange and Orange (non-weather) alerts

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
    And I am on "/"
    Then I should not see "CAMPUS ALERT"

