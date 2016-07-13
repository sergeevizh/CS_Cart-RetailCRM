{script src="js/lib/maskedinput/jquery.maskedinput.min.js"}
{script src="js/lib/inputmask/jquery.inputmask.min.js"}
{script src="js/addons/rus_sdek/sdek.js"}
{if !empty($data_shipments)}
    <div id="content_sdek_orders">
        {foreach from=$data_shipments item=shipment key="shipment_id"}
            <form action="{""|fn_url}" method="post" name="sdek_form_{$shipment_id}" class="cm-processed-form cm-check-changes">
                <input type="hidden" name="order_id" value="{$order_id}" />
                <input type="hidden" name="add_sdek_info[{$shipment_id}][Order][RecCityCode]" value="{$rec_city_code}" />
                <input type="hidden" name="add_sdek_info[{$shipment_id}][Order][SendCityCode]" value="{$shipment.send_city_code}" />
                <div class="control-group">
                    <div class="control">
                        <div class="pull-left">
                            <i id="on_{$shipment_id}" class="hand cm-sdek_form_call exicon-expand"></i>
                            <i title="{__("collapse_sublist_of_items")}" id="off_{$shipment_id}" class="hand cm-sdek_form_call hidden exicon-collapse"></i>
                        </div>
                        <h4>{__("shipment")}: <a class="underlined" href="{"shipments.details?shipment_id=`$shipment_id`"|fn_url}" target="_blank"><span>#{$shipment_id} ({__("details")})</span></a></h4>
                    </div>

                    <table width="100%" class="table table-middle">
                    <thead>
                    <tr>
                        <th width="35%" class="shift-left">{__("sdek.sdek_address_shipping")}</th>
                        <th width="20%">{__("sdek.sdek_tariff")}</th>
                        <th width="25%">
                            {if !empty($shipment.register_id)}
                                {if !empty($shipment.notes)}
                                    {__("comment")}
                                {/if}
                            {else}
                                {__("comment")}
                            {/if}
                        </th>
                        <th width="5%">{if !$shipment.register_id}{__("shipping_cost")}{/if}</th>
                        <th width="15%">&nbsp;</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr class="cm-row-status" valign="top" >
                        <td>
                            {if (empty($shipment.offices))}
                                <input type="text" name="add_sdek_info[{$shipment_id}][Address][Street]" value="{$shipment.address}" />
                                <input type="hidden" name="add_sdek_info[{$shipment_id}][Address][House]" value="-" />
                                <input type="hidden" name="add_sdek_info[{$shipment_id}][Address][Flat]" value="-" />
                            {else}
                                <select name="add_sdek_info[{$shipment_id}][Address][PvzCode]" class="input-slarge" id="item_modifier_type">
                                    {foreach from=$shipment.offices item=address_shipping}
                                        <option value="{$address_shipping.Code}" {if $address_shipping.Code == $shipment.address_pvz}selected="selected"{/if}>{$address_shipping.Address}</option>
                                    {/foreach}
                                </select>
                            {/if}
                        </td>
                        <td class="left nowrap {$no_hide_input}">
                            <input type="hidden" name="add_sdek_info[{$shipment_id}][Order][TariffTypeCode]" value="{$shipment.tariff_id}" />
                            {$shipment.shipping}
                        </td>
                        <td class="left nowrap">
                            {if !empty($shipment.register_id)}
                                {$shipment.notes}
                            {else}
                                <textarea class="input-textarea checkout-textarea" name="add_sdek_info[{$shipment_id}][Order][Comment]" cols="50" rows="1" value="">{$shipment.comments}</textarea>
                            {/if}
                        </td>
                        <td class="right nowrap">
                            {if $shipment.register_id}
                                <div class="pull-right">
                                    {capture name="tools_list"}
                                        <li>{btn type="list" text=__("sdek.update_status") dispatch="dispatch[orders.sdek_order_status]" form="sdek_form_`$shipment_id`"}</li>
                                        <li>{btn type="list" text=__("sdek.new_schedule") dispatch="dispatch[orders.sdek_call_recipient]" form="sdek_form_`$shipment_id`"}</li>
                                        <li>{btn type="list" text=__("sdek.call_courier") dispatch="dispatch[orders.sdek_call_courier]" form="sdek_form_`$shipment_id`"}</li>
                                        <li>{btn type="list" text=__("delete") dispatch="dispatch[orders.sdek_order_delete]" form="sdek_form_`$shipment_id`"}</li>
                                    {/capture}
                                    {dropdown content=$smarty.capture.tools_list}
                                </div>
                            {else}
                                <input type="text" name="add_sdek_info[{$shipment_id}][Order][DeliveryRecipientCost]" value="{$shipment.delivery_cost}" class="input-mini" size="6"/>
                            {/if}
                        </td>
                        <td class="right nowrap">
                            {if !$shipment.register_id}
                                {include file="buttons/button.tpl" but_role="submit" but_name="dispatch[orders.sdek_order_delivery]" but_text=__("send") but_target_form="sdek_form_`$shipment_id`"}
                            {else}
                                {$ticket_href = "{"orders.sdek_get_ticket?order_id=`$order_info.order_id`&shipment_id=`$shipment_id`"|fn_url}"}

                                {include file="buttons/button.tpl" but_role="submit-link" but_href=$ticket_href but_text=__("sdek.receipt_order") but_meta="cm-no-ajax"}
                            {/if}
                        </td>
                    </tr>
                    {if (empty($shipment.register_id))}
                    <tr>
                        <td>
                            <div class="control-group">
                                <label class="control-label" for="cash_delivery">{__("sdek.cash_on_delivery")}</label>
                                <div class="controls">
                                    <input id="cash_delivery" type="text" name="add_sdek_info[{$shipment_id}][CashDelivery]" value="0" class="input-mini" size="6"/>
                                </div>
                            </div>
                        </td>
                        <td colspan="2">
                            <div class="control-group">
                                <label class="control-label" for="use_imposed">{__("shipping.sdek.use_imposed")}</label>
                                <div class="controls">
                                    <input id="use_imposed" type="checkbox" name="add_sdek_info[{$shipment_id}][use_imposed]" value="Y"/>
                                </div>
                            </div>
                        </td>
                        <td colspan="2">
                            <div class="control-group">
                                <label class="control-label" for="use_product">{__("shipping.sdek.use_product")}</label>
                                <div class="controls">
                                    <input type="hidden" name="add_sdek_info[{$shipment_id}][use_product]" value="N" />
                                    <input id="use_product" type="checkbox" name="add_sdek_info[{$shipment_id}][use_product]" checked="checked" value="Y"/>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr class="cm-row-status" valign="top" >
                        <td colspan="5">
                            <div class="control-group">
                                <label class="control-label" for="barcode">{__("shippings.sdek.barcode")}</label>
                                <div class="controls">
                                    <input id="barcode" type="text" name="add_sdek_info[{$shipment_id}][barcode]" value="{$shipment_id}" />
                                </div>
                            </div>
                        </td>
                    </tr>
                    {/if}
                    </tbody>
                    </table>

                    <div class="row-more row-gray hidden" id="{$shipment_id}" valign="top">
                        <h4>{__("sdek.new_schedule")}</h4>
                        <table width="100%" class="table table-middle">
                            <thead class="cm-first-sibling">
                            <tr>
                                <th width="25%">{__("recipient")}</th>
                                <th width="15%">{__("phone")}</th>
                                <th width="10%">{__("sdek.delivery_recipient_cost")}</th>
                                <th width="15%">{__("sdek.date_delivery")}</th>
                                <th width="35%">{__("sdek.time")}</th>
                            </tr>
                            </thead>
                            <tbody>
                                <tr id="box_add_sdek_schedule_{$shipment_id}">
                                    <td><input type="text" name="add_sdek_info[{$shipment_id}][Schedule][RecipientName]" value="{$shipment.new_schedules.recipient_name}" /></td>
                                    <td><input type="text" name="add_sdek_info[{$shipment_id}][Schedule][Phone]" value="{$shipment.new_schedules.phone}" size="10" class="input-small" /></td>
                                    <td><input id="recipient_cost" type="text" name="add_sdek_info[{$shipment_id}][Schedule][DeliveryRecipientCost]" value="{$shipment.new_schedules.recipient_cost}" size="10" class="input-small" /></td>
                                    <td>{include file="common/calendar.tpl" date_id="elm_date_call_recipient_`$shipment_id`" date_name="add_sdek_info[{$shipment_id}][Schedule][Date]" date_val="{$shipment.new_schedules.date}" start_year=$settings.Company.company_start_year}</td>
                                    <td><input id="timebeg_{$shipments_id}" class="input-small cm-mask-time" type="text" name="add_sdek_info[{$shipment_id}][Schedule][TimeBeg]" value="{$shipment.new_schedules.timebag}" size="6" /> &mdash; <input id="timeend_{$shipments_id}" class="input-small cm-mask-time" type="text" name="add_sdek_info[{$shipment_id}][Schedule][TimeEnd]" value="{$shipment.new_schedules.timeend}" size="6" /></td>
                                </tr>
                                <tr>
                                    <td colspan="5">
                                        <label for="elm_comment">{__("comment")}</label>
                                        <textarea id="elm_comment" class="input-textarea checkout-textarea" name="add_sdek_info[{$shipment_id}][Schedule][Comment]" cols="40" rows="2">{$shipment.new_schedules.call_comment}</textarea>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <h4>{__("sdek.call_courier")}</h4>
                        <table width="100%" class="table table-middle">
                            <thead class="cm-first-sibling">
                            <tr>
                                <th width="10%">{__("date")}</th>
                                <th width="40%">{__("sdek.time_courier")}</th>
                                <th width="40%">{__("sdek.time_lunch")}</th>
                                <th width="10%">{__("comment")}</th>
                            </tr>
                            </thead>
                            <tbody>
                                {foreach from=$shipment.call_couriers item=call_courier}
                                    {if (!empty($call_courier.call_courier_id))}
                                        <tr id="box_add_sdek_call_couriers_{$shipment_id}">
                                            <td>{$call_courier.call_courier_date}</td>
                                            <td>{$call_courier.timebag} &mdash; {$call_courier.timeend}</td>
                                            <td>{$call_courier.lunch_timebag} &mdash; {$call_courier.lunch_timeend}</td>
                                            <td>{$call_courier.comment_courier}</td>
                                        </tr>
                                    {else}
                                        <tr id="box_add_sdek_call_courier_{$shipment_id}">
                                            <td>{include file="common/calendar.tpl" date_id="elm_date_courier_`$shipment_id`" date_name="add_sdek_info[{$shipment_id}][CallCourier][Date]" date_val="{$call_courier.date}" start_year=$settings.Company.company_start_year}</td>
                                            <td><input id="timebeg_{$shipments_id}" class="input-small cm-mask-time" type="text" name="add_sdek_info[{$shipment_id}][CallCourier][TimeBeg]" value="" size="6" /> &mdash; <input id="timeend_{$shipments_id}" class="input-small cm-mask-time" type="text" name="add_sdek_info[{$shipment_id}][CallCourier][TimeEnd]" value="" size="6" /></td>
                                            <td><input id="timebeg_{$shipments_id}" class="input-small cm-mask-time" type="text" name="add_sdek_info[{$shipment_id}][CallCourier][LunchBeg]" value="" size="6" /> &mdash; <input id="timeend_{$shipments_id}" class="input-small cm-mask-time" type="text" name="add_sdek_info[{$shipment_id}][CallCourier][LunchEnd]" value="" size="6" /></td>
                                            <td><textarea id="elm_comment" class="input-textarea checkout-textarea" name="add_sdek_info[{$shipment_id}][CallCourier][Comment]" cols="40" rows="2"></textarea></td>
                                        </tr>
                                    {/if}
                                {/foreach}
                            </tbody>
                        </table>
                    </div>

                    {if !empty($shipment.sdek_status)}
                        {include file="common/subheader.tpl" title=__("shippings.sdek.status_title") target="#status_information_{$shipment_id}"}
                        <div id="status_information_{$shipment_id}" class="collapse">
                            <table width="100%" class="table table-middle" >
                            <tr>
                                <td>
                                    {__("code")}
                                </td>
                                <td>
                                    {__("date")}
                                </td>
                                <td>
                                    {__("status")}
                                </td>
                                <td>
                                    {__("sdek.lang_city")}
                                </td>
                            </tr>
                            {foreach from=$shipment.sdek_status item=d_status}
                                <tr>
                                    <td>
                                        {$d_status.id}
                                    </td>
                                    <td>
                                        {$d_status.date}
                                    </td>
                                    <td>
                                        {$d_status.status}
                                    </td>
                                    <td>
                                        {$d_status.city}
                                    </td>
                                </tr>
                            {/foreach}
                            </table>
                        </div>
                    {/if}
                </div>
                <hr />
            </form>
        {/foreach}
    </div>
{/if}
