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

namespace Tygh\Shippings\Services;

use Tygh\Shippings\YandexDelivery;
use Tygh\Shippings\IService;
use Tygh\Registry;
use Tygh\Http;

class Yandex implements IService
{
    /**
     * Abailability multithreading in this module
     *
     * @var array $_allow_multithreading
     */
    private $_allow_multithreading = false;

    /**
     * Stack for errors occured during the preparing rates process
     *
     * @var array $_error_stack
     */
    private $_error_stack = array();

    /**
     * Current Company id environment
     *
     * @var int $company_id
     */
    public $company_id = 0;

    public $sid;

    public $tariff_id = 0;
    public $pickuppoint_id = 0;
    public $select_yd_store = '';

    /**
     * Collects errors during preparing and processing request
     *
     * @param string $error
     */
    private function _internalError($error)
    {
        $this->_error_stack[] = $error;
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
     * Gets error message from shipping service server
     *
     * @param string $response
     * @internal param string $resonse Reponse from Shipping service server
     * @return string Text of error or false if no errors
     */
    public function processErrors($response)
    {
        $error = '';
        if (!empty($this->_error_stack)) {
            foreach ($this->_error_stack as $_error) {
                $error .= '; ' . $_error;
            }
        }

        return $error;
    }

    /**
     * Sets data to internal class variable
     *
     * @param  array      $shipping_info
     * @return array|void
     */
    public function prepareData($shipping_info)
    {
        $this->_shipping_info = $shipping_info;
        $this->company_id = Registry::get('runtime.company_id');

        $group_key = isset($shipping_info['keys']['group_key']) ? $shipping_info['keys']['group_key'] : 0;
        $shipping_id = isset($shipping_info['keys']['shipping_id']) ? $shipping_info['keys']['shipping_id'] : 0;

        if (isset($_SESSION['cart']['shippings_extra']['yd']['tariff_id'][$group_key][$shipping_id])) {
            $this->tariff_id = $_SESSION['cart']['shippings_extra']['yd']['tariff_id'][$group_key][$shipping_id];
        }

        if (isset($_SESSION['cart']['shippings_extra']['yd']['pickuppoint_id'][$group_key][$shipping_id])) {
            $this->pickuppoint_id = $_SESSION['cart']['shippings_extra']['yd']['pickuppoint_id'][$group_key][$shipping_id];
        }

        if (isset($_SESSION['cart']['select_yd_store'])) {
            $this->select_yd_store = $_SESSION['cart']['select_yd_store'];
        }
    }

    /**
     * Prepare request information
     *
     * @return array Prepared data
     */
    public function getRequestData()
    {
        $request_data = array();

        $yad = new YandexDelivery();

        if (!empty($yad->client_ids['client_id'])) {
            $url = 'https://delivery.yandex.ru/api/1.0/searchDeliveryList';

            $package_info = $this->_shipping_info['package_info'];
            $data['city_from'] = $package_info['origination']['city'];
            $data['city_to'] = !empty($package_info['location']['city']) ? $package_info['location']['city'] : '';

            $data['client_id'] = $yad->client_ids['client_id'];
            $data['sender_id'] = $yad->client_ids['sender_ids'];

            $weight_data = fn_expand_weight($this->_shipping_info['package_info']['W']);
            $data['weight'] = $weight_data['plain'] * Registry::get('settings.General.weight_symbol_grams') / 1000;
            $data['weight'] = sprintf('%.3f', round((double) $data['weight'] + 0.00000000001, 3));

            $service_params = $this->_shipping_info['service_params'];
            list($length, $width, $height) = $this->getPackageValues();
            $data['width'] = ($service_params['width'] > $width) ? $service_params['width'] : $width;
            $data['height'] = ($service_params['height'] > $height) ? $service_params['height'] : $height;
            $data['length'] = ($service_params['length'] > $length) ? $service_params['length'] : $length;

            $data['total_cost'] = $this->_shipping_info['package_info']['C'];

            $data['secret_key'] = $yad->generateSecretKey('searchDeliveryList', $data);

            $request_data = array(
                'method' => 'post',
                'url' => $url,
                'data' => $data,
            );
        }

        return $request_data;
    }

    /**
     * Process simple request to shipping service server
     *
     * @return string Server response
     */
    public function getSimpleRates()
    {
        $data = $this->getRequestData();
        $key = $data['data']['secret_key'];

        $response = fn_get_session_data($key);

        if (empty($response)) {
            $response = Http::post($data['url'], $data['data']);
            fn_set_session_data($key, $response, YD_CACHE_SESSION);
        }

        return $response;
    }

    /**
     * Gets shipping cost and information about possible errors
     *
     * @param string $response
     * @internal param string $resonse Reponse from Shipping service server
     * @return array Shipping cost and errors
     */
    public function processResponse($response)
    {
        $return = array(
            'cost' => false,
            'error' => false,
            'delivery_time' => false,
        );

        $response = json_decode($response, true);
        $service_params = $this->_shipping_info['service_params'];

        if ($service_params['display_type'] = 'CMS') {
            $this->processCms($response, $service_params, $return);

        } else {
            $this->processWidget($response, $service_params, $return);
        }

        if (CART_PRIMARY_CURRENCY != 'RUB') {
            $return['cost'] = fn_format_price_by_currency($return['cost'], 'RUB', CART_PRIMARY_CURRENCY);
        }

        return $return;
    }

    public function processCms($response, $service_params, &$return)
    {
        $deliveries = array();
        $pickup_points = array();
        $group_key = isset($this->_shipping_info['keys']['group_key']) ? $this->_shipping_info['keys']['group_key'] : 0;
        $shipping_id = isset($this->_shipping_info['keys']['shipping_id']) ? $this->_shipping_info['keys']['shipping_id'] : 0;


        if (!empty($service_params['deliveries'])) {
            foreach ($response['data'] as $key => $data) {
                if (!empty($data['delivery']) && in_array($data['delivery']['id'], $service_params['deliveries'])) {
                    $deliveries[$data['delivery']['id']] = $data;

                    foreach ($data['pickupPoints'] as $key => $pickup) {
                        $data['pickupPoints'][$key]['delivery_name'] = $data['delivery']['name'];
                    }

                    $pickup_points = array_merge($pickup_points, $data['pickupPoints']);
                }

            }
        }

        if (!empty($pickup_points)) {
            $old_pickup_points = $pickup_points;
            $pickup_points = array();

            foreach($old_pickup_points as $pickup) {
                $short_address = explode(', ', $pickup['full_address']);
                unset($short_address[0]);
                unset($short_address[1]);
                $pickup['short_address'] = implode(', ', $short_address);

                $pickup_points[$pickup['id']] = $pickup;
            }
        }

        $shipping_data = array();

        if (!empty($pickup_points)) {
            if ($this->_shipping_info['service_params']['sort_type'] == "near") {
                $pickup_points = $this->sortByNearPoints($pickup_points);
            }

            if (empty($_SESSION['cart']['select_yd_store']) ||
                (!empty($_SESSION['cart']['shippings_extra']['yd']['hash_address']) &&
                    $_SESSION['cart']['shippings_extra']['yd']['hash_address'] != md5(trim($this->_shipping_info['package_info']['location']['address']))
                )) {

                $this->select_yd_store = array(
                    $group_key => array(
                        $shipping_id => $this->findNearPickpoint($pickup_points)
                    )
                );

                $_SESSION['cart']['select_yd_store'] = $this->select_yd_store;
            }

            if (!fn_is_empty($this->select_yd_store) && !empty($shipping_id)) {
                $pickup_point_id = $this->select_yd_store[$group_key][$shipping_id];
                $delivery_id = $pickup_points[$pickup_point_id]['delivery_id'];
            } else {
                $delivery_id = $pickup_points[$this->findNearPickpoint($pickup_points)]['delivery_id'];
            }

            if (isset($delivery_id)) {
                $shipping_data = $deliveries[$delivery_id];
            } else {
                $shipping_data = reset($deliveries);
            }
        }

        $shipping_data['deliveries'] = $deliveries;
        $shipping_data['pickup_points'] = $pickup_points;

        if (!empty($shipping_data['maxTime']['d'])) {
            if ($shipping_data['maxTime']['d'] == $shipping_data['minTime']['d']) {
                $return['delivery_time'] = $shipping_data['maxTime']['d'] . " " . __('days');
            } else {
                $return['delivery_time'] = $shipping_data['maxTime']['d'] . "-" . $shipping_data['maxTime']['d'] . " " . __('days');
            }
        }

        if (empty($this->_error_stack) && isset($shipping_data)) {

            $this->fillSessionData($shipping_data);

            if (isset($shipping_data['costWithRules'])) {
                $return['cost'] = $this->getCost($shipping_data);
            }

        } else {

            $this->clearSessionData();
            $return['error'] = $this->processErrors($response);

        }
    }

    public function processWidget($response, $service_params, &$return)
    {
        $first_delivery = reset($response['data']);

        if (!empty($first_delivery)) {
            $delivery_cost = $first_delivery['costWithRules'];
            $delivery_index = 0;
            $pickuppoint_index = 0;

            if (empty($this->tariff_id)) {
                // Find min delivery cost
                foreach ($response['data'] as $key_delivery => $delivery) {
                    if ($delivery['costWithRules'] < $delivery_cost) {
                        $delivery_index = $key_delivery;
                        $delivery_cost = $delivery['costWithRules'];
                    }
                }

            } else {
                foreach ($response['data'] as $key_delivery => $delivery) {
                    if ($delivery['tariffId'] == $this->tariff_id) {

                        $delivery_index = $key_delivery;
                        $delivery_cost = $delivery['costWithRules'];

                        if ($delivery['type'] == 'PICKUP') {

                            foreach ($delivery['pickupPoints'] as $pickup_index => $pickup) {
                                if ($pickup['id'] == $this->pickuppoint_id) {
                                    $pickuppoint_index = $pickup_index;
                                    break;
                                }
                            }
                        }

                        break;
                    }
                }
            }

            $this->_fillSessionData($response, $delivery_index, $pickuppoint_index);

            $return = array(
                'cost' => $this->convertCurrencies($delivery_cost),
                'error' => false,
            );

        } else {
            $return = array(
                'cost' => false,
                'error' => false,
            );
        }
    }

    public function getCost($shipping_data)
    {
        $cost = $shipping_data['costWithRules'];

        return $cost;

    }

    /**
     * Process simple calculate length, width and height
     *
     * @return array length, width, height
     */
    public function getPackageValues()
    {

        $packages = $this->_shipping_info['package_info']['packages'];

        foreach ($packages as $key => $pack) {
            if (!isset($pack['shipping_params'])) {
                unset($packages[$key]);
            }
        }

        $count = count($packages);

        $maximus = array(
            'length' => 0,
            'width' => 0,
            'height' => 0,
        );

        $volume = 0;

        if ($count == 0) {

            $length = $width = $height = 1;

        } elseif ($count == 1) {
            $ship_params = $packages[0]['shipping_params'];

            $length = !empty($ship_params['box_length']) ? $ship_params['box_length'] : 1 ;
            $width = !empty($ship_params['box_width']) ? $ship_params['box_width'] : 1 ;
            $height = !empty($ship_params['box_height']) ? $ship_params['box_height'] : 1 ;

        } elseif ($count > 1) {

            foreach ($packages as $key => $value) {
                $ship_params = $value['shipping_params'];

                $tmp_length = !empty($ship_params['box_length']) ? $ship_params['box_length'] : 1 ;
                $tmp_width = !empty($ship_params['box_width']) ? $ship_params['box_width'] : 1 ;
                $tmp_height = !empty($ship_params['box_height']) ? $ship_params['box_height'] : 1 ;

                $volume += $tmp_length * $tmp_width * $tmp_height;

                if ($tmp_length > $maximus['length']) {
                    $maximus['length'] = $tmp_length;
                }
                if ($tmp_width > $maximus['width']) {
                    $maximus['width'] = $tmp_width;
                }
                if ($tmp_height > $maximus['height']) {
                    $maximus['height'] = $tmp_height;
                }
            }

            arsort($maximus);

            $length = reset($maximus);

            $width = $height = ceil(sqrt($volume / $length));

        }

        return array($length, $width, $height);
    }

    /**
     * Fills edost_cod array in session cart variable
     *
     * @param  string $code       Shipping service code
     * @param  int    $company_id Selected company identifier
     * @param  array  $rates      Previously calculated rates
     * @return bool   true Always true
     */
    private function fillSessionData($shipping_data)
    {

        $group_key = isset($this->_shipping_info['keys']['group_key']) ? $this->_shipping_info['keys']['group_key'] : 0;
        $shipping_id = isset($this->_shipping_info['keys']['shipping_id']) ? $this->_shipping_info['keys']['shipping_id'] : 0;

        $address = !empty($this->_shipping_info['package_info']['location']['address']) ? trim($this->_shipping_info['package_info']['location']['address']) : '';

        $_SESSION['cart']['shippings_extra']['yd']['data'][$group_key][$shipping_id] = $shipping_data;
        $_SESSION['cart']['shippings_extra']['yd']['hash_address'] = md5($address);

        return true;
    }

    private function clearSessionData()
    {

        $group_key = isset($this->_shipping_info['keys']['group_key']) ? $this->_shipping_info['keys']['group_key'] : 0;
        $shipping_id = isset($this->_shipping_info['keys']['shipping_id']) ? $this->_shipping_info['keys']['shipping_id'] : 0;

        unset($_SESSION['cart']['shippings_extra']['yd']['data'][$group_key][$shipping_id]);

        return true;
    }

    private function _fillSessionData($response, $delivery_index, $pickuppoint_index)
    {
        $group_key = isset($this->_shipping_info['keys']['group_key']) ? $this->_shipping_info['keys']['group_key'] : 0;
        $shipping_id = isset($this->_shipping_info['keys']['shipping_id']) ? $this->_shipping_info['keys']['shipping_id'] : 0;

        if ($response['data'][$delivery_index]['type'] == 'PICKUP') {
            $response['data'][$delivery_index]['schedule_days'] = YandexDelivery::getScheduleDays($response['data'][$delivery_index]['pickupPoints'][$pickuppoint_index]['schedules']);
        }

        $_SESSION['cart']['shippings_extra']['yd']['index'][$group_key][$shipping_id] = $delivery_index;
        $_SESSION['cart']['shippings_extra']['yd']['pickup_index'][$group_key][$shipping_id] = $pickuppoint_index;
        $_SESSION['cart']['shippings_extra']['yd']['data'][$group_key][$shipping_id] = $response['data'][$delivery_index];
        $_SESSION['cart']['shippings_extra']['yd']['package_size'][$group_key] = $this->getSizePackage($this->_shipping_info['package_info']);

        return true;
    }
    protected function getSizePackage($shipping_info)
    {
        $shipping_settings = $this->_shipping_info['service_params'];

        $length = !empty($shipping_settings['length']) ? $shipping_settings['length'] : '0';
        $width = !empty($shipping_settings['width']) ? $shipping_settings['width'] : '0';
        $height = !empty($shipping_settings['height']) ? $shipping_settings['height'] : '0';

        $package_size = array(
            'length' => 0,
            'width' => 0,
            'height' => 0,
        );

        if (!empty($shipping_info['packages'])) {

            $box_data = array();
            foreach ($shipping_info['packages'] as $package) {
                $box_data[] = array(
                    empty($package['shipping_params']['box_length']) ? $length : $package['shipping_params']['box_length'],
                    empty($package['shipping_params']['box_width']) ? $width : $package['shipping_params']['box_width'],
                    empty($package['shipping_params']['box_height']) ? $height : $package['shipping_params']['box_height']
                );
            }

            $sort_box_data = array();
            foreach ($box_data as $box) {
                arsort($box);
                $sort_box_data[] = array_values($box);
            }

            $lenght_data = array();
            $width_data = array();
            $height_data = array();
            foreach ($sort_box_data as $box) {
                $lenght_data[] = $box[0];
                $width_data[] = $box[1];
                $height_data[] = $box[2];
            }

            $package_size = array(
                'length' => max($lenght_data),
                'width' => max($width_data),
                'height' => array_sum($height_data),
            );
        }

        return $package_size;
    }

    protected function convertCurrencies($price, $from_currency = 'RUB')
    {
        if (CART_PRIMARY_CURRENCY != $from_currency) {
            $currencies = Registry::get('currencies');

            if (isset($currencies[$from_currency])) {
                $currency = $currencies[$from_currency];
                $price = $price * floatval($currency['coefficient']);
                $price = fn_format_price($price, '', $currency['decimals']);
            }
        }

        return $price;
    }

    protected function findNearPickpoint($pickup_points)
    {
        $pickpints_near = $this->getNearPickpoints($pickup_points);
        $pickpints_near = array_keys($pickpints_near);

        return reset($pickpints_near);
    }

    protected function getNearPickpoints($pickup_points)
    {
        $address = !empty($this->_shipping_info['package_info']['location']['address']) ? trim($this->_shipping_info['package_info']['location']['address']) : '';
        $key = md5($this->_shipping_info['shipping_id'] . implode('_', $this->_shipping_info['service_params']['deliveries']) . $address . trim($this->_shipping_info['package_info']['location']['city']));
        $near_pickoints = fn_get_session_data($key);

        if (empty($near_pickoints)) {

            $address = preg_split('/[ ,]+/', $address);
            $address[] = trim($this->_shipping_info['package_info']['location']['city']);

            $url = "https://geocode-maps.yandex.ru/1.x/";
            $data = array(
                'geocode' => implode('+', $address),
                'format' => 'json',
                'results' => 2,
                'sco' => 'longlat'
            );

            $response = Http::post($url, $data);
            $response = json_decode($response, true);

            $response = $response['response']['GeoObjectCollection'];

            if ($response['metaDataProperty']['GeocoderResponseMetaData']['found'] > 0) {
                $object = reset($response['featureMember']);
                $object = $object['GeoObject'];

                $ll_address = explode(' ', $object['Point']['pos']);
            }


            $lat_pickoints = array();
            $lng_pickoints = array();
            $near_pickoints = array();
            foreach($pickup_points as $point) {
                $lat_pickoints[$point['id']] = $point['lat'];
                $lng_pickoints[$point['id']] = $point['lng'];
                $near_pickoints[$point['id']] = sqrt(pow($lat_pickoints[$point['id']] - $ll_address[1], 2) + pow($lng_pickoints[$point['id']] - $ll_address[0], 2));
            }

            asort($near_pickoints);

            fn_set_session_data($key, $near_pickoints, YD_CACHE_SESSION);
        }

        return $near_pickoints;
    }

    protected function sortByNearPoints($pickup_points)
    {
        $sort_pickup_points = array();
        $pickpints_near = $this->getNearPickpoints($pickup_points);

        foreach($pickpints_near as $point_id => $distance) {
            $sort_pickup_points[$point_id] = $pickup_points[$point_id];
        }

        return $sort_pickup_points;
    }

    public function prepareAddress($address)
    {
        
    }
}
