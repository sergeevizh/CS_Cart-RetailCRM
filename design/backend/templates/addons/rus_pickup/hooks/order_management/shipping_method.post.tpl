{if $product_groups}
    {foreach from=$product_groups key=group_key item=group}
        {if $group.shippings && !$group.shipping_no_required}

            {foreach from=$group.shippings item=shipping}
                {if $cart.chosen_shipping.$group_key == $shipping.shipping_id}
                
                    {if $shipping.data.stores}

                        {assign var="old_store_id" value=$old_ship_data.$group_key.store_location_id}
                        {assign var="shipping_id" value=$shipping.shipping_id}
                        {assign var="select_id" value=$select_store.$group_key.$shipping_id}
                        {assign var="store_count" value=$shipping.data.stores|count}

                        {if $store_count == 1}
                            {foreach from=$shipping.data.stores item=store}
                            <div class="sidebar-row">
                                <input type="hidden" name="select_store[{$group_key}][{$shipping_id}]" value="{$store.store_location_id}" id="store_{$group_key}_{$shipping_id}_{$store.store_location_id}" class="cm-submit cm-ajax cm-skip-validation" data-ca-dispatch="dispatch[order_management.update_shipping]">
                                {$store.name} {if $store.pickup_surcharge}({include file="common/price.tpl" value=$store.pickup_surcharge}){/if}
                                <p class="muted">
                                {$store.city}, {$store.pickup_address},
                                {$store.pickup_phone}</br>
                                {__("rus_pickup.work_time")}: {$store.pickup_time}
                                </p>
                            </div>
                            {/foreach}
                        {else}
                            {foreach from=$shipping.data.stores item=store}
                            <div class="sidebar-row">
                                <div class="control-group">
                                    <div id="pickup_stores" class="controls">
                                        <label for="store_{$group_key}_{$shipping_id}_{$store.store_location_id}" class="radio">
                                            <input type="radio" name="select_store[{$group_key}][{$shipping_id}]" value="{$store.store_location_id}" {if $select_id == $store.store_location_id || (!$select_id && $old_store_id == $store.store_location_id)}checked="checked"{/if} id="store_{$group_key}_{$shipping_id}_{$store.store_location_id}" class="cm-submit cm-ajax cm-skip-validation" data-ca-dispatch="dispatch[order_management.update_shipping]"> {$store.name} {if $store.pickup_surcharge}({include file="common/price.tpl" value=$store.pickup_surcharge}){/if}
                                        </label>
                                        <p class="muted">                                
                                            {$store.city}, {$store.pickup_address},
                                            {$store.pickup_phone}</br>
                                            {__("rus_pickup.work_time")}: {$store.pickup_time}
                                        </p>
                                    </div>    
                                </div> 
                            </div>
                            {/foreach}
                        {/if}
                    {/if}
                {/if}
            {/foreach}
        {/if}
    {/foreach}
{/if}