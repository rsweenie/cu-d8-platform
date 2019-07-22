@content_page
Feature: Content Page
  In order to verify that the Content pages are functioning
  As an Admin
  I should be able to create, edit, and delete content pages

  Background: Create/Edit Content Page
    Given I am logged in as a user with the "administrator" role
    And I am viewing a "content_page" with the title "title"
    And I set browser window size to "1920" x "1080"
    And I visit the edit form

  @api @javascript
  Scenario: Un/publish
    And I press "Scheduling options"
    Then I fill in "edit-publish-on-0-value-date" with "2019-11-23"
    And I fill in "edit-publish-on-0-value-time" with "20:22:45"
    Then I fill in "edit-unpublish-on-0-value-date" with "2019-11-23"
    And I fill in "edit-unpublish-on-0-value-time" with "20:23:45"
    # save the content
    And I press "Save"
    #verify
    Then I go to "/admin/content/scheduled"
    Then I should see "title"
    And I should see "Content Page"
    And I should see "Sat, 11/23/2019 - 20:22"
    And I should see "Sat, 11/23/2019 - 20:23"

  @api @javascript @375963940
  Scenario: create/edit content page
    And I wait for AJAX to finish
    # order matters here...
    And I visit the "span.cke_toolgroup a:nth-of-type(8)" link
    # hax happen here wysiwyg embed image/video/text
    And I select "Raw HTML" from "body[0][format]"
    And I fill in "body[0][value]" with "Here's some text using the rich text editor <drupal-entity alt='Karole Davis' data-embed-button='media_browser' data-entity-embed-display='media_image' data-entity-embed-display-settings='{&quot;image_style&quot;:&quot;&quot;,&quot;image_link&quot;:&quot;&quot;,&quot;link_url&quot;:&quot;&quot;}' data-entity-type='media' data-entity-uuid='81f57d5f-e518-407c-95a5-64ee6bd2c6b9' title='Karole Davis'></drupal-entity><drupal-entity data-align='center' data-embed-button='media_browser' data-entity-embed-display='view_mode:media.embedded' data-entity-embed-display-settings='{&quot;link_url&quot;:&quot;&quot;}' data-entity-type='media' data-entity-uuid='188e59f2-fb84-4499-aa05-18d00799b815'></drupal-entity>"
    And I select "Rich Text" from "body[0][format]"
    And I press "Continue"
    And I wait for "3" seconds
    And I fill in class "#linkit-editor-dialog-form form .form-text" with "http://www.smokedhamandstuff.com"
    # open in new window
    And I visit the "#linkit-editor-dialog-form form .form-checkbox" link
    # save link
    And I visit the ".editor-linkit-dialog button.form-submit" link
    # And I wait for AJAX to finish
    And I switch to Iframe ".cke_wysiwyg_frame.cke_reset"
    And I select "http://www.smokedhamandstuff.com"
    # save the content
    And I press "Save"
    #verify
    Then I should see "http://www.smokedhamandstuff.com"

  @api @javascript
  Scenario: Adding Header Image
    Given I am logged in as a user with the "administrator" role
    And I am on "/node/add/content_page"
    And I fill in "title[0][value]" with "Header Image Test"
    And I press "Header Background Image"
    And I wait for AJAX to finish
    #Switching to the image browser
    And I switch to the "entity_browser_iframe_media_browser" frame 
    And I click "Upload"
    And I wait for AJAX to finish
    And I wait "3" seconds
    #Attaching file from repo
    And I attach the file "test_profile.jpg" to "input_file"
    And I wait for AJAX to finish
    And I wait "3" seconds
    #Fields for the image
    And I fill in "entity[image][0][alt]" with "Alt text for header image"
    And I fill in "entity[name][0][value]" with "Name text for Header Image"
    And I press "Place"
    And I wait for AJAX to finish
    And I wait "3" seconds
    #Switch back to the main window to save
    And I switch to the window
    And I press "Save"
    Then I should see "Content Page Header Image Test has been created"
 
  @api @javascript
  Scenario: Adding Inline Slide
    Given I am logged in as a user with the "administrator" role
    And I am on "/node/add/content_page"
    And I fill in "title[0][value]" with "Inline Slider Test"
    And I press "edit-field-slide-actions-ief-add-existing"
    And I wait for AJAX to finish
    And I wait "3" seconds
    #Trouble with two identical iframes, have to compromise 
    And I switch to the "entity_browser_iframe_creighton_slideshow" frame
    #Add two slides: THESE MIGHT NEED TO CHANGE AS SITE GETS UPDATED
    And I check the box "entity_browser_select[node:11]"
    And I check the box "entity_browser_select[node:96]"
    #Press save in the iframe
    And I press "op"
    And I switch to the window
    #Waiting just in case
    And I wait for AJAX to finish
    And I wait "3" seconds
    #Press save in the parent window
    And I press "op"
    Then I should see "Content Page Inline Slider Test has been created"

  @api @javascript
  Scenario: Menu Settings    
    Given I am logged in as a user with the "administrator" role
    And I am on "/node/add/content_page"
    And I fill in "title[0][value]" with "Menu Settings Test"
    #Click the Menu Settings button
    And I visit the ".menu-link-form .seven-details__summary" button
    #Menu settings fields
    And I check the box "edit-menu-enabled"
    And I fill in "menu[title]" with "Text for Menu link title"
    And I fill in "menu[description]" with "Text for Menu link description"
    And I fill in "menu[weight]" with "10"
    And I press "Save"
    Then I should see "Content Page Menu Settings Test has been created"

  @api @javascript
  Scenario: Metatags   
    Given I am logged in as a user with the "administrator" role
    And I am on "/node/add/content_page"
    And I fill in "title[0][value]" with "Metatags"
    #Click the Metatags button
    And I visit the "#edit-field-meta-tags-0 .seven-details__summary" button
    #Metatag fields
    And I check the box "edit-menu-enabled"
    And I fill in "field_meta_tags[0][basic][title]" with "Text for Metatags title"
    And I fill in "field_meta_tags[0][basic][description]" with "Text for Metatags summary"
    And I fill in "field_meta_tags[0][basic][abstract]" with "Text for Metatags abstract"
    And I fill in "field_meta_tags[0][basic][keywords]" with "Keywords Metatags"
    And I press "Save"
    Then I should see "Content Page Metatags has been created"

  @api @javascript
  Scenario: URL Alias  
    Given I am logged in as a user with the "administrator" role
    And I am on "/node/add/content_page"
    And I fill in "title[0][value]" with "URL Alias"
    #Click the URL Alias button
    And I visit the "#edit-path-0 .seven-details__summary" button
    #URL Alias fields
    And I uncheck the box "path[0][pathauto]"
    And I fill in "path[0][alias]" with "/test-url-alias"
    And I press "Save"
    Then the url should match "/test-url-alias"

  @api @javascript
  Scenario: Promotion Options 
    Given I am logged in as a user with the "administrator" role
    And I am on "/node/add/content_page"
    And I fill in "title[0][value]" with "Promotion Options test"
    #Click the Promotion Options button
    And I visit the "#edit-options .seven-details__summary" button
    And I check the box "private[0][stored]"
    And I press "Save"
    #Click the edit button
    And I visit the ".nav-tabs li:nth-child(2) a" link
    #Verify the "Private" option is selected
    Then the "private[0][stored]" checkbox should be checked
    