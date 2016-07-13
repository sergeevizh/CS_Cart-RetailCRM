{assign var="yd_shippings_extra" value=$cart.shippings_extra.yd.data.$group_key[$shipping.shipping_id]}
{assign var="old_store_id" value=$select_yd_store.$group_key[$shipping.shipping_id]}
{assign var="store_count" value=$yd_shippings_extra.pickup_points|count}
{assign var="map_container" value="yd_map_$group_key"}
{$store_locations = $yd_shippings_extra.pickup_points}

{include file="addons/yandex_delivery/views/yandex_delivery/components/yandex.tpl"}

{if !empty($store_locations)}

<div class="ty-yd-select-store__map-wrapper">
    {if $store_count > 1}
        <div class="ty-pickup-location__item-all_stores">
            <div class="ty-pickup-location__item-view">{include file="buttons/button.tpl" but_role="text" but_meta="cm-yd-view-locations ty-btn__secondary" but_text=__("view_all") but_extra="data-ca-scroll={$map_container} data-ca-group-key={$group_key} "}</div>
        </div>

    {/if}
    <div class="ty-yd-select-store__map" id="{$map_container}">
    </div>
</div>

<div class="ty-yd-select-store">

{foreach from=$store_locations item=store name=st}

    <div class="ty-yd-store{if $store_count == 1} ty-yd-store__selected{/if}"{if !empty($shipping.service_params.count_points) && $smarty.foreach.st.iteration > $shipping.service_params.count_points} style="display: none;"{/if}>
        <input type="radio" name="select_yd_store[{$group_key}][{$shipping.shipping_id}]" value="{$store.id}" {if $old_store_id == $store.id || $store_count == 1}checked="checked"{/if} id="store_{$group_key}_{$shipping.shipping_id}_{$store.id}" class="ty-yd-store__radio-{$group_key}  ty-valign cm-yd-select-store">

        <div class="ty-yd-store__label">
            <a data-ca-scroll="yd_map_0" data-ca-latitude="{$store.lat}" data-ca-longitude="{$store.lng}" data-ca-group-key="{$group_key}" class="cm-yd-view-location ty-icon-location"></a>
            <label for="store_{$group_key}_{$shipping.shipping_id}_{$store.id}" class="ty-valign ty-yd-store__name">
                {$store.name}
            </label>

            <div class="ty-yd-store__description">
                {$yd_shippings_extra.deliveries[$store.delivery_id].delivery.name}, {if $store.short_address}{$store.short_address}{/if}
                <br/>
                {if $store.phones[0].number}{__("phone")}: {$store.phones[0].number}</br>{/if}
                {if $store.address.comment}
                    <a id="sw_store_description_{$store.id}" class="cm-combination ty-cart-content__detailed-link detailed-link ty-yd-store__detailed-link">{__("description")}</a>
                    <div id="store_description_{$store.id}" class="hidden ty-yd-store__comment">
                        {if $store.full_address}{$store.full_address}. {/if}
                        {$store.address.comment nofilter}
                    </div>
                {/if}
            </div>
        </div>
    </div>
{/foreach}
</div>
    {if $shipping.service_params.count_points != 0 && $store_count > $shipping.service_params.count_points}
    <div class="ty-yd-show-all">
        <a class="cm-combination ty-cart-content__detailed-link detailed-link ty-yd-show_all__link cm-show-all-point">{__("yandex_delivery.all_point")}</a>
    </div>
    {/if}
{/if}

