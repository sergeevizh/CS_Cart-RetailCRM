{script src="js/addons/rus_spsr/spsr.js"}

<div class="control-group">
    <div class="control">
        <div class="pull-left">
            <i id="on_{$value}" class="hand cm-spsr_form_call exicon-expand"></i>
            <i title="{__("collapse_sublist_of_items")}" id="off_{$value}" class="hand cm-spsr_form_call hidden exicon-collapse"></i>
        </div>
        <h4>{__("shippings.spsr.params_shipping_spsr")}:</h4>
    </div>
    <div class="row-more row-gray hidden" id="{$value}" valign="top">
        <table width="100%" class="table table-middle">
            <tr>
                <td class="left">
                    <label class="control-label" for="spsr_invoice_additional_paid_by_receiver">{__("shippings.spsr.invoice_additional_paid_by_receiver")} {include file="common/tooltip.tpl" tooltip=__("shippings.spsr.invoice_additional_paid_by_receiver.tooltip")}:</label>
                    <div class="controls">
                        <select name="settings_shipping_spsr[{$value}][paid_by_receiver]" id="spsr_invoice_additional_paid_by_receiver">
                            <option value="1" {if $shipping.service_params.paid_by_receiver == '1'} selected="selected"{/if}>{__("yes")}</option>
                            <option value="0" {if $shipping.service_params.paid_by_receiver == '0'} selected="selected"{/if}>{__("no")}</option>
                        </select>
                    </div>
                </td>
                <td>
                    <label class="control-label" for="spsr_invoice_additional_cod">{__("shippings.spsr.invoice_additional_cod")} {include file="common/tooltip.tpl" tooltip=__("shippings.spsr.invoice_additional_cod.tooltip")}:</label>
                    <div class="controls">
                        <select name="settings_shipping_spsr[{$value}][cod]" id="spsr_invoice_additional_cod">
                            <option value="1" {if $shipping.service_params.cod == '1'} selected="selected"{/if}>{__("yes")}</option>
                            <option value="0" {if $shipping.service_params.cod == '0'} selected="selected"{/if}>{__("no")}</option>
                        </select>
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <label class="control-label" for="spsr_invoice_insurance_type">{__("shippings.spsr.invoice_insurance_type")} {include file="common/tooltip.tpl" tooltip=__("shippings.spsr.invoice_insurance_type.tooltip")}:</label>
                    <div class="controls">
                        <select name="settings_shipping_spsr[{$value}][insurance_type]" id="spsr_invoice_insurance_type">
                            <option value="INS" {if $shipping.service_params.insurance_type == 'INS'} selected="selected"{/if}>{__("shippings.spsr.insurance_type.ins")}</option>
                            <option value="VAL" {if $shipping.service_params.insurance_type == 'VAL'} selected="selected"{/if}>{__("shippings.spsr.insurance_type.val")}</option>
                        </select>
                    </div>
                </td>
                <td>
                    <label class="control-label" for="spsr_invoice_additional_agreed_delivery">{__("shippings.spsr.invoice_additional_agreed_delivery")} {include file="common/tooltip.tpl" tooltip=__("shippings.spsr.invoice_additional_agreed_delivery.tooltip")}:</label>
                    <div class="controls">
                        <select name="settings_shipping_spsr[{$value}][agreed_delivery]" id="spsr_invoice_additional_agreed_delivery">
                            <option value="1" {if $shipping.service_params.agreed_delivery == '1'} selected="selected"{/if}>{__("yes")}</option>
                            <option value="0" {if $shipping.service_params.agreed_delivery == '0'} selected="selected"{/if}>{__("no")}</option>
                        </select>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="left">
                    <label class="control-label" for="spsr_invoice_additional_try_on">{__("shippings.spsr.invoice_additional_try_on")} {include file="common/tooltip.tpl" tooltip=__("shippings.spsr.invoice_additional_try_on.tooltip")}:</label>
                    <div class="controls">
                        <select name="settings_shipping_spsr[{$value}][try_on]" id="spsr_invoice_additional_try_on">
                            <option value="1" {if $shipping.service_params.try_on == '1'} selected="selected"{/if}>{__("yes")}</option>
                            <option value="0" {if $shipping.service_params.try_on == '0'} selected="selected"{/if}>{__("no")}</option>
                        </select>
                    </div>
                </td>
                <td>
                    <label class="control-label" for="spsr_invoice_additional_by_hand">{__("shippings.spsr.invoice_additional_by_hand")} {include file="common/tooltip.tpl" tooltip=__("shippings.spsr.invoice_additional_by_hand.tooltip")}:</label>
                    <div class="controls">
                        <select name="settings_shipping_spsr[{$value}][by_hand]" id="spsr_invoice_additional_by_hand">
                            <option value="1" {if $shipping.service_params.by_hand == '1'} selected="selected"{/if}>{__("yes")}</option>
                            <option value="0" {if $shipping.service_params.by_hand == '0'} selected="selected"{/if}>{__("no")}</option>
                        </select>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="left">
                    <label class="control-label" for="spsr_invoice_sms_to_shipper">{__("shippings.spsr.invoice_sms_to_shipper")} {include file="common/tooltip.tpl" tooltip=__("shippings.spsr.invoice_sms_to_shipper.tooltip")}:</label>
                    <div class="controls">
                        <select name="settings_shipping_spsr[{$value}][sms_to_shipper]" id="spsr_invoice_sms_to_shipper">
                            <option value="1" {if $shipping.service_params.sms_to_shipper == '1'} selected="selected"{/if}>{__("yes")}</option>
                            <option value="0" {if $shipping.service_params.sms_to_shipper == '0'} selected="selected"{/if}>{__("no")}</option>
                        </select>
                    </div>
                </td>
                <td>
                    <label class="control-label" for="spsr_invoice_sms_to_receiver">{__("shippings.spsr.invoice_sms_to_receiver")} {include file="common/tooltip.tpl" tooltip=__("shippings.spsr.invoice_sms_to_receiver.tooltip")}:</label>
                    <div class="controls">
                        <select name="settings_shipping_spsr[{$value}][sms_to_receiver]" id="spsr_invoice_sms_to_receiver">
                            <option value="1" {if $shipping.service_params.sms_to_receiver == '1'} selected="selected"{/if}>{__("yes")}</option>
                            <option value="0" {if $shipping.service_params.sms_to_receiver == '0'} selected="selected"{/if}>{__("no")}</option>
                        </select>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="left">
                    <label class="control-label" for="spsr_invoice_additional_idc">{__("shippings.spsr.invoice_additional_idc")} {include file="common/tooltip.tpl" tooltip=__("shippings.spsr.invoice_additional_idc.tooltip")}:</label>
                    <div class="controls">
                        <select name="settings_shipping_spsr[{$value}][idc]" id="spsr_invoice_additional_idc">
                            <option value="1" {if $shipping.service_params.idc == '1'} selected="selected"{/if}>{__("yes")}</option>
                            <option value="0" {if $shipping.service_params.idc == '0'} selected="selected"{/if}>{__("no")}</option>
                        </select>
                    </div>
                </td>
                <td>
                    <label class="control-label" for="spsr_invoice_additional_return_doc">{__("shippings.spsr.invoice_additional_return_doc")} {include file="common/tooltip.tpl" tooltip=__("shippings.spsr.invoice_additional_return_doc.tooltip")}:</label>
                    <div class="controls">
                        <select name="settings_shipping_spsr[{$value}][return_doc]" id="spsr_invoice_additional_return_doc">
                            <option value="0" {if $shipping.service_params.return_doc == '0'} selected="selected"{/if}>{__("no")}</option>
                            <option value="1" {if $shipping.service_params.return_doc == '1'} selected="selected"{/if}>{__("yes")}</option>
                        </select>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="left">
                    <label class="control-label" for="spsr_to_be_called_for">{__("shippings.spsr.to_be_called_for")}:</label>
                    <div class="controls">
                        <select name="settings_shipping_spsr[{$value}][to_be_called_for]" id="spsr_to_be_called_for">
                            <option value="0" {if $shipping.service_params.to_be_called_for == "0"} selected="selected"{/if}>{__("no")}</option>
                            <option value="1" {if $shipping.service_params.to_be_called_for == "1"} selected="selected"{/if}>{__("yes")}</option>
                        </select>
                    </div>
                </td>
                <td></td>
            </tr>
        </table>
    </div>
</div>