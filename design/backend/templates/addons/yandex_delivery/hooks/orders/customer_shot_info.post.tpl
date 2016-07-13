{foreach from=$order_info.shipping item="shipping" key="shipping_id" name="f_shipp"}
    {if $shipping.module == 'yandex' && $shipping.pickup_data && $shipping.pickup_data.type == "PICKUPPOINT"}
        <div class="well orders-right-pane form-horizontal">
            <div class="control-group shift-top">
                <div class="control-label">
                    {include file="common/subheader.tpl" title=__("yandex_delivery_pickuppoint")}
                </div>
            </div>

            <p class="strong">
            {$shipping.pickup_data.name}
            </p>
            <p class="muted">
                {$shipping.pickup_data.full_address}<br />
                {foreach from=$shipping.pickup_data.phones item="phone"}
                    {$phone.number}
                {/foreach}
                <br />

                {include file="addons/yandex_delivery/views/yandex_delivery/components/schedules.tpl" schedules=$shipping.pickup_data.work_time}
            </p>
        </div>
    {/if}
{/foreach}
