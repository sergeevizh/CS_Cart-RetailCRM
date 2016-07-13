{if $cart.chosen_shipping.$group_key == $shipping.shipping_id && $shipping.module == 'dellin' && $shipping.service_params.arrival_door == 'N'}

    {if $display == "radio"}
        {assign var="arrival_count" value=$shipping.data.arrival_terminals|count}
        {assign var="shipping_id" value=$shipping.shipping_id}
        {assign var="old_terminal" value=$arrival_terminal.$group_key.$shipping_id}
        <div class="checkout-select-terminals">
            {foreach from=$shipping.data.arrival_terminals item=arrival_terminal}
                <div class="one-terminal">
                    <input type="radio" name="arrival_terminal[{$group_key}][{$shipping.shipping_id}]" value="{$arrival_terminal.code}" {if $old_terminal == $arrival_terminal.code || $arrival_count == 1}checked="checked"{/if} id="terminal_{$arrival_terminal.code}" class="terminal-radio" />
                    <div class="terminal__label">
                        <label for="terminal_{$arrival_terminal.code}" >
                            <p class="one-terminal__name">{$arrival_terminal.name}</p>
                            <div class="one-terminal__description">
                                {$arrival_terminal.address}
                            </div>
                        </label>
                    </div>
                </div>
            {/foreach}
        </div>
    {/if}
{/if}
