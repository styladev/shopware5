{
    {if isset($lastUpdated)}
    "last_updated": {$lastUpdated},
    "processed_stories": {$processedCount},
    "total_stories": {$totalStories},
    "last_cached_path": "{$lastCachedPath}"{if $error ne ""},{/if}
    {/if}
    {if $error ne ""}
    "error": "{$error}"
    {/if}
}
