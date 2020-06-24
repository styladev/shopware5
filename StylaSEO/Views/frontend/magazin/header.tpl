{extends file='parent:frontend/custom/header.tpl'}

{* Title *}
{block name='frontend_index_header_title'}{strip}
	{$sCustomPage.title}
{/strip}{/block}

{* Canonical *}
{block name='frontend_index_header_canonical'}
	{$sCustomPage.canonical}
{/block}

{* Description *}
{block name='frontend_index_header_meta_description'}{strip}
	{$sCustomPage.description}
{/strip}{/block}

{* Opengraph *}
{block name='frontend_index_header_meta_tags_opengraph'}
	{$sCustomPage.openGraph}
{/block}

{* Robots *}
{block name='frontend_index_header_meta_robots'}{strip}
	{$sCustomPage.robots}
{/strip}{/block}

{* Hreflang *}
{block name='frontend_index_header_hreflangs'}
	{$sCustomPage.hreflang}
{/block}

{* Other tags *}
{block name='frontend_index_header_meta_tags'}
	{$smarty.block.parent}
	{$sCustomPage.otherTags}
{/block}

{block name="frontend_index_header_favicons"}{/block}
{block name='frontend_index_header_meta_tags_ie9'}{/block}
{block name='frontend_index_header_meta_tags_android'}{/block}
