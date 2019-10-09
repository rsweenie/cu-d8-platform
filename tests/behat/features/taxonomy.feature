@taxonomy 
Feature: Taxonomy
  In order to verify that taxonomy is functioning
  As an Admin
  I should be able to create and delete taxonomy/terms

  Background: Add/edit Taxonomy
    Given I am logged in as a user with the "administrator" role
    And I am on "/admin/structure/taxonomy"

    @365446620 @api @javascript
    Scenario: Add Taxonomy
    Given I click "Add vocabulary"
    #Fields for Taxonomy
    And I fill in "edit-name" with "Title-Test-for-taxonomy"
    And I fill in "edit-description" with "Description Test for taxonomy"
    And I fill in "edit-vid" with "taxonomy_test"
    And I press "Save"
    #Verify
    Then I should see "Title-Test-for-taxonomy"
    And I click "Add term"
    And I fill in "name[0][value]" with "Name for term for taxonomy"
    And I put "Term copy for taxonomy" into CKEditor
    And I fill in "path[0][alias]" with "/taxonomy-test"
    And I press "Save"
    #Deleting Taxonomy test
    And I am on "/admin/structure/taxonomy/manage/taxonomy_test"
    And I click "edit-delete"
    And I press "edit-submit"
    #Verify deletion
    And I am on "/admin/structure/taxonomy"
    Then I should not see "Title-Test-for-taxonomy"
