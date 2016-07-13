{if $product_groups}

    {script src="js/addons/yandex_delivery/yandex.js"}

    {foreach from=$product_groups key=group_key item=group}
        {if $group.shippings && !$group.shipping_no_required}

            {assign var="shipping_data" value=$group.chosen_shippings.0.service_params}

            {foreach from=$group.shippings item=shipping}

                {if $cart.chosen_shipping.$group_key == $shipping.shipping_id}

                    {assign var="shippings_extra"  value=$cart.shippings_extra.yd.data.$group_key[$shipping.shipping_id]}

                    {if $shippings_extra.pickup_points}

                        {$old_store_id = $old_ship_data.$group_key.select_pickup_id}
                        {$shipping_id = $shipping.shipping_id}
                        {$select_id = $select_yd_store.$group_key.$shipping_id}
                        {$store_count = $shippings_extra.pickup_points|count}

                        {if $store_count == 1}
                            {foreach from=$shippings_extra.pickup_points item=store}
                                <div class="sidebar-row ty-yd-store">
                                    <input type="hidden" name="select_yd_store[{$group_key}][{$shipping_id}]" value="{$store.id}" id="store_{$group_key}_{$shipping_id}_{$store.id}">
                                    {$store.name}
                                    <p class="muted">
                                        {if $store.full_address}{$store.full_address}{/if}
                                    </p>
                                </div>
                            {/foreach}
                        {else}

                            {foreach from=$shippings_extra.pickup_points item=store name=st}

                                <div class="sidebar-row ty-yd-store" {if !empty($shipping_data.count_points) && $smarty.foreach.st.iteration > $shipping_data.count_points} style="display: none;"{/if}>
                                    <div class="control-group">
                                        <div id="pickup_stores" class="controls">
                                            <label for="store_{$group_key}_{$shipping_id}_{$store.id}" class="radio">
                                                <input type="radio" name="select_yd_store[{$group_key}][{$shipping_id}]" value="{$store.id}" {if $select_id == $store.id || (!$select_id && $old_store_id == $store.id)}checked="checked"{/if} id="store_{$group_key}_{$shipping_id}_{$store.id}" class="cm-submit cm-ajax cm-skip-validation" data-ca-dispatch="dispatch[order_management.update_shipping]">
                                                {$store.name} ({$store.delivery_name})
                                            </label>
                                            <p class="muted">
                                                {if $store.full_address}{$store.full_address}{/if}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            {/foreach}
                        {/if}

                        {if !empty($shipping_data.count_points) && $store_count > $shipping_data.count_points}
                            <div class="ty-yd-show-all">
                                <a class="cm-combination ty-cart-content__detailed-link detailed-link ty-yd-show_all__link cm-show-all-point">{__("yandex_delivery.all_point")}</a>
                            </div>
                        {/if}
                    {/if}
                {/if}
            {/foreach}
        {/if}
    {/foreach}
{/if}