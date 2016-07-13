{foreach from=$order_info.shipping item="shipping_method"}
	{if $shipping_method.pickup_data && $shipping_method.module == 'yandex'}
        <p class="ty-strong">
            {$shipping_method.pickup_data.name}
        </p>
        <p class="ty-muted">
            {if $shipping_method.pickup_data.full_address} {$shipping_method.pickup_data.full_address}{/if}</br>
            {if $shipping_method.pickup_data.phones[0]}
                {__("phone")}: {$shipping_method.pickup_data.phones[0].number}</br>
            {/if}
            {if $shipping_method.pickup_data.work_time}
                {include file="addons/yandex_delivery/views/yandex_delivery/components/schedules.tpl" schedules= $shipping_method.pickup_data.work_time}
            {/if}
            {if $shipping_method.pickup_data.address.comment}
                {$shipping_method.pickup_data.address.comment nofilter}
            {/if}
        </p>

        {assign var="store_count" value=1}
        {assign var="shipping_id" value=$order_info.shipping.shipping_id}

        {assign var="store_location" value=$shipping_method.pickup_data}
        {assign var="map_container" value="yd_map"}
        {include file="addons/yandex_delivery/views/yandex_delivery/components/yandex_details.tpl"}
        <div class="clearfix ty-yd-select-store__map-full-div">
            <div class="ty-yd-select-store__map-details" id="{$map_container}"></div>
        </div>
    {/if}
{/foreach}