@content_page
Feature: Taxonomy
  In order to verify that the Content pages are functioning
  As an Admin
  I should be able to create, edit, and delete content pages

  Background: Add/edit Taxonomy
    Given I am logged in as a user with the "administrator" role
    And I am on "/admin/structure/taxonomy"

    @api @javascript
    Scenario: Add Taxonomy
    Given I click "Add vocabulary"
    And I fill in "edit-name" with "Title-Test-for-taxonomy"
    And I fill in "edit-description" with "Description Test for taxonomy"
    And I fill in "edit-vid" with "taxonomy_test"
    And I press "Save"
    And I wait for AJAX to finish
    And I click "Add term"
    And I wait for AJAX to finish
    And I fill in "name[0][value]" with "Name for term for taxonomy"
    And I put "Term copy for taxonomy" into CKEditor
    And I fill in "path[0][alias]" with "/taxonomy-test"
    And I press "Save"
    And I take a screenshot with size "1920" x "1080"

    #And I am on "/admin/structure/taxonomy/manage/taxonomy_test"