# Styla SEO Enhancements Shopware Module (v0.9.9) 
## Installation How-to
#### Author: Mark Mulder (BSolut GmbH)
#### Last updated: 15.12.2014

--

1. Place the *StylaSEO* folder at the following location of your Shopware installaton: engine/Shopware/Plugins/Local/Frontend

2. Once the code is in place, access your Shopware administration page. The Styla SEO Enhancements Shopware module can be configured and activated under **Configuration -> Plugin Manager -> Local Extensions**.

3. Click on the Pencil (edit) icon to edit the plugin settings. Enter your username at **Amazine/Styla Username** (i.e. the name that appears for your magazine page at http://amazine.com/user/[username].

4. If you do not wish to use /magazin as your base folder for displaying the Styla content, please enter a new path under **Amazine/Styla Base Folder** (IMPORTANT: do not modify this unless it's approved by Styla, since the Javascript snippet relies on this base directory name).

5. If all is working, the pages will be accessible at :
   
    - **http://[yourwebsite.com]/magazin/**
    (Default feed, as per http://amazine.com/user/[your_username])
    
    - **http://[yourwebsite.com]/magazin/tag/[tagname]**
    (Products by tag view, as per http://amazine.com/user/[username]/tag/[tagname])
    
    - **http://[yourwebsite.com]/magazin/story/[storyname]**
    (Story view, as per http://amazine.com/user/[username]/story/[storyname])
    
    - **http://[yourwebsite.com]/magazin/user/[username]**
    (User feed, as per http://amazine.com/user/[username])
    
    - **http://[yourwebsite.com]/magazin/search/[searchterm]**
    (Search results, as per http://amazine.com/search/[searchterm])
