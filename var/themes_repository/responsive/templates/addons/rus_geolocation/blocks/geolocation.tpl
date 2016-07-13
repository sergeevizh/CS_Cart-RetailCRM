{** block-description:tmpl_geolocation **}

{assign var="city" value=$smarty.request.city}
<div class="ty-geolocation" id="geolocation_city_link">
    {script src="js/addons/rus_geolocation/func.js"}
    <input type="hidden" name="result_ids" value="geolocation_city_link">

    <div>
        <input type="hidden" name="data_geolocation[geocity]" id="geocity" value="{$smarty.session.geocity}" />
        <label class="ty-geolocation-head-city">{__("addon.rus_geolocation.find_city")}: 

        {if !$smarty.session.geocity}
            <a id="geolocation_link" class="cm-dialog-opener hidden cm-dialog-auto-size" data-ca-target-id="geolocation_dialog">{__("addon.rus_geolocation.select_city")}</a>
        {else}
            <a class="cm-dialog-opener cm-dialog-auto-size" data-ca-target-id="geolocation_dialog">{$smarty.session.geocity}</a>
        {/if}
        </label>
    </div>

    <div class="ty-geolocation-link">
        
    </div>

    <div class="hidden" title="{__("addon.rus_geolocation.select_geocities")}" id="geolocation_dialog">
        <div id="geolocation_block">
            <form name="geolocation_form" action="{""|fn_url}" method="post" class="ty-form-geolocation-city cm_ajax cm-ajax-full-render cm-form-dialog-closer">
                <input type="hidden" name="result_ids" value="geolocation_block">
                <input type="hidden" name="geolocation_provider" value="{$addons.rus_geolocation.geolocation_provider}" />
                <input type="hidden" name="pull_url_geolocation" value="{$config.current_url}">
                <input type="hidden" id="default_city" name="default_city" value="{$settings.General.default_city}">

                <input type="hidden" name="data_geolocation[geocity]" id="geocity" value="{$smarty.session.geocity}" />
                <div class="ty-control-group ty-geolocation-city">
                    <label  class="ty-control-group__label">{__("addon.rus_geolocation.find_city")}</label>
                    <input type="text" id="auto_geocity" class="ty-search-block__input" name="data_geolocation[geocity]" {if $smarty.session.geocity}value="{$smarty.session.geocity}"{else}value="{$geocity}"{/if} x-autocomplete="auto_geocity" autocomplete="on" />
                </div>
                <hr />

                {if $data_cities}
                    {assign var="cities_count" value=$data_cities|count}
                    {assign var="display_count" value=0}
                    <div class="ty-control-group">
                        <table class="ty-table-cities">
                            <tr>
                        {foreach from=$data_cities item="data_city"}
                            {if ($display_count==0)}
                                <td class="ty-table-cities__item">
                                    <ul>
                            {/if}
                                        <li>
                                            <a class="cm-dialog-closer" id="choose-list-city" onclick="fn_get_geolocation_choose_city('{$data_city.city}', 'geolocation_city_link')">{$data_city.city}</a>
                                        </li>

                            {$display_count = $display_count + 1}

                            {if (($display_count==22) || ($display_count==$cities_count))}
                                {$display_count = 0}
                            {/if}

                            {if ($display_count==0)}
                                    </ul>
                                </td>
                            {/if}
                        {/foreach}
                            </tr>
                        </table>
                    </div>
                {/if}

                <div class="buttons-container clearfix buttons-container-picker">
                    <a class="ty-btn__primary ty-btn__big ty-btn cm-dialog-closer" onclick="fn_get_geolocation_button_city('{$smarty.session.geocity}')">{__("addon.rus_geolocation.choose_cities")}</a>
                </div>

            </form>
        <!--geolocation_block--></div>
    </div>
<!--geolocation_city_link--></div>
