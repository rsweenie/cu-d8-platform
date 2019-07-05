@google_translate
Feature: Google Translate
  In order to verify that Google Translate is functioning
  As a user
  I should be able to see pages in different languages

  Background: Add/edit Taxonomy
    Given I am an anonymous user
    And I am on "/"
    And I set browser window size to "1920" x "1080"
    And I wait for AJAX to finish
    And I wait "3" seconds

    @364480301 @api @javascript
    Scenario: Change Language to German
    Then I should see "Select Language"
    And I select "German" in the "@onchange = 'doGTranslate(this);'" select
    And I wait "3" seconds
    Then I should see "Humanressourcen"
    And I should not see "Human Resources"

    @364480301 @api @javascript
    Scenario: Change Language to Italian 
    Then I should see "Select Language"
    And I select "Italian" in the "@onchange = 'doGTranslate(this);'" select
    And I wait "3" seconds
    Then I should see "Risorse umane"
    And I should not see "Human Resources"