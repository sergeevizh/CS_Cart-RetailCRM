<fieldset>

<div class="control-group">
    <label class="control-label" for="ship_width">{__("ship_width")}</label>
    <div class="controls">
        <input id="ship_width" type="text" name="shipping_data[service_params][width]" size="30" value="{$shipping.service_params.width}" />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="ship_height">{__("ship_height")}</label>
    <div class="controls">
        <input id="ship_height" type="text" name="shipping_data[service_params][height]" size="30" value="{$shipping.service_params.height}" />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="ship_length">{__("ship_length")}</label>
    <div class="controls">
        <input id="ship_length" type="text" name="shipping_data[service_params][length]" size="30" value="{$shipping.service_params.length}" />
    </div>
</div>

<div class="control-group">
    <label for="sort_type" class="control-label">{__("yandex_delivery.sort_points_list")}:</label>
    <div class="controls">
        <select id="sort_type" name="shipping_data[service_params][sort_type]">
            <option value="no" {if $shipping.service_params.sort_type == "no"}selected="selected"{/if}>{__("yandex_delivery.sort_no")}</option>
            <option value="near" {if empty($shipping.service_params.sort_type) || $shipping.service_params.sort_type == "near"}selected="selected"{/if}>{__("yandex_delivery.sort_near")}</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="count_points">{__("yandex_delivery.display_count_pickuppoints")}</label>
    <div class="controls">
        <input id="count_near_points" type="text" name="shipping_data[service_params][count_points]" size="30" value="{$shipping.service_params.count_points}" />
    </div>
</div>

<div class="control-group">
    <label for="ship_yandex_delivery_delivery" class="control-label">{__("yandex_delivery_shipping")}:</label>
    <div class="controls">
        {foreach from=$deliveries item="delivery" key="id"}
        <label class="checkbox inline" for="delivery_{$id}">
            <input type="checkbox" name="shipping_data[service_params][deliveries][]" id="delivery_{$id}"{if array_key_exists($id, $deliveries_select)} checked="checked"{/if} value="{$id}"/>
            {$delivery}
        </label>
        {/foreach}
    </div>
</div>

<div class="control-group">
    <label for="ship_yandex_delivery_display_type" class="control-label">{__("yandex_delivery_ship_display_type")}:</label>
    <div class="controls">
        <select id="ship_yandex_delivery_display_type" name="shipping_data[service_params][display_type]">
            <option value="CMS" {if $shipping.service_params.display_type == "CMS"}selected="selected"{/if}>{__("yandex_delivery_cms")}</option>
        </select>
    </div>
</div>

</fieldset>