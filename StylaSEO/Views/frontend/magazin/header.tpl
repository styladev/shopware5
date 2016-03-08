{extends file='parent:frontend/custom/header.tpl'}

{block name='frontend_index_header_canonical'}{$sCustomPage.canonical_link}{/block}

{block name='frontend_index_header_meta_tags' append}
    {if $feed_type == 'user' || $feed_type == 'magazine' || $feed_type == 'story'}
        {$sCustomPage.meta_fb_app_id}
        {$sCustomPage.meta_og_url}
        {$sCustomPage.meta_og_title}
        {$sCustomPage.meta_og_type}
        {$sCustomPage.meta_og_image}
        {$sCustomPage.author}
    {/if}

{/block}