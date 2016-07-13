<div id="settings_block_import">


        <input type="hidden" name="addon" value="yml_export">
        <input type="hidden" name="redirect_url" value="admin.php?dispatch=addons.manage">

        <div id="content_yml_export_general" class="settings">

            <h4 class="subheader   hand" data-toggle="collapse" data-target="#collapsable_addon_option_newsletters_elm_advanced_mailing_server_options">
                {__("yml_export.data_import_from_addon")}
                <span class="exicon-collapse"></span></h4>


            <div id="collapsable_addon_option_newsletters_elm_advanced_mailing_server_options" class="in collapse">
                <fieldset>
                    {if $yandex_market_on}
                    <div id="container_addon_option_yml_export_products" class="control-group setting-wide">
                        <label for="addon_option_yml_export_products" class="control-label ">{__("yml_export.import_products")}:</label>

                        <div class="controls">
                            <input type="hidden" name="yml_import[import_products]" value="N">
                            <input id="addon_option_yml_export_products" type="checkbox" name="yml_import[import_products]" value="Y">
                        </div>
                    </div>

                    <div id="container_addon_option_yml_export_categories" class="control-group setting-wide">
                        <label for="addon_option_yml_export_categories" class="control-label ">{__("yml_export.import_categories")}:</label>

                        <div class="controls">
                            <input type="hidden" name="yml_import[import_categories]" value="N">
                            <input id="addon_option_yml_export_categories" type="checkbox" name="yml_import[import_categories]" value="Y">
                        </div>
                    </div>

                    <div id="container_addon_option_yml_export_categories" class="control-group setting-wide">
                        <label for="addon_option_yml_export_settings" class="control-label ">{__("yml_export.import_settings")}:</label>

                        <div class="controls">
                            <input type="hidden" name="yml_import[import_settings]" value="N">
                            <input id="addon_option_yml_export_settings" type="checkbox" name="yml_import[import_settings]" value="Y">
                        </div>
                    </div>

                    <div class="control-group setting-wide">
                        {include file="buttons/button.tpl" but_role="submit" but_name="dispatch[yml_import.import]" but_text=__("yml_export.import")}
                    </div>
                    {else}
                        <div class="control-group setting-wide">
                            {__('yml_export.yandex_market_not_installed')}
                        </div>
                    {/if}
                </fieldset>
            </div>

        </div>
</div>
