@featured_content_group
Feature: Featured Content Group
  In order to verify that the content type Featured Content Group is functioning
  As an Admin
  I should be able to create and delete content
  As a reader
  I should see the content created by the content type Featured Content Group

  @api @javascript
  Scenario: Adding Featured Content Group
    Given I am logged in as a user with the "administrator" role
    And I am on "/node/add/featured_content_group"
    And I set browser window size to "1920" x "1080"
    #Filling in various information for a Featured Content Group Item
    And I fill in "title[0][value]" with "Testing Featured Content Group"
    And I put "Featured Content Group copy" into CKEditor
    And I switch to the "entity_browser_iframe_media_browser" frame 
    #Hijacking css finder to check the first item in media browser
    And I visit the "tr:nth-of-type(1) td input" link
    And I switch to the window
    And I fill in "featured_content[0][subform][field_fc_p_link][0][subform][field_link_text][0][value]" with "Text for node link"
    And I fill in "featured_content[0][subform][field_fc_p_link][0][subform][field_internal_or_external_link][0][uri]" with "News (796)"
    And I fill in "featured_content[0][subform][field_fc_supporting_p_links][0][subform][field_link_text][0][value]" with "Link Text for Feature Content Supporting Links"
    And I fill in "featured_content[0][subform][field_fc_supporting_p_links][0][subform][field_internal_or_external_link][0][uri]" with "FL - FP News (51)"
    And I check "featured_content[0][subform][field_fc_supporting_p_links][0][subform][field_open_in_new_window][value]"
    And I press "featured_content_featured_content_item_add_more"
    And I wait for AJAX to finish
    #Loading content
    And I wait "3" seconds
    #Loading new Feature Content Item, if this is present, then everything is the same
    Then I should see a ".ajax-new-content" element
    And I press "Save"
    And I am on "/admin/content"
    Then I should see "Testing Featured Content Group"
    #Deleting Featured Content Group'
    And I click "Testing Featured Content Group"
    And I wait for AJAX to finish
    And I visit the edit form
    And I wait "5" seconds
    And I visit the "#edit-delete" link
    And I visit the "#edit-submit" link
    Then I should see "Testing Featured Content Group has been deleted"