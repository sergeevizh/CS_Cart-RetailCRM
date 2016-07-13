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
use Tygh\Storage;
use Tygh\Commerceml\RusEximCommerceml;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_settings_variants_addons_rus_exim_1c_exim_1c_order_statuses()
{
    $order_statuses = fn_get_simple_statuses('O', false, false, CART_LANGUAGE);

    return $order_statuses;
}

function fn_settings_variants_addons_rus_exim_1c_exim_1c_default_category()
{
    $categories_tree = array();
    $categories = fn_get_plain_categories_tree(0, false);       
    foreach ($categories as $key => $category_data) {
        if (isset($category_data['level'])) {
            $indent = '';
            for($i = 0; $i < $category_data['level']; $i++) {
                $indent = $indent . "¦__";
            }
            $categories_tree[$category_data['category_id']] = $indent.$category_data['category'];
        }
    }

    return $categories_tree;
}

function fn_settings_variants_addons_rus_exim_1c_exim_1c_lang()
{
    $langs = fn_get_simple_languages();

    return $langs;
}

function fn_rus_exim_1c_get_information()
{
    $storefront_url = fn_get_storefront_url(fn_get_storefront_protocol());
    if (fn_allowed_for('ULTIMATE')) {
        if (Registry::get('runtime.company_id') || Registry::get('runtime.simple_ultimate')) {
        } else {
            $storefront_url = '';
        }
    }

    $exim_1c_info = '';
    if (!empty($storefront_url)) {
        $exim_1c_info = __('exim_1c_information', array(
            '[http_location]' => $storefront_url . '/' . 'exim_1c',
        ));
    }

    return $exim_1c_info;
}

function fn_rus_exim_1c_get_information_shipping_features()
{
    $exim_1c_info_features = __('exim_1c_information_shipping_features');

    return $exim_1c_info_features;
}

function fn_rus_exim_1c_get_orders($params, $fields, $sortings, &$condition, $join, $group)
{
    $number_for_orders = trim(Registry::get('addons.rus_exim_1c.exim_1c_from_order_id'));
    if (isset($params['place'])) {
        if (!empty($number_for_orders)) {
            $order_id = Registry::get('addons.rus_exim_1c.exim_1c_from_order_id');
            if (!empty($order_id)) {
                $condition .= db_quote(" AND ?:orders.order_id >= ?i", $order_id);
            }
        }
    }
}

function fn_rus_exim_1c_init_secure_controllers(&$controllers)
{
    $controllers['exim_1c'] = 'passive';
}

