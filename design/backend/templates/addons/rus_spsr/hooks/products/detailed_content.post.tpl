
{include file="common/subheader.tpl" title=__("shippings.spsr.service_label") target="#spsr_product_type"}
<div id="spsr_product_type" class="collapsed in">
    <div class="control-group">
        <label class="control-label" for="spsr_product_type">{__("shippings.spsr.product_type")}:</label>
        <div class="controls">
        <select name="product_data[spsr_product_type]" id="spsr_necesserytime">
            {foreach from=$type_products item="type"}
                <option {if $product_data.spsr_product_type == $type.Value}selected="selected"{/if} value="{$type.Value}">{$type.Name}</option>
            {/foreach}
        </select>
        </div>
    </div>
</div>