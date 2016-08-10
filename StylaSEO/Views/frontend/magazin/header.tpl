{extends file='parent:frontend/custom/header.tpl'}

{block name='frontend_index_header_canonical'}{$sCustomPage.canonical_link}{/block}

{* Page title *}
{if $sCustomPage.page_title}{block name='frontend_index_header_title'}{$sCustomPage.page_title}{/block}{/if}

{if $sCustomPage.meta_description}{block name='frontend_index_header_meta_description'}{$sCustomPage.meta_description}{/block}{/if}

{if $sCustomPage.meta_keywords}{block name='frontend_index_header_meta_keywords'}{$sCustomPage.meta_keywords}{/block}{/if}

{block name='frontend_index_header_meta_tags_opengraph'}
	{if $feed_type == 'user' || $feed_type == 'magazine' || $feed_type == 'story'}
		{$sCustomPage.meta_fb_app_id}
		{$sCustomPage.meta_og_url}
		{$sCustomPage.meta_og_title}
		{$sCustomPage.meta_og_type}
		{$sCustomPage.meta_og_image}
		{if $sCustomPage.meta_og_image_width}<meta property="og:image:width" content="{$sCustomPage.meta_og_image_width}" />{"\n"}{/if}
		{if $sCustomPage.meta_og_image_height}<meta property="og:image:height" content="{$sCustomPage.meta_og_image_height}" />{/if}
		{$sCustomPage.author}
	{/if}
{/block}

{* Newly added 151008 *}
{block name="frontend_index_header_favicons"}
{/block}

{block name='frontend_index_header_meta_tags_ie9'}
{/block}

{block name='frontend_index_header_meta_tags_android'}
{/block}
