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

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if (!empty($_SESSION['cart']['pickpoint_office'])) {
    Tygh::$app['view']->assign('pickpoint_office', $_SESSION['cart']['pickpoint_office']);
}

if (!empty($_SESSION['cart']['user_data'])) {
    $fromcity = '';
    $city = '';

    if (!empty($_SESSION['cart']['user_data']['s_state_descr'])) {
        $fromcity = $_SESSION['cart']['user_data']['s_state_descr'];

    } elseif (!empty($_SESSION['cart']['user_data']['b_state_descr'])) {
        $fromcity = $_SESSION['cart']['user_data']['b_state_descr'];
    }

    if (!empty($_SESSION['cart']['user_data']['s_city'])) {
        $city = $_SESSION['cart']['user_data']['s_city'];
    } elseif (!empty($_SESSION['cart']['user_data']['b_city'])) {
        $city = $_SESSION['cart']['user_data']['b_city'];
    }

    Tygh::$app['view']->assign('fromcity', $fromcity);
    Tygh::$app['view']->assign('pickpoint_city', $city);
}
