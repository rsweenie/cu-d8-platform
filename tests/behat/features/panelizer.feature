@Panels
Feature: Panelizer IPE
  In order to verify that the Panels are functioning
  As an administrator
  I should be able to create, edit, and move blocks

  Background: edit content page with ipe
    Given I am logged in as a user with the "administrator" role
    And I set browser window size to "1920" x "1080"
    And I am viewing a "content_page" with the title "panel test"
    And I visit the edit form
    And I fill in "field_content_page_headline[0][value]" with "content page headline"
    And I put "content page body" into CKEditor 
    And I press "Add existing Slide"
    And I wait for AJAX to finish
    Then I switch to the "entity_browser_iframe_creighton_slideshow" frame
    And I visit the "tr:nth-of-type(1) td input" link
    And I visit the "tr:nth-of-type(2) td input" link
    And I press "op"
    Then I wait for AJAX to finish
    And I switch to the window
    
    # adding existing Accordion
    And I press "ief-76892c35868b5c161d105599ed207ec2ce9142f1-add-existing"
    Then I wait for AJAX to finish
    And I switch to the "entity_browser_iframe_creighton_tabbed_accordion" frame
    And I press "Apply" 
    Then I wait for AJAX to finish
    And I wait for "3" seconds
    And I visit the "tr:nth-of-type(1) td input" link
    And I press "Add Item"
    Then I wait for AJAX to finish
    And I switch to the window
    # save
    Then I press "Save"
  @api @javascript @374990656
  Scenario: Changing ipe layout
    And I change the layout to "flexbox_three_section" from the "Creighton Layouts" category
    Then I open the "Edit" tab
    And I place the "views_block:profile-block_33" block from the "Lists (Views)" category
    And I place the "entity_field:node:body" block from the "Content" category
    And I move block "Slick Slideshow" to region "left"
    
    # verify
    Then I should see "REGION: TOP"
    And I should see "BLOCK: SLICK SLIDESHOW"
    And I should see "BLOCK: BODY"
    And I should see "BLOCK: TABBED/ACCORDION"
    And I should see "REGION: LEFT"
    And I should see "REGION: RIGHT"
    #save
    Then I save the layout
    And I wait for AJAX to finish

    #verify
    Then I should not see "REGION: TOP"
    And I should not see "BLOCK: SLICK SLIDESHOW"
    And I take a screenshot with size "1920" x "full"
    And I should see "You can include captions"
    And I should see the link "And links"
    And I should not see "BLOCK: BODY"
    And I should see "content page body"
    And I should not see "BLOCK: TABBED/ACCORDION"
    And I should see "ACCORDION"
    And I should see "ACCORDION 2"
    And I should see "ACCORDION 3"
    And I should not see "REGION: LEFT"
    And I should not see "REGION: RIGHT"