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

if (isset($_REQUEST['select_yd_store']) && !empty($_REQUEST['select_yd_store'])) {
    $_SESSION['cart']['select_yd_store'] = $_REQUEST['select_yd_store'];
}

if (isset($_SESSION['cart']['select_yd_store']) && !empty($_SESSION['cart']['select_yd_store'])) {
    Tygh::$app['view']->assign('select_yd_store', $_SESSION['cart']['select_yd_store']);
}

if ($mode == 'update') {

    $cart = $_SESSION['cart'];

    if (!empty($cart['order_id'])) {
        $old_ship_data = db_get_field("SELECT data FROM ?:order_data WHERE order_id = ?i AND type = ?s", $cart['order_id'], 'L');

        if (!empty($old_ship_data)) {
            $old_ship_data = unserialize($old_ship_data);

            Tygh::$app['view']->assign('old_ship_data', $old_ship_data);
        }
    }

}
