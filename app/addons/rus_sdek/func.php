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
use Tygh\Http;
use Tygh\Languages\Languages;
use Tygh\Shippings\Shippings;

if ( !defined('AREA') ) { die('Access denied'); }

function fn_rus_sdek_install()
{
    $service = array(
        'status' => 'A',
        'module' => 'sdek',
        'code' => '1',
        'sp_file' => '',
        'description' => 'СДЭК',
    );
    
    $service['service_id'] = db_query('INSERT INTO ?:shipping_services ?e', $service);

    foreach (Languages::getAll() as $service['lang_code'] => $lang_data) {
        db_query('INSERT INTO ?:shipping_service_descriptions ?e', $service);
    }
}

function fn_rus_sdek_uninstall()
{
    $service_ids = db_get_fields('SELECT service_id FROM ?:shipping_services WHERE module = ?s', 'sdek');
    db_query('DELETE FROM ?:shipping_services WHERE service_id IN (?a)', $service_ids);
    db_query('DELETE FROM ?:shipping_service_descriptions WHERE service_id IN (?a)', $service_ids);

    db_query('DROP TABLE IF EXISTS ?:rus_cities_sdek');
    db_query('DROP TABLE IF EXISTS ?:rus_city_sdek_descriptions');
    db_query('DROP TABLE IF EXISTS ?:rus_sdek_products');
    db_query('DROP TABLE IF EXISTS ?:rus_sdek_register');
    db_query('DROP TABLE IF EXISTS ?:rus_sdek_status');
    db_query('DROP TABLE IF EXISTS ?:rus_sdek_call_recipient');
    db_query('DROP TABLE IF EXISTS ?:rus_sdek_call_courier');
}

function fn_rus_sdek_update_cart_by_data_post(&$cart, $new_cart_data, $auth)
{
    if (!empty($new_cart_data['select_office'])) {
        $cart['select_office'] = $new_cart_data['select_office'];
    }
}

function fn_rus_sdek_calculate_cart_taxes_pre(&$cart, $cart_products, &$product_groups)
{

    if (!empty($cart['shippings_extra']['data'])) {
        if (!empty($cart['select_office'])) {
            $select_office = $cart['select_office'];

        } elseif (!empty($_REQUEST['select_office'])) {
            $select_office = $cart['select_office'] = $_REQUEST['select_office'];
        }

        if (!empty($select_office)) {
            foreach ($product_groups as $group_key => $group) {
                if (!empty($group['chosen_shippings'])) {
                    foreach ($group['chosen_shippings'] as $shipping_key => $shipping) {
                        $shipping_id = $shipping['shipping_id'];

                        if($shipping['module'] != 'sdek') {
                            continue;
                        }

                        if (!empty($cart['shippings_extra']['data'][$group_key][$shipping_id])) {
                            $shippings_extra = $cart['shippings_extra']['data'][$group_key][$shipping_id];
                            $product_groups[$group_key]['chosen_shippings'][$shipping_key]['data'] = $shippings_extra;
                            if (!empty($select_office[$group_key][$shipping_id])) {
                                $office_id = $select_office[$group_key][$shipping_id];
                                $product_groups[$group_key]['chosen_shippings'][$shipping_key]['office_id'] = $office_id;

                                if (!empty($shippings_extra['offices'][$office_id])) {
                                    $office_data = $shippings_extra['offices'][$office_id];
                                    $product_groups[$group_key]['chosen_shippings'][$shipping_key]['office_data'] = $office_data;
                                }
                            }
                        }
                    }
                }
            }
        }

        if (!empty($cart['shippings_extra']['data'])) {
            foreach ($cart['shippings_extra']['data'] as $group_key => $shippings) {
                foreach ($shippings as $shipping_id => $shippings_extra) {
                    if (!empty($product_groups[$group_key]['shippings'][$shipping_id]['module'])) {
                        $module = $product_groups[$group_key]['shippings'][$shipping_id]['module'];

                        if ($module == 'sdek' && !empty($shippings_extra)) {
                            $product_groups[$group_key]['shippings'][$shipping_id]['data'] = $shippings_extra;

                            if (!empty($shippings_extra['delivery_time'])) {
                                $product_groups[$group_key]['shippings'][$shipping_id]['delivery_time'] = $shippings_extra['delivery_time'];
                            }
                        }
                    }
                }
            }
        }

        foreach ($product_groups as $group_key => $group) {
            if (!empty($group['chosen_shippings'])) {
                foreach ($group['chosen_shippings'] as $shipping_key => $shipping) {
                    $shipping_id = $shipping['shipping_id'];
                    $module = $shipping['module'];

                    if ($module == 'sdek' && !empty($cart['shippings_extra']['data'][$group_key][$shipping_id])) {
                        $shipping_extra = $cart['shippings_extra']['data'][$group_key][$shipping_id];
                        $product_groups[$group_key]['chosen_shippings'][$shipping_key]['data'] = $shipping_extra;
                    }
                }
            }
        }
    }
}

function fn_sdek_calculate_cost_by_shipment($order_info, $shipping_info, $shipment_info, $rec_city_code)
{
    $total = $weight = 0;
    $length = $width = $height = SDEK_DEFAULT_DIMENSIONS;
    $sum_rate = 0;

    $shipping_info['module'] = $shipment_info['carrier'];

    foreach ($shipment_info['products'] as $item_id => $amount) {
        $product = $order_info['products'][$item_id];

        $total += $product['subtotal'];

        $product_extra = db_get_row("SELECT shipping_params, weight FROM ?:products WHERE product_id = ?i", $product['product_id']);

        if (!empty($product_extra['weight']) && $product_extra['weight'] != 0) {
            $product_weight = $product_extra['weight'];
        } else {
            $product_weight = SDEK_DEFAULT_WEIGHT;
        }

        $p_ship_params = unserialize($product_extra['shipping_params']);

        $package_length = empty($p_ship_params['box_length']) ? $length : $p_ship_params['box_length'];
        $package_width = empty($p_ship_params['box_width']) ? $width : $p_ship_params['box_width'];
        $package_height = empty($p_ship_params['box_height']) ? $height : $p_ship_params['box_height'];
        $weight_ar = fn_expand_weight($product_weight);
        $weight = round($weight_ar['plain'] * Registry::get('settings.General.weight_symbol_grams') / 1000, 3);

        $params_product['weight'] = $weight;
        $params_product['length'] = $package_length;
        $params_product['width'] = $package_width;
        $params_product['height'] = $package_height;

        foreach ($order_info['product_groups'] as $product_groups) {
            if (!empty($product_groups['products'][$item_id])) {
                $products[$item_id] = $product_groups['products'][$item_id];
                $products[$item_id] = array_merge($products[$item_id], $params_product);
                $products[$item_id]['amount'] = $amount;
            }

            $shipping_info['package_info'] = $product_groups['package_info'];
        }
    }

    $data_package = Shippings::groupProductsList($products, $shipping_info['package_info']['location']);
    $data_package = reset($data_package);
    $shipping_info['package_info_full'] = $data_package['package_info_full'];
    $shipping_info['package_info'] = $data_package['package_info_full'];

    $sum_rate = Shippings::calculateRates(array($shipping_info));
    $sum_rate = reset($sum_rate);
    $result = $sum_rate['price'];

    return $result;
}
