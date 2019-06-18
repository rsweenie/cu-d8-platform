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
    #FIlling in various information for a Featured Content Group Item
    And I fill in "title[0][value]" with "Testing Featured Content Group"
    And I put "Featured Content Group copy" into CKEditor
    Then I switch to the "entity_browser_iframe_media_browser" frame 
    And I check the box "entity_browser_select[media:1576]"
    And I switch to the window
    And I fill in "featured_content[0][subform][field_fc_p_link][0][subform][field_link_text][0][value]" with "Text for node link"
    And I fill in "featured_content[0][subform][field_fc_p_link][0][subform][field_internal_or_external_link][0][uri]" with "News (796)"
    And I fill in "featured_content[0][subform][field_fc_supporting_p_links][0][subform][field_link_text][0][value]" with "Link Text for Feature Content Supporting Links"
    And I fill in "featured_content[0][subform][field_fc_supporting_p_links][0][subform][field_internal_or_external_link][0][uri]" with "FL - FP News (51)"
    And I check "featured_content[0][subform][field_fc_supporting_p_links][0][subform][field_open_in_new_window][value]"
    And I press "featured_content_featured_content_item_add_more"
    Then I wait for AJAX to finish
    # having issues with loading content
    And I wait "3" seconds
    # Loading new Feature Content Item, if this is present, then everything is the same
    Then I should see a ".ajax-new-content" element
    And I press "Save"
    Then I should see "Text for node link"
    #Editing Featured Content Griup
    And I click on the ".nav-tabs li:nth-child(2) a" link
    And I click on the "#edit-delete" link
    Then I take a screenshot with size "1920" x "full"
    And I click on the "#edit-submit" link
    Then I should not see "Text for node link"

  

    #And I am on "/admin/content?title=&type=featured_content_group&status=All&langcode=All"
