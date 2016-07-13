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

$params = $_REQUEST;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($mode == 'add_cities') {
        if (!empty($params['add_city_codes'])) {
            foreach ($params['add_city_codes'] as $city_code) {
                db_query("UPDATE ?:rus_cities SET geolocation_city = 'Y' WHERE city_code = ?i", $city_code);
            }
        }

        return array(CONTROLLER_STATUS_REDIRECT, "geolocation.select_cities");
    }

    if ($mode == 'delete') {
        if (!empty($_REQUEST['city_code'])) {
            db_query("UPDATE ?:rus_cities SET geolocation_city = 'N' WHERE city_code = ?i", $_REQUEST['city_code']);
        }
    }

    if ($mode == 'm_delete') {
        if (!empty($params['city_codes'])) {
            foreach ($params['city_codes'] as $city_code) {
                db_query("UPDATE ?:rus_cities SET geolocation_city = 'N' WHERE city_code = ?i", $city_code);
            }
        }
    }

    return array(CONTROLLER_STATUS_OK, fn_url("geolocation.select_cities"));
}

if ($mode == 'select_cities') {
    $list_cities = $select_cities = array();
    $lang_code = CART_LANGUAGE;
    $joins = array();
    $limit = '';

    $items_per_page = (!empty($_REQUEST['items_per_page'])) ? $_REQUEST['items_per_page'] : Registry::get('settings.Appearance.admin_elements_per_page');
    $default_params = array (
        'page' => 1,
        'items_per_page' => $items_per_page
    );

    $params = array_merge($default_params, $params);

    $d_module = db_get_row("SELECT addon, status FROM ?:addons WHERE addon = 'rus_cities'");

    if (!empty($d_module['addon']) && ($d_module['status'] == 'A')) {
        $fields = array(
            'b.city_code',
            'a.city',
            'b.geolocation_city',
            'c.state',
            'd.country'
        );

        $joins[] = db_quote(" LEFT JOIN ?:rus_cities as b ON a.city_id = b.city_id ", $lang_code);
        $joins[] = db_quote(" LEFT JOIN (SELECT c1.code, c2.lang_code, c2.state, c1.status, c1.country_code FROM ?:states as c1 LEFT JOIN ?:state_descriptions as c2 ON c1.state_id = c2.state_id) as c ON c.code = b.state_code AND c.country_code = b.country_code ", $lang_code);
        $joins[] = db_quote(" LEFT JOIN (SELECT d1.code, d2.lang_code, d2.country, d1.status FROM ?:countries as d1 LEFT JOIN ?:country_descriptions as d2 ON d1.code = d2.code) as d ON d.code = b.country_code ", $lang_code);

        $condition = 'WHERE 1';
        $condition .= db_quote(" AND a.lang_code = ?s AND b.status = 'A'", $lang_code);
        $condition .= db_quote(" AND c.lang_code = ?s AND c.status = 'A'", $lang_code);
        $condition .= db_quote(" AND d.lang_code = ?s AND d.status = 'A'", $lang_code);

        $geo_condition = " AND geolocation_city = 'Y'";
        $n_geo_condition = " AND geolocation_city = 'N'";

        $sorting = "ORDER BY a.city";

        if (!empty($params['items_per_page'])) {
            $geo_cities = db_get_array(
                "SELECT " . implode(', ', $fields) . " FROM ?:rus_city_descriptions as a " .
                implode(' ', $joins) .
                "$condition $geo_condition"
            );
            $params['total_items'] = count($geo_cities);
            $limit = db_paginate($params['page'], $params['items_per_page']);
        }

        $list_cities = db_get_array(
            "SELECT " . implode(', ', $fields) . " FROM ?:rus_city_descriptions as a " .
            implode(' ', $joins) .
            "$condition $n_geo_condition $sorting $limit"
        );

        $select_cities = db_get_array(
            "SELECT " . implode(', ', $fields) . " FROM ?:rus_city_descriptions as a " .
            implode(' ', $joins) .
            "$condition $geo_condition $sorting $limit"
        );
    }

    Tygh::$app['view']->assign('list_cities', $list_cities);
    Tygh::$app['view']->assign('select_cities', $select_cities);
    Tygh::$app['view']->assign('search', $params);
}

if ($mode == 'list_geocities') {
    if (defined('AJAX_REQUEST')) {
        $list_cities = array();
        $lang_code = CART_LANGUAGE;
        $joins = array();
        $limit = '';

        $items_per_page = (!empty($_REQUEST['items_per_page'])) ? $_REQUEST['items_per_page'] : Registry::get('settings.Appearance.admin_elements_per_page');
        $default_params = array (
            'page' => 1,
            'items_per_page' => $items_per_page
        );

        $params = array_merge($default_params, $params);

        $d_module = db_get_row("SELECT addon, status FROM ?:addons WHERE addon = 'rus_cities'");

        if (!empty($d_module['addon']) && ($d_module['status'] == 'A')) {
            $fields = array(
                'b.city_code',
                'a.city',
                'b.geolocation_city',
                'c.state',
                'd.country'
            );

            $joins[] = db_quote(" LEFT JOIN ?:rus_cities as b ON a.city_id = b.city_id ", $lang_code);
            $joins[] = db_quote(" LEFT JOIN (SELECT c1.code, c2.lang_code, c2.state, c1.status, c1.country_code FROM ?:states as c1 LEFT JOIN ?:state_descriptions as c2 ON c1.state_id = c2.state_id) as c ON c.code = b.state_code AND c.country_code = b.country_code ", $lang_code);
            $joins[] = db_quote(" LEFT JOIN (SELECT d1.code, d2.lang_code, d2.country, d1.status FROM ?:countries as d1 LEFT JOIN ?:country_descriptions as d2 ON d1.code = d2.code) as d ON d.code = b.country_code ", $lang_code);

            $condition = 'WHERE 1';
            $condition .= db_quote(" AND a.lang_code = ?s AND b.status = 'A'", $lang_code);
            $condition .= db_quote(" AND c.lang_code = ?s AND c.status = 'A'", $lang_code);
            $condition .= db_quote(" AND d.lang_code = ?s AND d.status = 'A'", $lang_code);

            $n_geo_condition = " AND geolocation_city = 'N'";

            $sorting = "ORDER BY a.city";

            if (!empty($params['items_per_page'])) {
                $geo_cities = db_get_array(
                    "SELECT " . implode(', ', $fields) . " FROM ?:rus_city_descriptions as a " .
                    implode(' ', $joins) .
                    "$condition $n_geo_condition"
                );
                $params['total_items'] = count($geo_cities);
                $limit = db_paginate($params['page'], $params['items_per_page']);
            }

            $list_cities = db_get_array(
                "SELECT " . implode(', ', $fields) . " FROM ?:rus_city_descriptions as a " .
                implode(' ', $joins) .
                "$condition $n_geo_condition $sorting $limit"
            );
        }

        Tygh::$app['view']->assign('list_cities', $list_cities);
        Tygh::$app['view']->assign('search', $params);
    } else {
        return array(CONTROLLER_STATUS_REDIRECT, fn_url("geolocation.select_cities"));
    }
}
