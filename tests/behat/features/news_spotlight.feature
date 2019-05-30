@news_spotlight
Feature: News and Spotlight
  In order to verify that the News and Spotlight are functioning
  As an administrator
  I should be able to create, edit, edit others, revert and delete content

  Background: Create/Edit News Spotlight
    Given I am logged in as a user with the "administrator" role
    And I am viewing a "news_spotlight" with the title "title"
    And I visit the edit form
    And I select the radio button "News"
    And I fill in "edit-title-0-value" with "News/SpotLight title"
    And I fill in "edit-field-content-page-headline-0-value" with "News/Spotlight heading"
    And I put "News/Spotlight body" into CKEditor

  @api @javascript
  Scenario: New/Spotlight Accordion
    # adding new accordion here
    And I press "Add new Accordion Set"
    Then I wait for AJAX to finish
    Then I select "accordion" from "field_tabbed_accordion[0][field_accordion_display_type]"
    And I fill in "field_tabbed_accordion[0][title][0][value]" with "News/Spotlight accordian title"
    And I fill in "field_tabbed_accordion[0][field_tabbed_acc_content][0][subform][field_tabbed_acc_title][0][value]" with "News/Spotlight accordian item title"
    When I fill in the wysiwyg "field_tabbed_accordion[0][field_tabbed_acc_content][0][subform][field_tabbed_acc_body][0][value]" with "News/Spotlight accordian body"
    And I press "Create Accordion Set"
    Then I wait for AJAX to finish

  @api @javascript
  Scenario:Add Copy Box
    # new copy box sidebar items
    Then I select "Copy Box" from "field_content_page_sidebar_items[actions][bundle]"
    And I press "Add new Sidebar Item"
    Then I wait for AJAX to finish
    And I fill in "field_content_page_sidebar_items[0][title][0][value]" with "Copy Box title"
    When I put "Copy Box body" into CKEditor
    And I press "Create Sidebar Item"
    Then I wait for AJAX to finish
    # save and confirm
    Then I press "Save"
    And the response status code should be 200
    Then I should see "News/Spotlight title has been updated."

  @api @javascript
  Scenario:Add Featured Links
    # new feature links sidebar items
    Then I select "Featured Links" from "field_content_page_sidebar_items[actions][bundle]"
    And I press "Add new Sidebar Item"
    Then I wait for AJAX to finish
    And I fill in "field_content_page_sidebar_items[0][title][0][value]" with "Featured Links title"
    And I fill in "field_content_page_sidebar_items[0][field_featured_p_link][0][subform][field_link_text][0][value]" with "Featured Links text"
    And I fill in "field_content_page_sidebar_items[0][field_featured_p_link][0][subform][field_internal_or_external_link][0][uri]" with "/Featured-Links-link"
    And I press "Create Sidebar Item"
    Then I wait for AJAX to finish
    # save and confirm
    Then I press "Save"
    And the response status code should be 200
    Then I should see "News/Spotlight title has been updated."

  @api @javascript
  Scenario:Add Promo Box
    # new feature links sidebar items
    Then I select "Promo Box" from "field_content_page_sidebar_items[actions][bundle]"
    And I press "Add new Sidebar Item"
    Then I wait for AJAX to finish
    And I fill in "field_content_page_sidebar_items[0][title][0][value]" with "Promo Box title"
    And I fill in "field_content_page_sidebar_items[0][field_promo_box_promo_text][0][value]" with "Promo Box text"
    And I fill in "field_content_page_sidebar_items[0][field_promo_p_link][0][subform][field_link_text][0][value]" with "Promo Box link text"
    And I fill in "field_content_page_sidebar_items[0][field_promo_p_link][0][subform][field_internal_or_external_link][0][uri]" with "/Featured-Links-link"
    And I press "Create Sidebar Item"
    Then I wait for AJAX to finish
    # save and confirm
    Then I press "Save"
    And the response status code should be 200
    Then I should see "News/Spotlight title has been updated."

  @api @javascript
  Scenario:Add Quote Box
    # new feature links sidebar items
    Then I select "Quote Box" from "field_content_page_sidebar_items[actions][bundle]"
    And I press "Add new Sidebar Item"
    Then I wait for AJAX to finish
    And I fill in "field_content_page_sidebar_items[0][title][0][value]" with "Quote Box Title"
    Then I select "Mr." from "field_content_page_sidebar_items[0][field_name_person][0][title]"
    And I fill in "field_content_page_sidebar_items[0][field_name_person][0][given]" with "Quote Box given"
    And I fill in "field_content_page_sidebar_items[0][field_name_person][0][middle]" with "Quote Box mid"
    And I fill in "field_content_page_sidebar_items[0][field_name_person][0][family]" with "Quote Box fam"
    Then I select "Jr." from "field_content_page_sidebar_items[0][field_name_person][0][generational]"
    And I fill in "field_content_page_sidebar_items[0][field_name_person][0][credentials]" with "Mother of Dragons"
    And I fill in "field_content_page_sidebar_items[0][field_quote_box_quote_text][0][value]" with "Quote text area"
    And I fill in "field_content_page_sidebar_items[0][field_quote_box_affiliation][0][value]" with "Affiliation"
    And I fill in "field_content_page_sidebar_items[0][field_quote_p_link][0][subform][field_link_text][0][value]" with "Quote Box link text"
    And I fill in "field_content_page_sidebar_items[0][field_quote_p_link][0][subform][field_internal_or_external_link][0][uri]" with "/qote-box-link"
    And I press "Create Sidebar Item"
    Then I wait for AJAX to finish
    # save and confirm
    Then I press "Save"
    And the response status code should be 200
    Then I should see "News/Spotlight title has been updated."

  @api @javascript
  Scenario:Add Related Links
    # new feature links sidebar items
    Then I select "Related Link" from "field_content_page_sidebar_items[actions][bundle]"
    And I press "Add new Sidebar Item"
    Then I wait for AJAX to finish
    And I fill in "field_content_page_sidebar_items[0][title][0][value]" with "Related Link title"
    And I fill in "field_content_page_sidebar_items[0][field_related_p_link][0][subform][field_link_text][0][value]" with "Related Link text"
    And I fill in "field_content_page_sidebar_items[0][field_related_p_link][0][subform][field_internal_or_external_link][0][uri]" with "/Related-Link-link"
    And I press "Create Sidebar Item"
    Then I wait for AJAX to finish
    # save and confirm
    Then I press "Save"
    And the response status code should be 200
    Then I should see "News/Spotlight title has been updated."

  @api @javascript
  Scenario Outline: Adding Existing Entity
    # adding existing here
    And I press "Add existing <type> <grouping>"
    Then I wait for AJAX to finish
    And I switch to the "entity_browser_iframe_creighton_<frame_id>" frame
    Then I fill in "title" with "<search>"
    And I press "Apply" 
    Then I wait for AJAX to finish
    # having issues with loading content
    Then I wait "3" seconds 
    And I check "entity_browser_select[node:<nid>]"
    And I press "Add Item"
    Then I wait for AJAX to finish
    And I switch to the window
    # save and confirm
    Then I press "Save"
    And the response status code should be 200
    Then I should see "News/Spotlight title has been updated."
    And I take a screenshot
  Examples:
    |frame_id|type|grouping|name|search|nid|
    |sidebar_items|Sidebar|Item|Copy Box|CB -|56|
    |sidebar_items|Sidebar|Item|Feature Links|FL -|31|
    |sidebar_items|Sidebar|Item|Promo Box|PB -|91|
    |sidebar_items|Sidebar|Item|Quote Box|QB -|81|
    |sidebar_items|Sidebar|Item|Realated Link|RL -|76|
    |tabbed_accordion|Accordion|Set|Accordion||21|