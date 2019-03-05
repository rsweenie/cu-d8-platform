---
layout: default
title:  "Front Page Classic"
categories: [Content]
---

{% include home.html %}
# How-To create the front page with classic layout

 Version: 1.0
 Created: 08/30/2018
 Updated: 11/28/2018
 Last Updated: 3/5/2019 klm
 Intended Audience: UCOM

## Summary

The front page classic layout handles the styling without specific content types. For example a featured content group will look differently on a front page than a content page.

## Procedure

1. Login

2. Create content page. 

3. For featured content items: create content > content > featured content group

    * Add needed information, Body, Title and images. Front pages typically have three featured content items. If you need further assistance there is a [featured content group how to.](featured-content-group.html)

    * Save


4. Create content > content > content page

    * Give it a title

    * Add content for the area to the left of the CTA buttons in following format:
        * H3 (area headline)
        * P or normal for content of paragraph
        * add a link for the button
    * Save


5. Manage content > entity Block > content
    * In entity field, type in the name of the slideshow block and select it
    * Set the region to `TOP`
    * Click Add
    * Save


6. Change layout > creighton front page > front page classic
    * Click `x` by block inlineSlideshow and block tabbed accordion
    * Drag body to region `first_content`
    * Save


7. Manage content > entity Block > content
    * Type in the name of the desired featured link buttons and select them
    * Set region to `CALLS_TO_ACTION`
    * Click Add
    * Repeat for the desired number of featured link buttons


8. Manage content > entity Block > content
    * In entity field, type in what you named the featured content group for the front page
    * Set region to `FEATURED_CONTENT`
    * Click Add


9. Manage content > entity Block > content
    * In entity field, type in the name of the green callout box and select it
    * Set region to `BOTTOM_CALLOUT`
    * Click Add


10. Save


11. Assign this page you created as the front/home page
    1) Hover over edit, look at the bottom of your browser window and get the node number. The URl will end with /node/###/edit, where ### are the numebrs you need.
    2) Hover over configuration at the top, then system, then click on basic settings
    3) Scroll to the section titled Front page, add the numbers of the node into the field. Specifically /###
    4) Save
    5) Visit landing page and verify

## Keywords

* front page
* layout
* panelizer
