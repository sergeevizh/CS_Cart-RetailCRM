
<form action="{""|fn_url}" method="post" name="add_cities_form" class="form-horizontal form-edit">

{include file="common/pagination.tpl" save_current_page=true save_current_url=true div_id="add_cities_form"}

{if $list_cities}
    <table width="100%" class="table table-middle">
    <thead>
    <tr>
        <th width="1%">{include file="common/check_items.tpl"}</th>
        <th width="40%">{__("city")}</th>
        <th width="20%">{__("country")}/{__("state")}</th>
    </tr>
    </thead>
    {foreach from=$list_cities item=list_city}
        <input type="hidden" name="cities[{$list_city.city_code}][country]" value="{$list_city.country}"/>
        <input type="hidden" name="cities[{$list_city.city_code}][state]" value="{$list_city.state}"/>
        <tr class="cm-row-status-{$list_city.status|lower}">
            <td>
                <input type="checkbox" name="add_city_codes[]" value="{$list_city.city_code}" class="checkbox cm-item" />
            </td>
            <td>{$list_city.city}</td>
            <td>
                <span class="muted"><small>{$list_city.country}</small></span>
                </br>
                <span class="muted"><small>{$list_city.state}</small></span>
            </td>
        </tr>
    {/foreach}
    </table>
{else}
    <p class="no-items">{__("no_data")}</p>
{/if}

{include file="common/pagination.tpl" div_id="add_cities_form"}

<div class="buttons-container">
    {include file="buttons/save_cancel.tpl" create=true but_name="dispatch[geolocation.add_cities]" cancel_action="close"}
</div>

</form>
