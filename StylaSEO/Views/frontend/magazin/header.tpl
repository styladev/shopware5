{extends file='parent:frontend/custom/header.tpl'}

{* Page title *}
{* if $sCustomPage.page_title}{block name='frontend_index_header_title'}{$sCustomPage.page_title}{/block}{/if *}

{* Description *}
{if $sCustomPage.meta_description}{block name='frontend_index_header_meta_description'}{$sCustomPage.meta_description}{/block}{/if}

{* Metatags *}
{block name='frontend_index_header_meta_tags_opengraph'}
		{$sCustomPage.head_content}
{/block}

{* Newly added 151008 *}
{block name="frontend_index_header_favicons"}{/block}
{block name='frontend_index_header_meta_tags_ie9'}{/block}
{block name='frontend_index_header_meta_tags_android'}{/block}
