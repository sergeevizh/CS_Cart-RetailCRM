{if $shipping.service_code == 'pickpoint'}
    {if $cart.chosen_shipping.$group_key == $shipping.shipping_id}
        {assign var="shipping_id" value=$shipping.shipping_id}
        {assign var="pickpoint_postamat" value=$pickpoint_office.$group_key.$shipping_id}
        {if $addons.rus_pickpoint.secure_protocol == 'Y'}
            {assign var="url" value='https://pickpoint.ru/select/postamat.js'}
        {else}
            {assign var="url" value='http://pickpoint.ru/select/postamat.js'}
        {/if}
        <script type="text/javascript" src="{$url}"></script>

        <input type="hidden" name="pickpoint_office[{$group_key}][{$shipping.shipping_id}][pickpoint_id]" id="pickpoint_id" value="{$pickpoint_postamat.pickpoint_id}" />
        <input type="hidden" name="pickpoint_office[{$group_key}][{$shipping.shipping_id}][address_pickpoint]" id="address_pickpoint" value="{$pickpoint_postamat.address_pickpoint}" />
        <div>{$pickpoint_postamat.address_pickpoint}</div>
        <a href="#" id="pickpoint_terminal_cart" onclick="fn_open_pickpoint();">{__("addons.rus_pickpoint.select_terminal")}</a>
    {/if}
{/if}
