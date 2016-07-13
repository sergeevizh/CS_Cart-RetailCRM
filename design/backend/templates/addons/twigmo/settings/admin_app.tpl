<div id="twg_admin_app">

{include file="addons/twigmo/settings/components/contact_twigmo_support.tpl"}

{if !$hide_header}
    {include file="common/subheader.tpl" title=__("twgadmin_mobile_admin_application")}
{/if}

<fieldset>

{assign var="img_lang" value="en"}
{if $smarty.const.CART_LANGUAGE == 'ru'}
    {assign var="img_lang" value="ru"}
{/if}

<div class="control-group form-field">
    <label class="control-label">{__("twgadmin_download_app")}:</label>
    <div class="controls">
        <a target="_blank" href="//itunes.apple.com/us/app/twigmo-admin-2.0/id895364611">
            <span class="twg-app-store-btn float-left"><img src="{$images_dir}/addons/twigmo/images/buttons/{$img_lang}/app-store.png"></span>
        </a>
        <a target="_blank" href="//play.google.com/store/apps/details?id=com.simtech.twigmoAdmin">
            <span class="twg-google-play-btn float-left"><img src="{$images_dir}/addons/twigmo/images/buttons/{$img_lang}/google-play.png"></span>
        </a>
        {if !$is_on_saas}
            <div class="twg-app-label">{__("twgadmin_download_app_hint")}</div>
        {/if}
        {if !$connected_access_id}
            <div class="twg-app-label">{__("twgadmin_connect_to_first_ult")}</div>
        {/if}
    </div>
</div>

{if $connected_access_id}
    <div class="control-group form-field">
        <label class="control-label">{__("twgadmin_qr_for_admin")}:</label>
        <div class="controls">
            <img style="width: 200px" src="{'twigmo_admin_app.show_qr'|fn_url}" />
            <div class="twg-app-label">{__("twgadmin_qr_for_admin_comment", ["[access_id]" => $connected_access_id])}</div>
        </div>
    </div>
{/if}

</fieldset>

<!--twg_admin_app--></div>
