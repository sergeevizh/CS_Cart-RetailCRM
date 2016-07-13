{if $status == "Y"}
    {$text_status = __("approved")}
{else}
    {$text_status = __("disapproved")}
{/if}

{include file="common/letter_header.tpl"}

{__("hello")},<br /><br />

{if $products|count > 1}
    {if $status == "Y"}
        {__("products_approval_status_approved")}<br />
    {else}
        {__("products_approval_status_disapproved")}<br />
    {/if}
    {foreach name="products_list" from=$products item="product_name" key="product_id"}
        {$smarty.foreach.products_list.iteration}) <a href="{"products.update?product_id=`$product_id`"|fn_url:"V":"http"}">{$product_name}</a><br />
    {/foreach}
    
    {if $status == "Y"}
        <br />{__("text_shoppers_can_order_products")}
    {/if}
{else}
    {$product_id = $products|key}
    {$product_name = $products|reset}
    {$product_url = "products.update?product_id=`$product_id`"|fn_url:"V":"http"}
    {if $status == "Y"}
        {__("product_approval_status_approved", ["[product]" => "<a href=\"{$product_url}\">{$product_name}</a>"])}
    {else}
        {__("product_approval_status_disapproved", ["[product]" => "<a href=\"{$product_url}\">{$product_name}</a>"])}
    {/if}
{/if}

{if $reason}
    <p>{$reason}</p>
{/if}

{include file="common/letter_footer.tpl"}