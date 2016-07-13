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

use Tygh\Languages\Languages;
use Tygh\Languages\Values;

use Tygh\Shippings\YandexDelivery;

if ( !defined('AREA') ) { die('Access denied'); }

function fn_yandex_delivery_install()
{
    $service = array(
        'status' => 'A',
        'module' => 'yandex',
        'code' => 'yandex',
        'sp_file' => '',
        'description' => 'Yandex.Delivery',
    );

    $service['service_id'] = db_get_field('SELECT service_id FROM ?:shipping_services WHERE module = ?s AND code = ?s', $service['module'], $service['code']);

    if (empty($service['service_id'])) {
        $service['service_id'] = db_query('INSERT INTO ?:shipping_services ?e', $service);
    }

    $languages = Languages::getAll();
    foreach ($languages as $lang_code => $lang_data) {

        if ($lang_code == 'ru') {
            $service['description'] = "Яндекс.Доставка";
        } else {
            $service['description'] = "Yandex.Delivery";
        }

        $service['lang_code'] = $lang_code;

        db_query('INSERT INTO ?:shipping_service_descriptions ?e', $service);
    }
}

function fn_yandex_delivery_uninstall()
{
    $service_ids = db_get_fields('SELECT service_id FROM ?:shipping_services WHERE module = ?s', 'yandex');
    if (!empty($service_ids)) {
        db_query('DELETE FROM ?:shipping_services WHERE service_id IN (?a)', $service_ids);
        db_query('DELETE FROM ?:shipping_service_descriptions WHERE service_id IN (?a)', $service_ids);
    }
}

function fn_yandex_delivery_pre_place_order(&$cart, $allow, $product_groups)
{
    foreach($cart['product_groups'] as $group_key => &$group) {
        if (!empty($group['chosen_shippings'])) {
            foreach($group['chosen_shippings'] as &$shipping) {
                if ($shipping['module'] == 'yandex' && !empty($shipping['pickup_data'])) {
                    if (!empty($shipping['pickup_data']['schedules'])) {
                        $shipping['pickup_data']['work_time'] = YandexDelivery::getScheduleDays($shipping['pickup_data']['schedules']);
                    }
                }
            }
        }
    }
}

function fn_yandex_delivery_calculate_cart_taxes_pre(&$cart, $cart_products, &$product_groups)
{
    if (!empty($cart['shippings_extra']['yd']['data'])) {
        if (!empty($_REQUEST['select_yd_store'])) {
            $select_yd_store = $cart['select_yd_store'] = $_REQUEST['select_yd_store'];
        } elseif (!empty($cart['select_yd_store'])) {
            $select_yd_store = $cart['select_yd_store'];
        }

        if (!empty($select_yd_store)) {
            foreach ($product_groups as $group_key => $group) {
                if (!empty($group['chosen_shippings'])) {
                    foreach ($group['chosen_shippings'] as $shipping_key => $shipping) {

                        if($shipping['module'] != 'yandex') {
                            continue;
                        }

                        if (!empty($cart['shippings_extra']['yd']['data'][$group_key][$shipping['shipping_id']])) {
                            $shippings_extra = $cart['shippings_extra']['yd']['data'][$group_key][$shipping['shipping_id']];

                            if (!empty($select_yd_store[$group_key][$shipping['shipping_id']])) {
                                $select_pickup_id = $select_yd_store[$group_key][$shipping['shipping_id']];
                                // Сохраняем id выбранной точки
                                $product_groups[$group_key]['chosen_shippings'][$shipping_key]['select_pickup_id'] = $select_pickup_id;

                                // Сохраняем информацию по выбранной точке
                                if (!empty($shippings_extra['pickup_points'])) {
                                    foreach($shippings_extra['pickup_points'] as $point) {
                                        //fn_print_r($point['id']);
                                        if ($point['id'] == $select_pickup_id) {
                                            $product_groups[$group_key]['chosen_shippings'][$shipping_key]['pickup_data'] = $point;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}