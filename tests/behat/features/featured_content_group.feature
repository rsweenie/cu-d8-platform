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
    And I set browser window size to "1920" x "2440"
    And I am on "/node/add/featured_content_group"
    And I fill in "title[0][value]" with "Testing Featured Content Group"
    And I select the radio button "Vertical"
    And I put "Featured Content Group copy" into CKEditor
    Then I switch to the "entity_browser_iframe_media_browser" frame
    And I click "Upload"   
    #And I check the box "entity_browser_select[media:<media_number>]"
    And I attach the file "/tests/behat/media/test_profile.jpg" to "edit-input-file"
    Then I press "Place"
    Then I wait for AJAX to finish
    #And I fill in "entity[image][0][alt]" with "alt text for test"
   
   




    #And I press "edit-submit"
    #Then I should see "Red Alert copy"
    #Anonymous user
    #Given I am an anonymous user
    #And I am on "/"
    #Then I should see "Red Alert copy"