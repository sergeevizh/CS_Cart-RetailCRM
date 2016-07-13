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

if (!defined('BOOTSTRAP')) { die('Access denied'); }

$cart = & $_SESSION['cart'];

if ($mode == 'checkout') {

    if (isset($_REQUEST['tariff_id'])) {
        foreach ($_REQUEST['shipping_ids'] as $group_key => $shipping_id) {

            if (isset($_REQUEST['tariff_id'][$group_key])) {
                $cart['shippings_extra']['yd']['tariff_id'][$group_key][$shipping_id] = $_REQUEST['tariff_id'][$group_key];
            }

            if (isset($_REQUEST['pickuppoint_id'][$group_key])) {
                $cart['shippings_extra']['yd']['pickuppoint_id'][$group_key][$shipping_id] = $_REQUEST['pickuppoint_id'][$group_key];
            }
        }
    }

}

if ($mode == 'update_steps' || $mode == 'shipping_estimation') {

    if (!empty($_REQUEST['select_yd_store'])) {
        foreach ($_REQUEST['select_yd_store'] as $g_id => $select) {
            foreach ($select as $s_id => $o_id) {
                $_SESSION['cart']['select_yd_store'][$g_id][$s_id] = $o_id;
            }
        }
    }

    if (!empty($_SESSION['cart']['select_yd_store'])) {
        Tygh::$app['view']->assign('select_yd_store', $_SESSION['cart']['select_yd_store']);
    }

}

if ($mode == 'checkout' || $mode == 'cart') {

    if (!empty($_REQUEST['select_yd_store'])) {
        foreach ($_REQUEST['select_yd_store'] as $g_id => $select) {
            foreach ($select as $s_id => $o_id) {
                $_SESSION['cart']['select_yd_store'][$g_id][$s_id] = $o_id;
            }
        }
    }

}
