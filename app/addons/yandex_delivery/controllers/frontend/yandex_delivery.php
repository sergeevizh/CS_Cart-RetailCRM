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
use Tygh\Shippings\YandexDelivery;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($mode == 'autocomplete') {

    $params = $_REQUEST;

    if (defined('AJAX_REQUEST') && $params['q']) {

        $yad = new YandexDelivery();

        $params['city'] = !empty($params['city']) ? $params['city'] : '';
        $result = $yad->autocomplete($params['q'], $params['type'], $params['city']);

        $select = array();
        if (!empty($result)) {

            foreach ($result as $city) {
                $city['value'] = explode(',', $city['value']);

                $select[] = array(
                    'code' => $city['value'],
                    'value' => $city['value'][0],
                    'label' => $city['label'],
                );
            }
        }

        Registry::get('ajax')->assign('autocomplete', $select);
        exit();
    }

} elseif ($mode == 'get_index') {

    $params = $_REQUEST;

    if (defined('AJAX_REQUEST') && !empty($params['address'])) {

        $yad = new YandexDelivery();

        $address[] = $params['address'];

        if (!empty($params['city'])) {
            $address[] = $params['city'];
        }

        $result = $yad->getIndex(implode(',', $address));

        if (!empty($result)) {
            Registry::get('ajax')->assign('get_index', $result);
        }
    }

    exit();

}
