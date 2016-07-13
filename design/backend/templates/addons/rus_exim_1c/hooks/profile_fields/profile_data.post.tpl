{assign var="_export_1c" value="`$key`_export_1c"}
<div class="control-group">
    <label class="control-label">{__("rus_exim_commerceml.export_field_commerceml")} {include file="common/tooltip.tpl" tooltip=__("rus_exim_commerceml.tooltip.export_field_commerceml")}:</label>
    <div class="controls">
        <input type="hidden" name="field_data[{$_export_1c}]" value="{if $field.field_name == "email"}Y{else}N{/if}" />
        <input type="checkbox" name="field_data[{$_export_1c}]" value="Y" {if $field.$_export_1c == "Y"}checked="checked"{/if} class="cm-switch-availability checkbox" {if $field.field_name == "email"}disabled="disabled"{/if} />
    </div>
</div>