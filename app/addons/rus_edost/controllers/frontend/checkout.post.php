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

if ($mode == 'checkout') {
    if (!empty($_REQUEST['select_office'])) {
        foreach($_REQUEST['select_office'] as $g_id => $select) {
            foreach($select as $s_id => $o_id) {
                $_SESSION['cart']['select_office'][$g_id][$s_id] = $o_id;
            }
        }
    }

    if (!empty($_SESSION['cart']['select_office'])) {
        Tygh::$app['view']->assign('select_office', $_SESSION['cart']['select_office']);
    }

} elseif ($mode == 'update_steps') {

    if (!empty($_REQUEST['select_office'])) {
        $_SESSION['cart']['select_office'] = $_REQUEST['select_office'];
    }

}
