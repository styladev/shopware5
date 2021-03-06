# Styla SEO Enhancements Shopware Module

Styla Magazine Plugin is a module to connect your Shopware 5 Store with [Styla](http://www.styla.com/). For our Shopware 4 plugin check this https://github.com/styladev/shopware4

[This documentation page](https://docs.styla.com/) should provide you an overview of how Styla works in general. 


## Installation How-to

- Place the *StylaSEO* folder at the following location of your Shopware installaton: `engine/Shopware/Plugins/Local/Frontend`

- Once the code is in place, access your Shopware administration page. The Styla Magazine Plugin can be configured and activated under **Configuration -> Plugin Manager -> Installed**.

- Click on the Pencil (edit) icon to edit the plugin settings:
    - **Styla Magazine ID**: Your Styla username which is provided to you by your Styla account manager. If it's in the Email format (magazine_id@styla.com) then use just the magazine_id part, without the @styla.com
    - **Styla SEO Server URL** _(default: http://seoapi.styla.com)_: Server that provides SEO information for your magazine content. (**IMPORTANT:** Do not modify this unless approved by Styla)
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
