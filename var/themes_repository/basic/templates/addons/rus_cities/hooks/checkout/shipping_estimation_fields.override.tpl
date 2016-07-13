<div class="control-group">
    <label for="{$prefix}elm_country{$id_suffix}">{__("country")}</label>
    <select id="{$prefix}elm_country{$id_suffix}" class="cm-country cm-location-estimation{$class_suffix}" name="customer_location[country]">
        <option value="">- {__("select_country")} -</option>
        {assign var="countries" value=1|fn_get_simple_countries}
        {foreach from=$countries item="country" key="code"}
        <option value="{$code}" {if ($cart.user_data.s_country == $code) || (!$cart.user_data.s_country && $code == $settings.General.default_country)}selected="selected"{/if}>{$country}</option>
        {/foreach}
    </select>
</div>

{assign var="_state" value=$cart.user_data.s_state|default:$settings.General.default_state}
<div class="control-group">
    <label for="{$prefix}elm_state{$id_suffix}">{__("state")}</label>
    <select class="cm-state cm-location-estimation{$class_suffix} {if !$states[$cart.user_data.s_country]}hidden{/if}" id="{$prefix}elm_state{$id_suffix}" name="customer_location[state]">
        <option value="">- {__("select_state")} -</option>
        {foreach $states[$cart.user_data.s_country] as $state}
            <option value="{$state.code}" {if $state.code == $_state}selected="selected"{/if}>{$state.state}</option>
        {foreachelse}
            <option label="" value="">- {__("select_state")} -</option>
        {/foreach}
    </select>

    <input type="text" class="cm-state cm-location-estimation{$class_suffix} input-text {if $states[$cart.user_data.s_country]}hidden{/if}" id="{$prefix}elm_state{$id_suffix}_d" name="customer_location[state]" size="{if $location != "sidebox"}32{else}20{/if}" maxlength="64" value="{$_state}" {if $states[$cart.user_data.s_country]}disabled="disabled"{/if} />
</div>

<div id="change_city">

    {if $cities}
        <div class="control-group">
            <label>{__("city")}</label>
            <select class="" id="elm_city" name="customer_location[city]">
                <option label="" value="">-- {__("select_city")} --</option>
                {foreach from=$cities item="city"}
                    {if !$client_city && ($cart.user_data.s_city == $city.city || $city.active == "Y")}
                        {assign var="input_city" value=$city.city}
                    {else}
                        {assign var="input_city" value=$client_city}
                    {/if}
                    <option {if !$client_city && ($cart.user_data.s_city == $city.city || $city.active == "Y")}selected="selected"{/if} value="{$city.city}">{$city.city}</option>
                {/foreach} 
                 <option label="" {if $client_city}selected="selected"{/if} value="client_city">-- {__("other_town")} --</option>
            </select>
        </div>

        <div id="client_city" class="control-group {if !$client_city}hidden{/if}">
            <label>{__("other_town")}</label>
            <input type="text" class="" id="elm_city_text" name="customer_location[city]" value="{$input_city}" />
        </div>
    {else}
        <div class="control-group">
            <label>{__("city")}</label>
            <input type="text" class="" id="elm_city" name="customer_location[city]" value="{$cart.user_data.s_city}" autocomplete="on" />
        </div>
    {/if}

<!--change_city--></div>

<div class="control-group">
    <label for="{$prefix}elm_zipcode{$id_suffix}" {if $location == "sidebox"}class="nowrap"{/if}>{__("zip_postal_code")}</label>
    <input type="text" class="input-text-medium" id="{$prefix}elm_zipcode{$id_suffix}" name="customer_location[zipcode]" size="{if $location != "sidebox"}25{else}20{/if}" value="{$cart.user_data.s_zipcode}" />
</div>

<script type="text/javascript"  class="cm-ajax-force">
//<![CDATA[

(function(_, $) {

    function fn_get_cities(change) 
    {
        var check_country = $("#{$prefix}elm_country{$id_suffix}").val();
        var check_state = $("#{$prefix}elm_state{$id_suffix}").val();

        var url = fn_url('city.shipping_estimation_city');

        url += '&check_country=' + check_country + '&check_state=' +  check_state ;

        var check_city = $("#elm_city").val();
        url += '&check_city=' + check_city;

        $.ceAjax('request', url, {
            result_ids: 'change_city',
            method: 'post'
        });
    }

    $(document).ready(function() {
        fn_get_cities(false);

        $(_.doc).on('change', '#{$prefix}elm_country{$id_suffix}', function() {
            $('#elm_city').val('');
            $('#elm_city_text').val('');
            fn_get_cities(true);
        });

        $(_.doc).on('change', '#{$prefix}elm_state{$id_suffix}', function() {
            $('#elm_city').val('');
            $('#elm_city_text').val('');
            fn_get_cities(true);
        });

        $(_.doc).on('change', '#elm_city', function() {
            var inp = $('#elm_city').val();
            if (inp == 'client_city') {
                $('#client_city').removeClass('hidden');
                $('#elm_city_text').val('');
            } else {
                $('#elm_city_text').val(inp);
                $('#client_city').addClass('hidden');
            }
        });

    });

}(Tygh, Tygh.$));
//]]>
</script>
