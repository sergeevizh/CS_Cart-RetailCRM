{if $cart.chosen_shipping.$group_key == $shipping.shipping_id && $shipping.module == 'sdek'}

    {assign var="office_count" value=$shipping.data.offices|count}
    {assign var="shipping_id" value=$shipping.shipping_id}
    {assign var="old_office_id" value=$select_office.$group_key.$shipping_id}
    <div class="ty-checkout-select-office">

        {foreach from=$shipping.data.offices item=office}
            <div class="ty-one-office">
                <input type="radio" name="select_office[{$group_key}][{$shipping.shipping_id}]" value="{$office.Code}" {if $old_office_id == $office.Code || $office_count == 1}checked="checked"{/if} id="office_{$group_key}_{$shipping.shipping_id}_{$office.Code}" class="ty-office-radio" >
                <div class="ty-office__label">
                    <label for="office_{$office.office_id}" >
                        <p class="ty-one-office__name">{$office.Name}</p>
                        <div class="ty-one-office__description">
                            {$office.City}, {$office.Address}<br />
                            {$office.WorkTime}<br />
                            {$office.Phone}<br />
                            {$office.Note}<br />
                        </div>
                    </label>
                </div>
            </div>
        {/foreach}
    </div>
{/if}
