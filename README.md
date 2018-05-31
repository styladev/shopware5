# Styla SEO Enhancements Shopware Module (v5.4.0)
#### Author: Mark Mulder (BSolut GmbH)
#### Contributor: Sebastian Sachtleben, Christian Korndoerfer, Roberto Solís, Antonio Cosentino

Styla Magazine Plugin is a module to connect your Shopware 5 Store with [Styla](http://www.styla.com/). For our Shopware 4 plugin check this https://github.com/styladev/shopware4

The first diagram on [this page](https://styladocs.atlassian.net/wiki/spaces/CO/pages/9961481/Technical+Integration) should provide you an overview of what the plugin does and how it exchanges data with Styla.


## Installation How-to

- Place the *StylaSEO* folder at the following location of your Shopware installaton: `engine/Shopware/Plugins/Local/Frontend`

- Once the code is in place, access your Shopware administration page. The Styla Magazine Plugin can be configured and activated under **Configuration -> Plugin Manager -> Installed**.

- Click on the Pencil (edit) icon to edit the plugin settings:
    - **Styla Magazine ID**: Your Styla username which is provided to you by your Styla account manager. If it's in the Email format (magazine_id@styla.com) then use just the magazine_id part, without the @styla.com
    - **Styla SEO Server URL** _(default: https://seoapi.styla.com)_: Server that provides SEO information for your magazine content. (**IMPORTANT:** Do not modify this unless approved by Styla)
    - **Styla API Server URL** _(default: https://client-scripts.styla.com)_: Server that provided the necessary scripts and styles for your magazine. (**IMPORTANT:** Do not modify this unless approved by Styla)
    - **Styla Base Folder** _(default: magazine)_: Path to your main magazine page. Your magazine will become available at `/[Styla Base Folder]` (e.g. `/magazine`). (**IMPORTANT:** Before changing, make sure to contact you account manager and provide him/her the new magazine path)
- Once all done, clear Shopware cache

If everything is set up correctly the following pages will be accessible:

    - **Main magazine:** http://[yourwebsite.com]/[Styla Base Folder]/
    - **Tag:** http://[yourwebsite.com]/[Styla Base Folder]/tag/[tagname]
    - **Category:** http://[yourwebsite.com]/[Styla Base Folder]/user/[Styla Magazine ID]/category/[category]
    - **Story:** http://[yourwebsite.com]/[Styla Base Folder]/story/[storyname]
    - **Search:** http://[yourwebsite.com]/[Styla Base Folder]/search/[searchterm]


### Please do not create any subpages in your CMS or directories for your magazine. The plugin itself will take care of setting up the /magazine/ (or any other) page on which the magazine will appear and of the routing as well.

--

## Opengraph tags for Shopware 5.0.0 - 5.0.2

All Shopware 5 versions between 5.0.0 and 5.0.2 have no opengraph block for displaying sharing relevant informations.

The easiest way to add opengraph tags is to add the block in the base header.tpl template.

1. Open the header.tpl located here: /themes/Frontend/Bare/frontend/index/header.tpl (or within the theme you use)

2. Add the new opengraph block:

    ```
    {* Meta opengraph tags *}
    {block name='frontend_index_header_meta_tags_opengraph'}{/block}
    ```

    ![Add opengraph tags block](/readme/readme_meta_tags_opengraph.png)


## SEO Content from Styla's SEO API

The module uses data from Styla's SEO API to:
* generate tags like: meta tags including `<title>`, canonical link, og:tags, static content inserted into <body>, `robots` instructions
* insert these tags accordingly into HTML of the template the page with Styla content uses
  
This is done to provide search engine bots with data to crawl and index all Styal URLs, which are in fact a Single-Page-Application.

Once you install and configure the module, please open source of the page on which your Styla content is embedded and check if none of the tags mentioned below are duplicated. In case `robots`or `link rel="canonical"` or any other are in the HTML twice, make sure to remove the original ones coming from your default template. Otherwise search engine bots might not be able to crawl all the Styla content or crawl it incorrectly. 

You can finde more information on the SEO API on [this page](https://styladocs.atlassian.net/wiki/spaces/CO/pages/9961486/SEO+API+and+Sitemaps+Integration)

## Setup Process

The process of setting up your Content Hub(s) usually goes as follows:

1. Install and configure the plugin on your stage using Content Hub ID(s) shared by Styla
2. Share the stage URL, credentials with Styla
4. Styla integrates product data from endpoints provided by the plugin, test your stage Content Hub and asks additional questions, if needed
5. Install and configure the plugin on production, without linking to the Content Hub(s) there and, again, share the URL with Styla
6. Make sure your content is ready to go live
7. Styla conducts final User Acceptance Tests before the go-live
8. Go-live (you link to the Content Hub embedded on your production)
