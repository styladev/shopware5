{extends file='parent:frontend/custom/index.tpl'}

{block name='frontend_index_header' append}
    {include file='frontend/magazin/header.tpl'}
{/block}

{* Sidebar left - Disable added 151012 *}
{block name="frontend_index_content_left"}
    {include file="frontend/index/sidebar.tpl"}
{/block}
