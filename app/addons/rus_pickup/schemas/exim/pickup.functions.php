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

function fn_exim_pickup_set_company_id($company, $pickup_id) {

    $company_id = fn_get_company_id_by_name($company);

    db_query("UPDATE ?:store_locations SET company_id = ?i WHERE store_location_id = ?i ", $company_id , $pickup_id);

    $store_location_data = array(
        'share_company_id' => $company_id,
        'share_object_id' => $pickup_id,
        'share_object_type' => 'store_locations'
    );

    db_query("REPLACE INTO ?:ult_objects_sharing ?e", $store_location_data);

    return true;
}

function fn_exim_pickup_get_destinations($store_location_id, $destinations, $lang_code) {

    $result = '';

    if (!empty($destinations)) {
        $result = array();
        $destinations = explode(',', $destinations);

        foreach ($destinations as $key => $destination_id) {
            $result[] = fn_get_destination_name($destination_id, $lang_code);
        }

        $result = implode(',', $result);
    }

    return $result;
}

function fn_exim_pickup_set_destinations($destinations, $lang_code) {

    $result = '';

    if (!empty($destinations)) {
        $result = array();
        $destinations = explode(',', $destinations);

        foreach($destinations as $destination) {
            $destination_id = db_get_field("SELECT destination_id FROM ?:destination_descriptions WHERE destination = ?s AND lang_code = ?s", $destination, $lang_code);
            
            if (!empty($destination_id)) {
                $result[] = $destination_id;
            }
        }

        $result = implode(',', $result);
    }

    return $result;

}