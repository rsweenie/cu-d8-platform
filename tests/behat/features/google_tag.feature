@google_tags
Feature: Google Tags

   @410416666 @api
   Scenario: check that google tags are correct
   Given I am an anonymous user
   And I am on "/"
   Then the "noscript" element should contain "https://www.googletagmanager.com/ns.html?id=GTM-WGCXHD"