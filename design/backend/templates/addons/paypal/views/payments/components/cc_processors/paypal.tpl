{assign var="paypal_currencies" value="standard"|fn_paypal_get_currencies}

<div class="control-group">
    <label class="control-label" for="account">{__("account")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][account]" id="account" value="{$processor_params.account}" >
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="item_name">{__("paypal_item_name")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][item_name]" id="item_name" value="{$processor_params.item_name}" >
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="currency">{__("currency")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][currency]" id="currency">
            {foreach from=$paypal_currencies item="currency"}
                <option value="{$currency.code}"{if !$currency.active} disabled="disabled"{/if}{if $processor_params.currency == $currency.code} selected="selected"{/if}>{$currency.name}</option>
            {/foreach}
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="mode">{__("test_live_mode")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][mode]" id="mode">
            <option value="test" {if $processor_params.mode == "test"}selected="selected"{/if}>{__("test")}</option>
            <option value="live" {if $processor_params.mode == "live"}selected="selected"{/if}>{__("live")}</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="order_prefix">{__("order_prefix")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][order_prefix]" id="order_prefix" value="{$processor_params.order_prefix}" >
    </div>
</div>
