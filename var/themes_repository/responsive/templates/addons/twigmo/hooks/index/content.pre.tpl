{assign var="state" value=$smarty.session.twg_state}
{if $state.twg_can_be_used and !$state.mobile_link_closed}
<div class="mobile-avail-notice">
    <a href="{$config.current_url|fn_query_remove:"mobile":"auto":"desktop"|fn_link_attach:"mobile"}">
        {__('twg_visit_our_mobile_store')}
    </a>

    {if $state.device == "android" and $state.url_on_googleplay}
        <a href="{$state.url_on_googleplay}">{__('twg_app_for_android')}</a>
    {/if}
    <span id="close_notification_mobile_avail_notice" class="cm-notification-close hand close" title="Close" /><i class="ty-icon-cancel"></i></span>
</div>
{/if}
