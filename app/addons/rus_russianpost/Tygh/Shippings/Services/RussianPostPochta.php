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

// rus_build_pack dbazhenov

namespace Tygh\Shippings\Services;

use Tygh\Shippings\IService;
use Tygh\Registry;
use Tygh\Http;

/**
 * UPS shipping service
 */
class RussianPostPochta implements IService
{
    /**
     * Availability multithreading in this module
     *
     * @var array $_allow_multithreading
     */
    private $_allow_multithreading = false;

    /**
     * Maximum allowed requests to Russian Post server
     *
     * @var integer $_max_num_requests
     */
    private $_max_num_requests = 2;


    /**
     * Timeout requests to Russian Post server
     *
     * @var integer $_timeout
     */
    private $_timeout = 3;
    /**
     * Stack for errors occured during the preparing rates process
     *
     * @var array $_error_stack
     */
    private $_error_stack = array();

    private function _internalError($error)
    {
        $this->_error_stack[] = $error;
    }

    /**
     * Sets data to internal class variable
     *
     * @param array $shipping_info
     */
    public function prepareData($shipping_info)
    {
        $this->_shipping_info = $shipping_info;
    }

    /**
     * Gets shipping cost and information about possible errors
     *
     * @param  string $resonse Reponse from Shipping service server
     * @return array  Shipping cost and errors
     */
    public function processResponse($response)
    {
        $return = array(
            'cost' => false,
            'error' => false,
        );
        $shipping_settings = $this->_shipping_info['service_params'];

        $result = (array) json_decode($response, true);
        if (!empty($result['status']) && $result['status'] == 'OK') {
            $data_result = $result['data'];
            if (CART_PRIMARY_CURRENCY != 'RUB') {
                $data_result['costEntity']['cost'] = fn_format_price_by_currency($data_result['costEntity']['cost'], 'RUB', CART_PRIMARY_CURRENCY);
            }
            $return['cost'] = $data_result['costEntity']['cost'];

            if ($shipping_settings['shipping_option'] == 'EMS') {
                $return['delivery_time'] = $data_result['timeEntity']['emsDeliveryTimeRange'] . ' дн.';

            } elseif ($shipping_settings['shipping_option'] == 'AVIA') {
                $return['delivery_time'] = $data_result['timeEntity']['firstClassTime'] . ' дн.';

            } else {
                $return['delivery_time'] = $data_result['timeEntity']['deliveryTime'] . ' дн.';
            }
        } else {
            $return['error'] = $this->processErrors($response);
        }

        return $return;
    }

    /**
     * Gets error message from shipping service server
     *
     * @param  string $resonse Reponse from Shipping service server
     * @return string Text of error or false if no errors
     */
    public function processErrors($response)
    {
        preg_match('/<span id=\"lblErrStr\">(.*)<\/span>/i', $response, $matches);

        $error = !empty($matches[1]) ? $matches[1] : __('error_occurred');

        if (!empty($this->_error_stack)) {
            foreach ($this->_error_stack as $_error) {
                $error .= '; ' . $_error;
            }
        }

        return $error;
    }

    /**
     * Checks if shipping service allows to use multithreading
     *
     * @return bool true if allow
     */
    public function allowMultithreading()
    {
        return $this->_allow_multithreading;
    }

