{if $field != $order_by}
    {$direction = "none"}
    {$order_direction = "asc"}
{else}
    {if $direction == "asc"}
        {$order_direction = "desc"}
    {else}
        {$order_direction = "asc"}
    {/if}
{/if}
{strip}
<a class="cm-ajax cm-ajax-cache" href="{"`$url`?order_by=`$field`,`$order_direction`&debugger_hash=`$debugger_hash`"|fn_url}" data-ca-target-id="{$target_id}">
    {$text}
    {if $direction == "none"}
        <i class="exicon-asc"></i><i class="exicon-desc" style="margin-left: -7px;"></i>
    {else}
        <i class="exicon-{$order_direction}"></i>
    {/if}
</a>
{/strip}