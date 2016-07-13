(function(_, $) {
    $(document).ready(function() {
        var city = $('#geocity').val();

        if (!city) {
            $('#geolocation_link').trigger('click');

            $.getScript('//api-maps.yandex.ru/2.1/?lang=ru_RU', function () {
                ymaps.ready(init);
            });
        }
    });

    $.ceEvent('on', 'ce.commoninit', function(context) {
        $("#auto_geocity").autocomplete({
            source: function( request, response ) {
                getListCities(request, response);
            },
            open: function () {
                var dialog = $(this).closest('.ui-dialog');
                if(dialog.length > 0){
                    $('.ui-autocomplete.ui-front').zIndex(dialog.zIndex()+1);
                }
            }
        });

        function getListCities(request, response) {
            $.ceAjax('request', fn_url('geolocation.autocomplete_city?q=' + encodeURIComponent(request.term)), {
                callback: function(data) {
                    response(data.autocomplete);
                }
            });
        }
    });

    function init()
    {
        var description_city = '';
        var city = '';
        var country_code = '';
        var geolocation = ymaps.geolocation;
        var geolocation_provider = $('#geolocation_provider').val();

        if (!geolocation_provider) {
            geolocation_provider = 'browser';
        }

        geolocation.get({
            provider: geolocation_provider,
            mapStateAutoApply: true
        }).then(
            function (result) {
                var url = $('input[name=pull_url_geolocation]').val();
                var firstGeoObject = result.geoObjects.get(0);
                var select_city = $('#geocity').val();

                description_city = firstGeoObject.properties.get('description');
                city = firstGeoObject.properties.get('metaDataProperty.GeocoderMetaData.AddressDetails.Country.AdministrativeArea.SubAdministrativeArea.Locality.LocalityName');
                country_code = firstGeoObject.properties.get('metaDataProperty.GeocoderMetaData.AddressDetails.Country.CountryNameCode');

                if (select_city) {
                    city = select_city;
                }
                $('#geocity').val(city);
                fn_get_geolocation_choose_city(city, 'geolocation_block');
            },
            function (err) {
                if (!$('#geocity').val()) {
                    city = $('#default_city').val();
                    $('#geocity').val(city);
                    fn_get_geolocation_choose_city(city, 'geolocation_block');
                }
            }
        );
    }

}(Tygh, Tygh.$));

function fn_get_geolocation_choose_city(city, result_ids)
{
    var url = $('input[name=pull_url_geolocation]').val();

    $.ceAjax('request', url, {
        result_ids: result_ids,
        method: 'post',
        full_render: true,
        data: {
            geocity: city,
            url: url
        },
        caching: false
    });
}

function fn_get_geolocation_button_city(city)
{
    var url = $('input[name=pull_url_geolocation]').val();
    var auto_geocity = $("#auto_geocity").val();

    if (auto_geocity || !city) {
        city = auto_geocity;
    }

    $.ceAjax('request', url, {
        result_ids: 'geolocation_city_link',
        method: 'post',
        full_render: true,
        data: {
            geocity: city,
            url: url
        },
        caching: false
    });
}

