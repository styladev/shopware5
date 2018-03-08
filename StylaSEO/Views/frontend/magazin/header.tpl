{extends file='parent:frontend/custom/header.tpl'}

{* Description *}
{block name='frontend_index_header_meta_description'}
	{if $sCustomPage.meta_description}
		{$sCustomPage.meta_description}
	{/if}
{/block}

{* Metatags *}
{block name='frontend_index_header_meta_tags_opengraph'}
		{$sCustomPage.head_content}
{/block}
{block name='frontend_index_header_canonical'}
{/block}

{* Newly added 151008 *}
{block name="frontend_index_header_favicons"}{/block}
{block name='frontend_index_header_meta_tags_ie9'}{/block}
{block name='frontend_index_header_meta_tags_android'}{/block}
