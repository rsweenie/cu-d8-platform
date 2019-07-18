  @api
  Feature: Content model
  In order to enter structured content into my site
  As a content editor
  I want to have content entity types that reflect my content model.

  Scenario: Bundles
    Then exactly the following content entity type bundles should exist
      | Name | Machine name | Type | Description |
      | Copy Box | copy_box | Content type | A basic text block that may have pre-defined styles applied to it. |
      | Content Page | content_page | Content type | Use <em>content pages</em> for your static content, such as an 'About us' page. |
      | Featured Content Group | featured_content_group | Content type | One or more featured content items that will have the same orientation and display in a group. |
      | Featured Links | featured_links | Content type | A link used for calls-to-action that appear as a graphic button that appears in the &#039;Second Sidebar&#039; region (to the right of the body when viewing in a desktop environment). |
      | Content Page | content_page | Content type | Use <em>content pages</em> for your static content, such as an 'About us' page. |

  Scenario: Fields
    Then exactly the following fields should exist
      | Type | Bundle | Field label | Machine name | Field type | Required | Cardinality | Form widget | Translatable | Help text |
      | Content type | Copy Box | Body | body | Text (formatted, long, with summary) |  | 1 | Text area with a summary |  |  |
      | Content type | Copy Box | Display Type | field_copy_box_display_type | List (text) |  | - | Textfield |  |  |
      | Content type | Featured Links | Button Color | field_featured_link_button_color | List (text) |  |  | Textfield |  |  |
      | Content type | Featured Links | Featured Link | field_featured_p_link | Entity reference |  | 1 |  |  |  |
