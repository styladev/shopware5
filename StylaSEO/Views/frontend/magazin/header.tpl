{extends file='parent:frontend/custom/header.tpl'}

{* Title *}
{block name='frontend_index_header_title'}{strip}
	{$sCustomPage.title}
{/strip}{/block}

{* Meta Tags *}
{block name='frontend_index_header_meta_tags'}
	{$sCustomPage.metaTags}
{/block}

{* Cleaning up everything else *}
{block name='frontend_index_header_canonical'}
{/block}
{block name="frontend_index_header_favicons"}{/block}
{block name='frontend_index_header_meta_tags_ie9'}{/block}
{block name='frontend_index_header_meta_tags_android'}{/block}
