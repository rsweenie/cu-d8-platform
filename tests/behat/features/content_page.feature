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
  Scenario: create/edit content page
    # hax happen here wysiwyg embed image/video/text
    # And I select "Raw HTML" from "body[0][format]"
    # And I fill in "body[0][value]" with "Here's some text using the rich text editor <drupal-entity alt='Karole Davis' data-embed-button='media_browser' data-entity-embed-display='media_image' data-entity-embed-display-settings='{&quot;image_style&quot;:&quot;&quot;,&quot;image_link&quot;:&quot;&quot;,&quot;link_url&quot;:&quot;&quot;}' data-entity-type='media' data-entity-uuid='81f57d5f-e518-407c-95a5-64ee6bd2c6b9' title='Karole Davis'></drupal-entity><drupal-entity data-align='center' data-embed-button='media_browser' data-entity-embed-display='view_mode:media.embedded' data-entity-embed-display-settings='{&quot;link_url&quot;:&quot;&quot;}' data-entity-type='media' data-entity-uuid='188e59f2-fb84-4499-aa05-18d00799b815'></drupal-entity>"
    # And I select "Rich Text" from "body[0][format]"
    # And I press "Continue"
    # And I wait for AJAX to finish
    And I execute JS "1 != 0";
    And I execute JS "jQuery('#cke_377').click()";
    And I wait "3" seconds
    And I wait for AJAX to finish
    And I take a screenshot with size "680" x "full"
    And I fill in "attributes[href]" with "http://www.maxtimothy.com"
    And I take a screenshot with size "680" x "full"
    #save
    And I press "Save"
    And I wait for AJAX to finish
    
    And I take a screenshot with size "680" x "full"