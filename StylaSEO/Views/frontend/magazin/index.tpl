{extends file='parent:frontend/custom/index.tpl'}

{block name='frontend_index_header'}
    {include file='frontend/magazin/header.tpl'}
{/block}

{* Sidebar left *}
{block name='frontend_index_content_left'}
    {$smarty.block.parent}
    <style>
        /* hide on desktop version */
        aside.sidebar-main { display: none; }
    </style>
{/block}