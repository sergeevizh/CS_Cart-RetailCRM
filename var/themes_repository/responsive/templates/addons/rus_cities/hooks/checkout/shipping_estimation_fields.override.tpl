{$_state = $cart.user_data.s_state}
{$_country = $cart.user_data.s_country}

{if !isset($cart.user_data.s_country)}
    {$_country = $settings.General.default_country}
{/if}

{if !isset($cart.user_data.s_state) && $_country == $settings.General.default_country}
    {$_state = $settings.General.default_state}
{/if}

<div class="ty-control-group">
    <label class="ty-control-group__label cm-required" for="{$prefix}elm_country{$id_suffix}">{__("country")}</label>
    <select id="{$prefix}elm_country{$id_suffix}" class="cm-country cm-location-estimation{$class_suffix} ty-input-text-medium" name="customer_location[country]">
        <option value="">- {__("select_country")} -</option>
        {assign var="countries" value=1|fn_get_simple_countries}
        {foreach from=$countries item="country" key="code"}
        <option value="{$code}" {if $_country == $code}selected="selected"{/if}>{$country}</option>
        {/foreach}
    </select>
</div>

<div class="ty-control-group">
    <label class="ty-control-group__label" for="{$prefix}elm_state{$id_suffix}">{__("state")}</label>
    <select class="cm-state cm-location-estimation{$class_suffix} {if !$states[$_country]}hidden{/if} ty-input-text-medium" id="{$prefix}elm_state{$id_suffix}" name="customer_location[state]">
        <option value="">- {__("select_state")} -</option>
        {foreach $states[$cart.user_data.s_country] as $state}
            <option value="{$state.code}" {if $state.code == $_state}selected="selected"{/if}>{$state.state}</option>
        {foreachelse}
            <option label="" value="">- {__("select_state")} -</option>
        {/foreach}
    </select>
    <input type="text" class="cm-state cm-location-estimation{$class_suffix} ty-input-text-medium {if $states[$cart.user_data.s_country]}hidden{/if}" id="{$prefix}elm_state{$id_suffix}_d" name="customer_location[state]" size="20" maxlength="64" value="{$_state}" {if $states[$cart.user_data.s_country]}disabled="disabled"{/if} />
</div>

<div id="change_city">
    {if $cities}
        <div class="ty-control-group">
            <label class="ty-control-group__label" for="{$prefix}elm_city{$id_suffix}">{__("city")}</label>
            <select class="cm-location-estimation{$class_suffix} ty-input-text-medium" id="{$prefix}elm_city{$id_suffix}" name="customer_location[city]">
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

        <div id="client_city" class="ty-control-group {if !$client_city}hidden{/if}">
            <label class="ty-control-group__label" for="{$prefix}elm_city_text{$id_suffix}">{__("other_town")}</label>
            <input type="text" class="ty-input-text-medium" id="{$prefix}elm_city_text{$id_suffix}" name="customer_location[city]" value="{$input_city}" disabled="disabled"/>
        </div>
    {else}
        <div class="ty-control-group">
            <label  class="ty-control-group__label">{__("city")}</label>
            <input type="text" class="ty-input-text-medium" id="{$prefix}elm_city{$id_suffix}" name="customer_location[city]" value="{$cart.user_data.s_city}" autocomplete="on" />
        </div>
    {/if}
<!--change_city--></div>

<div class="ty-control-group">
    <label class="ty-control-group__label" for="{$prefix}elm_zipcode{$id_suffix}">{__("zip_postal_code")}</label>
    <input type="text" class="ty-input-text-medium" id="{$prefix}elm_zipcode{$id_suffix}" name="customer_location[zipcode]" size="20" value="{$cart.user_data.s_zipcode}" />
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

            var check_city = $("#{$prefix}elm_city{$id_suffix}").val();
            url += '&check_city=' + check_city;

            var city_text = $("#elm_city_text").val();
            url += '&city_text=' + city_text;

            $.ceAjax('request', url, {
                result_ids: 'change_city',
                method: 'post'
            });
        }

        $(document).ready(function() {
            fn_get_cities(false);

            $(_.doc).on('change', '#{$prefix}elm_country{$id_suffix}', function() {
                $('#{$prefix}elm_city{$id_suffix}').val('');
                $('#elm_city_text').val('');
                fn_get_cities(true);
            });

            $(_.doc).on('change', '#{$prefix}elm_state{$id_suffix}', function() {
                $('#{$prefix}elm_city{$id_suffix}').val('');
                $('#elm_city_text').val('');
                fn_get_cities(true);
            });

            $(_.doc).on('change', '#{$prefix}elm_city{$id_suffix}', function() {
                var inp = $('#{$prefix}elm_city{$id_suffix}').val();
                if (inp == 'client_city') {
                    $('#client_city').removeClass('hidden');
                    $('#elm_city_text').removeAttr('disabled').val('');
                } else {
                    $('#elm_city_text').attr('disabled', 'disabled').val('');
                    $('#client_city').addClass('hidden');
                }
                $.ceDialog('get_last').ceDialog('reload');
            });

        });

    }(Tygh, Tygh.$));
    //]]>
</script>
