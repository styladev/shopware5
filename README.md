# Styla SEO Enhancements Shopware Module (v5.2.0)
#### Author: Mark Mulder (BSolut GmbH)
#### Contributor: Sebastian Sachtleben, Christian Korndoerfer, Roberto Solís
#### Last updated: 24.10.2016

--

## Installation How-to

- Place the *StylaSEO* folder at the following location of your Shopware installaton: `engine/Shopware/Plugins/Local/Frontend`

- Once the code is in place, access your Shopware administration page. The Styla SEO Enhancements Shopware module can be configured and activated under **Configuration -> Plugin Manager -> Installed**.

- Click on the Pencil (edit) icon to edit the plugin settings:
    - **Styla Magazine ID**: Your Styla username which is provided to you by your Styla account manager.
    - **Styla SEO Server URL** _(default: http://seo.styla.com/)_: Server that provides SEO information for your magazine content. (**IMPORTANT:** Do not modify this unless approved by Styla)
    - **Styla API Server URL** _(default: http://live.styla.com/)_: Server that provided the necessary scripts and styles for your magazine. (**IMPORTANT:** Do not modify this unless approved by Styla)
    - **Styla Base Folder** _(default: magazine)_: Path to your main magazine page. Your magazine will become available at `/[Styla Base Folder]` (e.g. `/magazine`). (**IMPORTANT:** Before changing, make sure to contact you account manager and provide him/her the new magazine path)

If everything is set up correctly the following pages will be accessible:

    - **Main magazine:** http://[yourwebsite.com]/[Styla Base Folder]/
    - **Tag:** http://[yourwebsite.com]/[Styla Base Folder]/tag/[tagname]
    - **Category:** mhttp://[yourwebsite.com]/[Styla Base Folder]/user/[Styla Magazine ID]/category/[category]
    - **Story:** http://[yourwebsite.com]/[Styla Base Folder]/story/[storyname]
    - **Search:** http://[yourwebsite.com]/[Styla Base Folder]/search/[searchterm]

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
    
    ![Add opengraph tags block](/readme/readme_opengraph_tags.png)