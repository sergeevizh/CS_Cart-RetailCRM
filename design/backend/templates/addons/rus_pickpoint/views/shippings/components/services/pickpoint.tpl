<fieldset>
<div class="control-group">
    <label class="control-label" for="pickpoint_width">{__("ship_width")}</label>
    <div class="controls">
        <input id="pickpoint_width" type="text" name="shipping_data[service_params][pickpoint_width]" size="30" value="{$shipping.service_params.pickpoint_width}" />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="pickpoint_height">{__("ship_height")}</label>
    <div class="controls">
        <input id="pickpoint_height" type="text" name="shipping_data[service_params][pickpoint_height]" size="30" value="{$shipping.service_params.pickpoint_height}" />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="pickpoint_length">{__("ship_length")}</label>
    <div class="controls">
        <input id="pickpoint_length" type="text" name="shipping_data[service_params][pickpoint_length]" size="30" value="{$shipping.service_params.pickpoint_length}" />
    </div>
</div>
</fieldset>