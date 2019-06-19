@content_page
Feature: Content Page
  In order to verify that the Content pages are functioning
  As an Admin
  I should be able to create, edit, and delete content pages

  Background: Create/Edit Content Page
    Given I am logged in as a user with the "administrator" role
    And I am viewing a "content_page" with the title "title"
    And I visit the edit form

  @api @javascript
  Scenario: ckeditor test
    Then I press "cke_21"
    And I wait for AJAX to finish
    And I take a screenshot with size "1920" x "full"