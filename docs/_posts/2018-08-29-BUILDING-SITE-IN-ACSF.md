---
layout: default
title:  "New Site in Acquia Cloud Site Factory"
categories: [ACSF]
---
{% include home.html %}
# Creating New Sites in Acquia Cloud Site Factory

* Version: 1.1
* Created: 07/17/2018
* Last Updated: 02/06/2019 cmb
* Intended Audience: UCOM

In Acquia Cloud Site Factory, new websites should first be created in your production environment, and then staged to your non-production environments for thorough testing before deploying them to production.
Production: [creighton.acsitefactory.com](https://www.creighton.acsitefactory.com)

1. Select one of the groups from the menu on the left side of the screen.

    ![Select group](images/select_group.png "Select Group Screenshot")

2. Select the “Create a new site” button on the top right of the screen.
    ![Create new site](images/create_new_site.png "Create New Site Screenshot")

3. Enter the site name. `!important` Select the creighton installation profile from the list at the bottom of the screen.
    ![Installation profile](images/installation_profile.png "Installation profile Screenshot")

4. Click the `Create site` button at the bottom of the screen. `!caution` Coordinate with devs on this step: The site should now be staged to the dev and test environments for testing prior to launch, but care should be taken. Staging sites down to dev or test can wipe all sites currently in those environments and only copy down the sites selected for staging. More info on staging sites to non-production environments [here](https://docs.acquia.com/site-factory/workflow/staging/).

