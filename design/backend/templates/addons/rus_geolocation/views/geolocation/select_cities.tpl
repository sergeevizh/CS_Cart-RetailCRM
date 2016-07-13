
{capture name="mainbox"}

{include file="views/profiles/components/profiles_scripts.tpl"}

<form action="{""|fn_url}" method="post" name="cities_form" class="{if $runtime.company_id} cm-hide-inputs{/if}">

{include file="common/pagination.tpl" save_current_page=true save_current_url=true div_id="cities_form"}

{if $select_cities}
    <table width="100%" class="table table-middle">
    <thead>
    <tr>
        <th width="1%">{include file="common/check_items.tpl"}</th>
        <th width="40%">{__("city")}</th>
        <th width="20%">{__("country")}/{__("state")}</th>
        <th width="5%">&nbsp;</th>
    </tr>
    </thead>
    {foreach from=$select_cities item=city}
        <input type="hidden" name="cities[{$city.city_code}][country]" value="{$city.country}"/>
        <input type="hidden" name="cities[{$city.city_code}][state]" value="{$city.state}"/>
        <tr class="cm-row-status-{$city.status|lower}">
            <td>
                <input type="checkbox" name="city_codes[]" value="{$city.city_code}" class="checkbox cm-item" />
            </td>
            <td>{$city.city}</td>
            <td>
                <span class="muted"><small>{$city.country}</small></span>
                </br>
                <span class="muted"><small>{$city.state}</small></span>
            </td>
            <td class="nowrap">
                {capture name="tools_list"}
                    <li>{btn type="list" class="cm-confirm cm-post" text=__("delete") href="geolocation.delete?city_code=`$city.city_code`"}</li>
                {/capture}
                <div class="hidden-tools">
                    {dropdown content=$smarty.capture.tools_list}
                </div>
            </td>
        </tr>
    {/foreach}
    </table>
{else}
    <p class="no-items">{__("no_data")}</p>
{/if}

{include file="common/pagination.tpl" div_id="cities_form"}

</form>

{capture name="buttons"}
    {capture name="tools_list"}
            {if $select_cities}
                <li>{btn type="delete_selected" dispatch="dispatch[geolocation.m_delete]" form="cities_form"}</li>
            {/if}
    {/capture}
    {dropdown content=$smarty.capture.tools_list}
{/capture}

{if ($list_cities)}
    {capture name="adv_buttons"}
        {include file="common/popupbox.tpl" id="new_city" text=__("addon.rus_geolocation.add_cities") href="geolocation.list_geocities"|fn_url title=__("addon.rus_geolocation.add_cities") act="general" icon="icon-plus"}
    {/capture}
{/if}

{/capture}

{include file="common/mainbox.tpl" title=__("addon.rus_geolocation.cities") content=$smarty.capture.mainbox adv_buttons=$smarty.capture.adv_buttons buttons=$smarty.capture.buttons sidebar=$smarty.capture.sidebar select_languages=true}
