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

use Tygh\Shippings\YandexDelivery;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

$cart = & $_SESSION['cart'];

if ($mode == "configure") {

    if (!empty($_REQUEST['module']) && $_REQUEST['module'] == 'yandex' && !empty($_REQUEST['shipping_id'])) {
        $yad = new YandexDelivery();
        $deliveries = $yad->getDeliveries();

        $deliveries_list = array();
        foreach($deliveries as $deliver) {
            $deliveries_list[$deliver['id']] = $deliver['name'];
        }

        $shipping = fn_get_shipping_params($_REQUEST['shipping_id']);

        $deliveries_select = array();
        if (!empty($shipping['deliveries'])) {
            foreach($shipping['deliveries'] as $delivery_id) {
                $deliveries_select[$delivery_id] = $deliveries_list[$delivery_id];
            }
        }

        Tygh::$app['view']->assign('deliveries', $deliveries_list);
        Tygh::$app['view']->assign('deliveries_select', $deliveries_select);
    }
}
