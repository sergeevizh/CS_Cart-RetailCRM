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

if ($mode == 'autocomplete_city') {
    $params = $_REQUEST;

    $data_country = db_get_fields("SELECT code FROM ?:countries WHERE status = 'A'");

    if (defined('AJAX_REQUEST') && $params['q'] && !empty($data_country)) {
        $select = array();
        $prefix = array('гор.','г.' ,'г ', 'гор ','город ');

        $params['q'] = str_replace($prefix,'',$params['q']);

        $table = '?:rus_cities';
        $table_description = '?:rus_city_descriptions';

        $search = trim($params['q'])."%";

        $join = db_quote("LEFT JOIN $table as c ON c.city_id = d.city_id");

        $condition = db_quote(" AND c.status = ?s", 'A');

        $data_states = db_get_fields("SELECT code FROM ?:states WHERE country_code IN (?a) ", $data_country);
        $condition .= db_quote(" AND c.state_code IN (?a) ", $data_states);

        $cities = db_get_array("SELECT d.city, c.city_code FROM $table_description as d ?p WHERE city LIKE ?l AND lang_code = ?s  ?p  LIMIT ?i", $join, $search, CART_LANGUAGE, $condition, 10);

        if (!empty($cities)) {
            foreach ($cities as $city) {
                $select[] = array(
                    'code' => $city['city_code'],
                    'value' => $city['city'],
                    'label' => $city['city'],
                );
            }
        }

        Tygh::$app['ajax']->assign('autocomplete', $select);
        exit();
    }
}
