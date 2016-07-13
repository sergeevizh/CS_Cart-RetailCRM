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

include_once(Registry::get('config.dir.addons') . 'rus_pickup/schemas/exim/pickup.functions.php');
include_once(Registry::get('config.dir.schemas') . 'exim/products.functions.php');

$schema = array(
    'section' => 'pickup',
    'pattern_id' => 'pickup',
    'name' => __('pickup'),
    'key' => array('store_location_id'),
    'order' => 1,
    'table' => 'store_locations',
    'references' => array(
        'store_location_descriptions' => array(
            'reference_fields' => array('store_location_id' => '#key', 'lang_code' => '#lang_code'),
            'join_type' => 'LEFT'
        ),
    ),
    'options' => array(
        'lang_code' => array(
            'title' => 'language',
            'type' => 'languages',
            'default_value' => array(DEFAULT_LANGUAGE),
        ),
    ),
    'condition' => array(
        'use_company_condition' => true,
    ),
    'export_fields' => array(
        'Pickup ID' => array(
            'db_field' => 'store_location_id',
            'alt_key' => true,
            'required' => true,
        ),
        'Language' => array(
            'table' => 'store_location_descriptions',
            'db_field' => 'lang_code',
            'type' => 'languages',
            'required' => true,
            'multilang' => true
        ),
        'Latitude' => array(
            'db_field' => 'latitude',
            'required' => true,
        ),
        'Longitude' => array(
            'db_field' => 'longitude',
            'required' => true,
        ),
        'Country' => array(
            'db_field' => 'country',
            'required' => true,
        ),
        'City' => array(
            'table' => 'store_location_descriptions',
            'db_field' => 'city',
            'multilang' => true
        ),
        'Name' => array(
            'table' => 'store_location_descriptions',
            'db_field' => 'name',
            'multilang' => true,
        ),
        'Description' => array(
            'table' => 'store_location_descriptions',
            'db_field' => 'description',
            'multilang' => true
        ),
        'Position' => array(
            'db_field' => 'position',
        ),
        'Status' => array(
            'db_field' => 'status',
        ),
        'Pickup avail' => array(
            'db_field' => 'pickup_avail',
            'multilang' => true
        ),
        'Pickup surcharge' => array(
            'db_field' => 'pickup_surcharge',
            'multilang' => true
        ),
        'Pickup address' => array(
            'table' => 'store_location_descriptions',
            'db_field' => 'pickup_address',
            'multilang' => true
        ),
        'Pickup phone' => array(
            'table' => 'store_location_descriptions',
            'db_field' => 'pickup_phone',
            'multilang' => true
        ),
        'Pickup time' => array(
            'table' => 'store_location_descriptions',
            'db_field' => 'pickup_time',
            'multilang' => true
        ),
        'Pickup destinations ids' => array(
            'db_field' => 'pickup_destinations_ids',
            'process_get' => array('fn_exim_pickup_get_destinations', '#key', '#this','#lang_code'),
            'convert_put' => array('fn_exim_pickup_set_destinations', '#this','#lang_code'),
        )
    )
);

if (fn_allowed_for('ULTIMATE')) {
    $schema['export_fields']['Store'] = array(
        'db_field' => 'company_id',
        'process_get' => array('fn_get_company_name', '#this'),
        'process_put' => array('fn_exim_pickup_set_company_id','#this', '#key')
    );

    if (!Registry::get('runtime.company_id')) {
        $schema['export_fields']['Store']['required'] = true;
    }
}

return $schema;
