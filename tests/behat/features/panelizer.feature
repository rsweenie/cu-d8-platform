@news_spotlight
Feature: Panelizer IPE
  In order to verify that the News and Spotlight are functioning
  As an administrator
  I should be able to create, edit, edit others, revert and delete content

  Background: edit content page with ipe
    Given I am logged in as a user with the "administrator" role
    And I am viewing a "content_page" with the title "panel test"
    And I visit the edit form
    And I fill in "field_content_page_headline[0][value]" with "content page headline"
    And I put "content page body" into CKEditor 
    # save
    Then I press "Save"

  @api @javascript
  Scenario: Changing ipe layout
    And I change the layout to "flexbox_three_section" from the "Creighton Layouts" category
    Then I open the "Edit" tab
    And I take a screenshot with size "680" x "full"
    # verify
    Then I should see "REGION: TOP"
    And I should see "BLOCK: SLICK SLIDESHOW"
    And I should see "BLOCK: BODY"
    And I should see "content page body"
    And I should see "flim flams"
    And I should see "BLOCK: TABBED/ACCORDION"
    And I should see "REGION: LEFT"
    And I should see "REGION: RIGHT"