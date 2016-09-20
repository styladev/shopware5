{extends file='parent:frontend/custom/header.tpl'}

{block name='frontend_index_header_canonical'}{$sCustomPage.canonical_link}{/block}

{* Page title *}
{if $sCustomPage.page_title}{block name='frontend_index_header_title'}{$sCustomPage.page_title}{/block}{/if}

{if $sCustomPage.meta_description}{block name='frontend_index_header_meta_description'}{$sCustomPage.meta_description}{/block}{/if}

{if $sCustomPage.meta_keywords}{block name='frontend_index_header_meta_keywords'}{$sCustomPage.meta_keywords}{/block}{/if}

{block name='frontend_index_header_meta_tags_opengraph'}
	{if $feed_type == 'user' || $feed_type == 'magazine' || $feed_type == 'story' || $feed_type == 'tag'}
		{$sCustomPage.head_content}
	{/if}
{/block}

{* Newly added 151008 *}
{block name="frontend_index_header_favicons"}
{/block}

{block name='frontend_index_header_meta_tags_ie9'}
{/block}

{block name='frontend_index_header_meta_tags_android'}
{/block}
