@canonical_links_https
Feature: Canonical Links Https

   Canonical links should be Https

   @367930468 @api
   Scenario: check canonical links are https
   Given I am an anonymous user
   And I am on "/"
   Then I the canonical link should be https