---
layout: default
title:  "Changing Site Logo"
categories: [Content] 
---
{% include home.html %}
# Changing the site name and title
* Version: 1.0
* Created: 09/28/2019 CMB
* Last Updated: 09/28/2019 cmb
* Intended Audience: UCOM

## Summary

The site name is changed in Configuration, but whether to show the site name is set in the Block Layout 

## Prerequisites

 1. Access to subdomain
 2. Permissions to change configuration
 3. Permissions to change block layout

## Procedure to change site name

1. Login to sub domain
2. In the administration menu navigate to Configuration > System > Basic site settings
3. Change the "Site name" field value
4. Click "Save configuration"

## Procedure to toggle on/off the site name block

1. In the administration menu navigate to Structure > Block Layout
2. If the "Site Name" block should be disabled:
    1. Click the inverted triangle on the corresponding "Configure" button
    2. Choose "Disable"
    3. Clear cache
3. If the "Site Name" block should be enabled:
    1. Click the the corresponding "Enable" button
    3. Clear cache
