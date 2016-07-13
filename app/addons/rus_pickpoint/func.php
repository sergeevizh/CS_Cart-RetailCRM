<?php
/***************************************************************************
*                                                                          *
*   (c) 2004 Vladimir V. Kalynyak, Alexey V. Vinokurov, Ilya M. Shalnev    *
*                                                                          *
* This  is  commercial  software,  only  users  who have purchased a valid *
* license  and  accept  to the terms of the  License Agreement can install *
* and use this program.                                                    *
*                                                                          *
****************************************************************************
* PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
* "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
****************************************************************************/

use Tygh\Registry;
use Tygh\Languages\Languages;
use Tygh\Shippings\RusPickpoint;
use Tygh\Http;

if ( !defined('AREA') ) { die('Access denied'); }

function fn_rus_pickpoint_install()
{
    $service = array(
        'status' => 'A',
        'module' => 'pickpoint',
        'code' => 'pickpoint',
        'sp_file' => '',
        'description' => 'Pickpoint'
    );

    $service['service_id'] = db_query('INSERT INTO ?:shipping_services ?e', $service);

    foreach (Languages::getAll() as $service['lang_code'] => $lang_data) {
        db_query('INSERT INTO ?:shipping_service_descriptions ?e', $service);
    }

    db_query("CREATE TABLE IF NOT EXISTS `?:rus_pickpoint_postamat` (
        `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
        `city_name` varchar(150) NOT NULL,
        `country_name` varchar(150) NOT NULL,
        `region_name` varchar(150) NOT NULL,
        `number` varchar(20) NOT NULL,
        `name` varchar(250) NOT NULL,
        `work_time` varchar(100) NOT NULL,
        `post_code` varchar(16) NOT NULL,
        `address` varchar(255) NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;");

    $http_url = fn_get_storefront_protocol();
    $url = $http_url . '://e-solution.pickpoint.ru/api/';
    RusPickpoint::postamatPickpoint($url . 'postamatlist');
}

function fn_rus_pickpoint_uninstall()
{
    $service_ids = db_get_fields('SELECT service_id FROM ?:shipping_services WHERE module = ?s', 'pickpoint');
    db_query('DELETE FROM ?:shipping_services WHERE service_id IN (?a)', $service_ids);
    db_query('DELETE FROM ?:shipping_service_descriptions WHERE service_id IN (?a)', $service_ids);
    db_query('DROP TABLE IF EXISTS ?:rus_pickpoint_postamat');
}

function fn_rus_pickpoint_calculate_cart_taxes_pre(&$cart, $cart_products, &$product_groups)
{
    if (!empty($cart['shippings_extra']['data'])) {
        if (!empty($cart['pickpoint_office'])) {
            $pickpoint_office = $cart['pickpoint_office'];
        } elseif (!empty($_REQUEST['pickpoint_office'])) {
            $pickpoint_office = $cart['pickpoint_office'] = $_REQUEST['pickpoint_office'];
        }

        if (!empty($pickpoint_office)) {
            foreach ($product_groups as $group_key => $group) {
                if (!empty($group['chosen_shippings'])) {
                    foreach ($group['chosen_shippings'] as $shipping_key => $shipping) {
                        if ($shipping['module'] != 'pickpoint') {
                            continue;
                        }

                        $shipping_id = $shipping['shipping_id'];

                        if (!empty($cart['shippings_extra']['data'][$group_key][$shipping_id])) {
                            $shippings_extra = $cart['shippings_extra']['data'][$group_key][$shipping_id];

                            $product_groups[$group_key]['chosen_shippings'][$shipping_key]['data'] = $shippings_extra;
                        }
                    }
                }
            }
        }

        foreach ($cart['shippings_extra']['data'] as $group_key => $shippings) {
            foreach ($shippings as $shipping_id => $shippings_extra) {
                if (!empty($product_groups[$group_key]['shippings'][$shipping_id]['module'])) {
                    $module = $product_groups[$group_key]['shippings'][$shipping_id]['module'];

                    if ($module == 'pickpoint' && !empty($shippings_extra)) {
                        $pickpoint_cost = $shippings_extra['pickpoint_postamat']['Cost'];
                        if (!empty($cart['pickpoint_office'][$group_key][$shipping_id])) {
                            $shippings_extra['pickpoint_postamat'] = $cart['pickpoint_office'][$group_key][$shipping_id];
                        }

                        if (!empty($pickpoint_cost)) {
                            $shippings_extra['pickpoint_postamat']['pickpoint_cost'] = $pickpoint_cost;
                        }

                        $product_groups[$group_key]['shippings'][$shipping_id]['data'] = $shippings_extra;
                    }
                }
            }
        }

        foreach ($product_groups as $group_key => $group) {
            if (!empty($group['chosen_shippings'])) {
                foreach ($group['chosen_shippings'] as $shipping_key => $shipping) {
                    $shipping_id = $shipping['shipping_id'];
                    $module = $shipping['module'];
                    if ($module == 'pickpoint' && !empty($cart['shippings_extra']['data'][$group_key][$shipping_id])) {
                        $shipping_extra = $cart['shippings_extra']['data'][$group_key][$shipping_id];
                        if (!empty($cart['pickpoint_office'][$group_key][$shipping_id])) {
                            $shipping_extra['pickpoint_postamat'] = $cart['pickpoint_office'][$group_key][$shipping_id];
                        }

                        if (!empty($pickpoint_cost)) {
                            $shipping_extra['pickpoint_postamat']['pickpoint_cost'] = $pickpoint_cost;
                        }

                        $product_groups[$group_key]['chosen_shippings'][$shipping_key]['data'] = $shipping_extra;
                    }
                }
            }
        }
    }
}

function fn_pickpoint_cost_by_shipment(&$cart, $shipping_info, $service_data, $city) 
{
    if (!empty($service_data['module']) && ($service_data['module'] == 'pickpoint')) {
        $data = array();
        $pickpoint_info = Registry::get('addons.rus_pickpoint');
        $url = RusPickpoint::Url();
        $data_url = RusPickpoint::$data_url;
        $login = RusPickpoint::Login();

        $total = $weight =  0;
        $length = $width = $height = 20;

        if ($login) {
            $shipping_settings = $service_data['service_params'];
            $weight_data = fn_expand_weight($shipping_info['package_info']['W']);
            $weight = round($weight_data['plain'] * Registry::get('settings.General.weight_symbol_grams') / 1000, 3);

            $packages = !empty($shipping_info['package_info']['packages']) ? $shipping_info['package_info']['packages'] : array();
            $origination = !empty($shipping_info['package_info']['origination']) ? $shipping_info['package_info']['origination'] : '';

            $from_state = fn_get_state_name($origination['state'], $origination['country'], 'RU');

            $length = !empty($shipping_settings['pickpoint_length']) ? $shipping_settings['pickpoint_length'] : 10;
            $width = !empty($shipping_settings['pickpoint_width']) ? $shipping_settings['pickpoint_width'] : 10;
            $height = !empty($shipping_settings['pickpoint_height']) ? $shipping_settings['pickpoint_height'] : 10;

            if (!empty($shipping_info['package_info']['packages'])) {
                $packages = $shipping_info['package_info']['packages'];
                $packages_count = count($packages);
                $pickpoint_weight = $pickpoint_length = $pickpoint_width = $pickpoint_height = 0;
                if ($packages_count > 0) {
                    foreach ($packages as $id => $package) {
                        $package_length = empty($package['shipping_params']['box_length']) ? $length : $package['shipping_params']['box_length'];
                        $package_width = empty($package['shipping_params']['box_width']) ? $width : $package['shipping_params']['box_width'];
                        $package_height = empty($package['shipping_params']['box_height']) ? $height : $package['shipping_params']['box_height'];
                        $weight_ar = fn_expand_weight($package['weight']);
                        $package_weight = round($weight_ar['plain'] * Registry::get('settings.General.weight_symbol_grams') / 1000, 3);

                        $pickpoint_weight = $pickpoint_weight + $package_weight;
                        $pickpoint_length = $pickpoint_length + $package_length;
                        $pickpoint_width = $pickpoint_width + $package_width;
                        $pickpoint_height = $pickpoint_height + $package_height;
                    }

                    $length = $pickpoint_length;
                    $width = $pickpoint_width;
                    $height = $pickpoint_height;
                    $weight = $pickpoint_weight;
                }
            } else {
                $packages_count = 1;
                $weight = round($weight_data['plain'] * Registry::get('settings.General.weight_symbol_grams') / 1000, 3);
            }

            $sid = RusPickpoint::$sid;
            $data_zone = array(
                'SessionId' => $sid,
                'FromCity' => $origination['city']
            );
            $url_zone = $url . 'getzone';

            $pickpoint_id = '';
            $address_pickpoint = RusPickpoint::findPostamatPickpoint($pickpoint_id, $city);
            $data_zone['ToPT'] = $pickpoint_id;
            $pickpoint_zone = RusPickpoint::zonesPickpoint($url_zone, $data_zone, $data_url);
            if (!empty($pickpoint_zone)) {
                $pickpoint_id = (!empty($pickpoint_zone['to_pt'])) ? $pickpoint_zone['to_pt'] : '';
                if ($pickpoint_zone['delivery_min'] == $pickpoint_zone['delivery_max']) {
                    $date_zone = $pickpoint_zone['delivery_max'] . ' ' . __('days');
                } else {
                    $date_zone = $pickpoint_zone['delivery_min'] . '-' . $pickpoint_zone['delivery_max'] . ' ' . __('days');
                }
            }

            if (!empty($pickpoint_id) && !empty($address_pickpoint)) {
                if (!empty($shipping_info['keys']['group_key']) && !empty($shipping_info['keys']['shipping_id'])) {
                    $group_key = $shipping_info['keys']['group_key'];
                    $shipping_id = $shipping_info['keys']['shipping_id'];
                    $cart['pickpoint_office'][$group_key][$shipping_id]['pickpoint_id'] = $pickpoint_id;
                    $cart['pickpoint_office'][$group_key][$shipping_id]['address_pickpoint'] = $address_pickpoint;

                } elseif (!empty($shipping_info['shippings'])) {
                    foreach ($shipping_info['shippings'] as $shipping) {
                        if ($shipping['module'] == 'pickpoint') {
                            $group_key = $shipping['group_key'];
                            $shipping_id = $shipping['shipping_id'];
                            $cart['pickpoint_office'][$group_key][$shipping_id]['pickpoint_id'] = $pickpoint_id;
                            $cart['pickpoint_office'][$group_key][$shipping_id]['address_pickpoint'] = $address_pickpoint;
                        }
                    }
                }
            }

            $data = array(
                'SessionId' => $sid,
                'IKN' => $pickpoint_info['ikn'],
                'FromCity' => $origination['city'],
                'FromRegion' => $from_state,
                'PTNumber' => $pickpoint_id,
                'EncloseCount' => $packages_count,
                'Length' => $length,
                'Depth' => $height,
                'Width' => $width,
                'Weight' => $weight
            );

            $response = Http::post($url . 'calctariff', json_encode($data), $data_url);
            $result = json_decode($response);
            $data_services = json_decode(json_encode($result), true);
            $cost = 0;
            if (isset($data_services['Error']) && ($data_services['Error'] == 1) && !empty($data_services['ErrorMessage'])){
                fn_set_notification('E', __('notice'), $data_services['ErrorMessage']);

            } elseif (isset($data_services['Error']) && !empty($data_services['Error'])) {
                fn_set_notification('E', __('notice'), $data_services['Error']);

            } elseif (isset($data_services['Services'])) {
                $shipment = array_shift($data_services['Services']);
                $cost = $shipment['Tariff'] + $shipment['NDS'];
            }
            foreach ($cart['shipping'] as &$shipping) {
                if ($shipping['module'] == 'pickpoint') {
                    $shipping['rate'] = $cost;
                    $shipping['delivery_time'] = $date_zone;
                }
            }

            RusPickpoint::Logout();
        } else {
            fn_set_notification('E', __('notice'), RusPickpoint::$last_error);
        }
    }
}
