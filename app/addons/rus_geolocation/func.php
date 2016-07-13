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

use Tygh\Registry;

function fn_rus_geolocation_install()
{
    db_query("ALTER TABLE ?:rus_cities ADD geolocation_city CHAR(1) NOT NULL DEFAULT 'N'");
    db_query("UPDATE ?:rus_cities SET geolocation_city = 'Y' WHERE country_code = 'RU' AND city_code = 1019");
    db_query("UPDATE ?:rus_cities SET geolocation_city = 'Y' WHERE country_code = 'RU' AND city_code = 1020");
    db_query("UPDATE ?:rus_cities SET geolocation_city = 'Y' WHERE country_code = 'RU' AND city_code = 713");
    db_query("UPDATE ?:rus_cities SET geolocation_city = 'Y' WHERE country_code = 'RU' AND city_code = 578");
    db_query("UPDATE ?:rus_cities SET geolocation_city = 'Y' WHERE country_code = 'RU' AND city_code = 724");
    db_query("UPDATE ?:rus_cities SET geolocation_city = 'Y' WHERE country_code = 'RU' AND city_code = 931");
    db_query("UPDATE ?:rus_cities SET geolocation_city = 'Y' WHERE country_code = 'RU' AND city_code = 621");
    db_query("UPDATE ?:rus_cities SET geolocation_city = 'Y' WHERE country_code = 'RU' AND city_code = 801");
    db_query("UPDATE ?:rus_cities SET geolocation_city = 'Y' WHERE country_code = 'RU' AND city_code = 681");
    db_query("UPDATE ?:rus_cities SET geolocation_city = 'Y' WHERE country_code = 'RU' AND city_code = 947");
    db_query("UPDATE ?:rus_cities SET geolocation_city = 'Y' WHERE country_code = 'RU' AND city_code = 515");
    db_query("UPDATE ?:rus_cities SET geolocation_city = 'Y' WHERE country_code = 'RU' AND city_code = 546");
    db_query("UPDATE ?:rus_cities SET geolocation_city = 'Y' WHERE country_code = 'RU' AND city_code = 662");
    db_query("UPDATE ?:rus_cities SET geolocation_city = 'Y' WHERE country_code = 'RU' AND city_code = 860");
    db_query("UPDATE ?:rus_cities SET geolocation_city = 'Y' WHERE country_code = 'RU' AND city_code = 45");
    db_query("UPDATE ?:rus_cities SET geolocation_city = 'Y' WHERE country_code = 'RU' AND city_code = 494");
}

function fn_rus_geolocation_uninstall()
{
    db_query("ALTER TABLE ?:rus_cities DROP geolocation_city");
}

function fn_geolocation_get_cities_location()
{
    $data_cities = array();
    $d_module = db_get_row("SELECT addon, status FROM ?:addons WHERE addon = 'rus_cities'");

    if (!empty($d_module['addon']) && ($d_module['status'] == 'A')) {
        $data_cities = db_get_array("SELECT * FROM ?:rus_city_descriptions as a LEFT JOIN ?:rus_cities as b ON a.city_id = b.city_id WHERE lang_code = ?s AND geolocation_city = 'Y'", CART_LANGUAGE);
    }

    return $data_cities;
}
