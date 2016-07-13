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

if ( !defined('AREA') ) { die('Access denied'); }

function fn_rus_dellin_install()
{
    $service = array(
        'status' => 'A',
        'module' => 'dellin',
        'code' => '301',
        'sp_file' => '',
        'description' => 'Деловые линии'
    );

    $service_id = db_query('INSERT INTO ?:shipping_services ?e', $service);
    $service['service_id'] = $service_id;

    foreach (Languages::getAll() as $service['lang_code'] => $lang_data) {
        db_query('INSERT INTO ?:shipping_service_descriptions ?e', $service);
    }
}

function fn_rus_dellin_uninstall()
{
    $service_ids = db_get_fields('SELECT service_id FROM ?:shipping_services WHERE module = ?s', 'dellin');

    if (!empty($service_ids)) {
        db_query('DELETE FROM ?:shipping_services WHERE service_id IN (?a)', $service_ids);
        db_query('DELETE FROM ?:shipping_service_descriptions WHERE service_id IN (?a)', $service_ids);
    }

    db_query('DROP TABLE IF EXISTS ?:rus_dellin_cities');

    $file_dir = fn_get_files_dir_path() . "dellin/";
    fn_rm($file_dir);
}

function fn_rus_dellin_calculate_cart_taxes_pre(&$cart, $cart_products, &$product_groups)
{
    if (!empty($cart['shippings_extra']['data'])) {
        if (!empty($cart['arrival_terminal'])) {
            $arrival_terminal = $cart['arrival_terminal'];

        } elseif (!empty($_REQUEST['arrival_terminal'])) {
            $arrival_terminal = $cart['arrival_terminal'] = $_REQUEST['arrival_terminal'];
        }

        if (!empty($arrival_terminal)) {
            foreach ($product_groups as $group_key => $group) {
                if (!empty($group['chosen_shippings'])) {
                    foreach ($group['chosen_shippings'] as $shipping_key => $shipping) {
                        $shipping_id = $shipping['shipping_id'];

                        if($shipping['module'] != 'dellin') {
                            continue;
                        }

                        if (!empty($cart['shippings_extra']['data'][$group_key][$shipping_id])) {
                            $shippings_extra = $cart['shippings_extra']['data'][$group_key][$shipping_id]['arrival_terminals'];
                            $product_groups[$group_key]['chosen_shippings'][$shipping_key]['terminal_data'] = $shippings_extra;
                            if (!empty($arrival_terminal[$group_key][$shipping_id])) {
                                $terminal_id = $arrival_terminal[$group_key][$shipping_id];
                                $product_groups[$group_key]['chosen_shippings'][$shipping_key]['terminal_id'] = $terminal_id;

                                foreach ($shippings_extra as $_terminal) {
                                    if ($_terminal['code'] == $terminal_id) {
                                        $terminal_data = $_terminal;
                                        $product_groups[$group_key]['chosen_shippings'][$shipping_key]['terminal_data'] = $terminal_data;
                                    }
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

                        if ($module == 'dellin' && !empty($shippings_extra)) {
                            $product_groups[$group_key]['shippings'][$shipping_id]['data'] = $shippings_extra;
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

                    if ($module == 'dellin' && !empty($cart['shippings_extra']['data'][$group_key][$shipping_id])) {
                        $shipping_extra = $cart['shippings_extra']['data'][$group_key][$shipping_id];
                        $product_groups[$group_key]['chosen_shippings'][$shipping_key]['data'] = $shipping_extra;
                    }
                }
            }
        }
    }
}