    /**
     * Prepare request information
     *
     * @return array Prepared data
     */
    public function getRequestData()
    {
        $data_url = array (
            'headers' => array('Content-Type: application/json'),
            'timeout' => $this->_timeout
        );
        $weight_data = fn_expand_weight($this->_shipping_info['package_info']['W']);
        $shipping_settings = $this->_shipping_info['service_params'];
        $origination = $this->_shipping_info['package_info']['origination'];
        $location = $this->_shipping_info['package_info']['location'];

        $origin['country'] = fn_get_country_name($origination['country'], 'ru');
        $origin['region'] = fn_get_state_name($origination['state'], $origination['country'], 'RU');
        $origin['city'] = $origination['city'];

        $destination['country'] = fn_get_country_name($location['country'], 'ru');
        $destination['region'] = fn_get_state_name($location['state'], $location['country'], 'RU');
        $destination['city'] = $location['city'];

        $international = false;
        if ($origination['country'] != 'RU' || $location['country'] != 'RU') {
            $international = true;
        }

        $country_code = db_get_field("SELECT code_N3 FROM ?:countries WHERE code = ?s", $location['country']);

        if (empty($location['zipcode'])) {
            $this->_internalError(__('russian_post_empty_zipcode'));
            $location['zipcode'] = false;
        }

        $weight = $weight_data['plain'] * Registry::get('settings.General.weight_symbol_grams');
        $total_cost = $this->_shipping_info['package_info']['C'];
        if (CART_PRIMARY_CURRENCY != 'RUB') {
            $total_cost = fn_format_price_by_currency($total_cost, CART_PRIMARY_CURRENCY, 'RUB');
        }
        $insurance = $shipping_settings['insurance'];

        $cash_sum = 0;
        if (!empty($shipping_settings['cash_on_delivery'])) {
            $cash_sum = $total_cost * $shipping_settings['cash_on_delivery'] / 100;
        }

        $insurance_sum = 0;
        if (!empty($insurance) || !empty($cash_sum)) {
            if ($total_cost > $cash_sum) {
                $insurance_sum = $total_cost;
            } else {
                $insurance_sum = $cash_sum;
            }
        }

        $is_wayforward = false;
        if ($shipping_settings['shipping_option'] != 'AVIA') {
            $is_wayforward = true;
        }

        $mailing = array(
            'postingType' => 'VPO',
            'postingCategory' => 'SIMPLE',
            'weight' => $weight,
            'zipCodeFrom' => $origination['zipcode'],
            'zipCodeTo' => $location['zipcode'],
            'postingKind' => 'POST_CARD'
        );

        if ($international) {
            $mailing['postingType'] = 'MPO';
        }

        if ($shipping_settings['delivery_notice']) {
            $mailing['notificationOfDeliveryRpo'] = $shipping_settings['delivery_notice'];
        }

        if ($shipping_settings['inventory']) {
            $mailing['inventory'] = true;
        }

        if ($shipping_settings['careful']) {
            $mailing['careful'] = true;
        }

        $main_type = 'banderol';
        if ($shipping_settings['shipping_option'] == 'EMS') {
            $main_type = 'ems';

        } elseif (($shipping_settings['sending_type'] != 'papers') || ($weight > 2000)) {
            if ($weight < 10000) {
                $main_type = 'standardParcel';

            } elseif ($weight >= 10000 && $weight < 20000) {
                $main_type = 'heavyParcel';

            } elseif ($weight >= 20000) {
                $main_type = 'bigHeavyParcel';
            }
        }

        if ($main_type == 'banderol') {
            $mailing['postingKind'] = "BANDEROLE";
            $mailing['postingCategory'] = "ORDERED";
            $mailing['wayForward'] = "EARTH";

        } elseif ($main_type == 'standardParcel' || $main_type == 'heavyParcel' || $main_type == 'bigHeavyParcel') {
            $mailing['postingKind'] = "PARCEL";
            $mailing['postingCategory'] = "ORDINARY";
            $mailing['wayForward'] = "EARTH";
            if (!$is_wayforward) {
                $mailing['wayForward'] = "AVIA";
            }

            if ($main_type == 'standardParcel') {
                $mailing['parcelKind'] = "STANDARD";
            }

            if ($main_type == 'heavyParcel') {
                $mailing['parcelKind'] = "HEAVY";
            }

            if ($main_type == 'bigHeavyParcel') {
                $mailing['parcelKind'] = "HEAVY_LARGE_SIZED";
            }

        } elseif ($main_type == 'ems') {
            $mailing['postingKind'] = "EMS";
            $mailing['postingCategory'] = "ORDINARY";
        }

        $sending_type = "LETTER_PARCEL";
        if (($weight > 2000) && ($shipping_settings['sending_type'] != 'papers')) {
            $sending_type = "PACKAGE";
        }

        $product_state = array(
            'fromCity' => $origin['city'],
            'fromCountry' => $origin['country'],
            'fromRegion' => $destination['region'],
            'insuranceSum' => $insurance_sum,
            'mainType' => $main_type,
            'toCity' => $destination['city'],
            'toCountry' => $destination['country'],
            'toCountryCode' => $country_code,
            'toRegion' => $destination['region'],
            'weight' => $weight
        );

        $data_post = array(
            'calculationEntity' => array(
                'origin' => $origin,
                'destination' => $destination,
                'sendingType' => $sending_type
            ),
            'costCalculationEntity' => $mailing,
            'productPageState' => $product_state
        );

        $url = "https://www.pochta.ru/calculator/v1/api/delivery.time.cost.get";
        $r_url = "https://www.pochta.ru/portal-portlet/delegate/calculator/v1/api/delivery.time.cost.get";
        $request_data = array(
            'method' => 'post',
            'url' => $url,
            'data' => json_encode($data_post),
            'r_url' => $r_url,
            'data_url' => $data_url
        );

        return $request_data;
    }

    /**
     * Process simple request to shipping service server
     *
     * @return string Server response
     */
    public function getSimpleRates()
    {
        $response = false;

        if (empty($this->_error_stack)) {
            $data = $this->getRequestData();
            $response = Http::post($data['url'], $data['data'], $data['data_url']);
            $result = (array) json_decode($response, true);
            if (empty($result['status']) || !empty($response)) {
                $response = Http::post($data['r_url'], $data['data'], $data['data_url']);
            }
        }

        return $response;
    }

    public function prepareAddress($address)
    {
        
    }
}
