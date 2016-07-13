{assign var="yd_shipping_select" value=$cart.shippings_extra.yd.data.$group_key[$shipping.shipping_id]}
{assign var="yd_pickup_index" value=$cart.shippings_extra.yd.pickup_index.$group_key[$shipping.shipping_id]}

<script type="text/javascript" src="https://api-maps.yandex.ru/2.0/?load=package.standard,package.geoQuery&lang=ru-RU"></script>
<script src="https://delivery.yandex.ru/widget/loader?resource_id=2035&sid=973&key=cc976dd8482810bb9c0b39427ffc3876"></script>
{script src="js/addons/yandex_delivery/widget.js"}

<div class="ty-yandex-delivery-block ty-clearfix" >

    <div>
        <div>
            <div class="ty-yandex-delivery-title">
                <h4 class="ty-yandex-delivery-name">{$yd_shipping_select.delivery.name}</h4>
            </div>


                <div class="ty-button-submit">
                    <input type="submit" class="ty-float-right" name="yd" id="yd{$group_key}"
                           data-ydwidget-open
                           data-shipping-id="{$shipping.shipping_id}"
                           data-group-id="{$group_key}"
                           data-weight="{$cart.product_groups.$group_key.package_info.W}"
                           data-length="{$cart.shippings_extra.yd.package_size.$group_key.length}"
                           data-width="{$cart.shippings_extra.yd.package_size.$group_key.width}"
                           data-height="{$cart.shippings_extra.yd.package_size.$group_key.height}"
                           data-cost="{$cart.product_groups.$group_key.package_info.C}"
                           data-city="{$cart.user_data.s_city}"
                           value={__(change)}
                            />
                </div>
        </div>

        <div class="ty-yd-type ty-float-left">
            {if $yd_shipping_select.type == 'PICKUP'}
                {__('yandex_delivery_pickuppoint')}
            {else}
                {__('yandex_delivery_todoor')}
            {/if}
        </div>

    </div>

    <div class="ty-clear-both">


        <div class="ty-yd-description">
            <div>
                {if $yd_shipping_select.address}{$yd_shipping_select.address}.{/if}
                {if $ud_shipping_select.metro} {__('yandex_delivery_metro')} {$yd_shipping_select.metro}.{/if}
            </div>

            <div class="ty-yd-location">
                {$yd_shipping_select.pickup_points.$yd_pickup_index.full_address}
            </div>

            <p>{include file="addons/yandex_delivery/views/yandex_delivery/components/schedules.tpl" schedules=$yd_shipping_select.schedule_days}</p>

            {if $yd_shipping_select.contact_phone}
                <p>{$yd_shipping_select.contact_phone}</p>
            {/if}

            <div>
                <p>
                    {__('yandex_delivery_pay_accepted')}:
                    {if $yd_shipping_select.pickup_points.$yd_pickup_index.has_payment_cash}
                        {__('yandex_delivery_cash')}{if $yd_shipping_select.card}, {/if}
                    {/if}

                    {if $yd_shipping_select.pickup_points.$yd_pickup_index.has_payment_card}
                        {__('yandex_delivery_credit_card')}
                    {/if}
                </p>
            </div>

            <div>
                <p><strong>{__('shipping_cost')}:</strong> {include file="common/price.tpl" value=$yd_shipping_select.costWithRules class="nowrap"}
                    ({$yd_shipping_select.days} {__('yandex_delivery_days')}, {__('yandex_delivery_deliver_orient_day')} {$yd_shipping_select.deliver_orient_day})</p>
            </div>
        </div>
    </div>

    {if $yd_shipping_select.pickup_points.$yd_pickup_index.address.comment}
        <div class="ty-yandex-delivery-instruction">
            <span><strong>{__('yandex_delivery_instruction')}:</strong><br /> {$yd_shipping_select.pickup_points.$yd_pickup_index.address.comment}</span>
        </div>
    {/if}

</div>
