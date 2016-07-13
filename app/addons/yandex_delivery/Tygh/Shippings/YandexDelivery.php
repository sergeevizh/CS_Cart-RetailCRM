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

namespace Tygh\Shippings;

use Tygh\Http;
use Tygh\Registry;

class YandexDelivery
{

    public $api_keys = array();
    public $client_ids = array();

    public $version = "1.0";
    public $url_api = "";

    private static $init_cache = false;

    /**
     * Creates API instance
     */
    public function __construct()
    {

        $addon_info = Registry::get('addons.yandex_delivery');

        $this->apiKeysToArray($addon_info['api_keys']);
        $this->clienIdsToArray($addon_info['client_ids']);

        $this->url_api = "https://delivery.yandex.ru/api/" . $this->version . "/";
    }

    public function apiKeysToArray($api_keys)
    {

        $api_keys = explode( PHP_EOL , $api_keys);

        foreach ($api_keys as $value) {
            if (strpos($value, ':') !== false) {
                $data = explode(':' , $value);
                $this->api_keys[trim($data[0])] = trim($data[1]);
            }
        }

    }

    public function clienIdsToArray($client_ids)
    {
        // cut out a comment with /* */
        $client_ids = preg_replace("/\/\*.*\*\//", '', $client_ids);
        if (!empty($client_ids)) {
            $client_ids = str_replace(array('[', ']'), '', $client_ids);
            $client_ids = explode(',', $client_ids);

            foreach ($client_ids as $value) {
                $data = explode(':', $value);
                $key = str_replace(array('"', ' '), '', $data[0]);
                $value = str_replace(array('"', ' '), '', $data[1]);
                $this->client_ids[$key] = $value;

            }
        }
    }

    public function generateSecretKey($method, $data)
    {

        $method_key = $this->getMethodKey($method);

        ksort($data);

        $data_for_key = implode('', $data);

        $string = $data_for_key . $method_key;

        $key = md5($string);

        return $key;
    }

    public function getMethodKey($method)
    {

        $method_key = $this->api_keys[$method];

        return $method_key;
    }

    public function autocompleteCity($term)
    {

        $result = $this->autocomplete($term, 'locality');

        return $result;
    }

    public function autocomplete($term, $type = 'locality', $city = false, $street = false)
    {
        $url = $this->url_api . 'autocomplete';

        $data = array(
            'client_id' => $this->client_ids['client_id'],
            'sender_id' => $this->client_ids['sender_ids'],
            'term' => $term,
            'type' => $type,
        );

        if (!empty($city)) {
            $data['locality_name'] = $city;
        }

        if (!empty($street)) {
            $data['street'] = $street;
        }

        $data['secret_key'] = $this->generateSecretKey('autocomplete', $data);

        $result = $this->request($url, $data);

        if (!empty($result)) {
            $result = $this->autocompleteResult($result);
        }

        return $result;
    }

    public function autocompleteResult($response)
    {
        $result = false;
        if (isset($response['suggestions']) && !empty($response['suggestions'])) {
            $result = $response['suggestions'];
        }

        return $result;
    }

    public function request($url, $data)
    {
        $key = $data['secret_key'];
        $response = fn_get_session_data($key);

        if (empty($response)) {
            $response = Http::post($url, $data);
            fn_set_session_data($key, $response, YD_CACHE_SESSION);
        }

        $response = json_decode($response, true);
        $response = $this->processResponse($response);

        return $response;
    }

    public function processResponse($response)
    {

        if (isset($response['status']) && $response['status'] == 'ok') {

            if (!empty($response['data'])) {
                $result = $response['data'];
            } else {
                $result = false;
            }

        } else {
            $result = false;
        }

        return $result;
    }

    public function getIndex($address)
    {
        $address = preg_split('/[ ,-]+/', trim($address));
        $address = implode('+', $address);

        $key_address = md5($address);
        $response = fn_get_session_data($key_address);

        if (empty($response)) {
            $url = "https://geocode-maps.yandex.ru/1.x/";
            $data = array(
                'geocode' => $address,
                'format' => 'json',
                'results' => 1
            );

            $response = Http::post($url, $data);

            fn_set_session_data($key_address, $response, YD_CACHE_DAY);
        }

        $response = json_decode($response, true);

        $address_line = $this->findElmArray($response, 'AddressLine');
        $address_line = reset($address_line);

        $result = '';
        $url = $this->url_api . 'getIndex';
        if (!empty($address_line) && !empty($this->client_ids)) {
            $data = array(
                'client_id' => $this->client_ids['client_id'],
                'sender_id' => $this->client_ids['sender_ids'],
                'address' => $address_line
            );

            $data['secret_key'] = $this->generateSecretKey('getIndex', $data);

            $result = $this->getStatic($data['secret_key']);

            if (empty($result)) {
                $result = $this->request($url, $data);
                $this->setStatic($data['secret_key'], $result);
            }
        }

        return $result;
    }

    public function getDeliveries()
    {
        $url = $this->url_api . 'getDeliveries';

        $data = array(
            'sender_id' => $this->client_ids['sender_ids'],
            'client_id' => $this->client_ids['client_id']
        );

        $data['secret_key'] = $this->generateSecretKey('getDeliveries', $data);

        $result = $this->request($url, $data);

        $deliveries = array();
        foreach($result['deliveries'] as $delivery) {
            if ($delivery['type'] == 'delivery') {
                $deliveries[] = $delivery;
            }
        }

        return $deliveries;
    }

    public static function getScheduleDays($schedules)
    {
        $last_to_day = -1;
        $last_from_day = -1;
        $last_day = 1;

        $days_same = array();
        $same_index = 0;

        foreach ($schedules as $key_day => $day) {

            $day['from'] = substr($day['from'], 0, strrpos($day['from'], ':', -1));
            $day['to'] = substr($day['to'], 0, strrpos($day['to'], ':', -1));

            if ($day['from'] == $last_from_day && $day['to'] == $last_to_day) {
                $days_same[$same_index]['last_day'] = $key_day + 1;
                $last_day = $key_day + 1;

            } else {
                $same_index++;
                $days_same[$same_index]['first_day'] = $key_day + 1;
                $days_same[$same_index]['last_day'] = $key_day + 1;
                $days_same[$same_index]['from'] = $day['from'];
                $days_same[$same_index]['to'] = $day['to'];

                if ($day['from'] == $day['to']) {
                    $days_same[$same_index]['all_day'] = true;
                }

                $last_day = $key_day + 1;
            }

            $last_from_day = $day['from'] ;
            $last_to_day = $day['to'];
        }

        if ($last_day < 7) {
            $same_index++;
            $days_same[$same_index]['first_day'] = $last_day + 1;
            $days_same[$same_index]['last_day'] = 7;
            $days_same[$same_index]['from'] = false;
        }

        foreach ($days_same as $key => $day) {
            if ($day['last_day'] == 7) {
                $days_same[$key]['last_day'] = 0;
            }

            if ($day['first_day'] == 7) {
                $days_same[$key]['first_day'] = 0;
            }
        }

        return $days_same;
    }

    protected function findElmArray($ar, $searchfor)
    {
        static $result = array();

        foreach($ar as $index => $data) {
            if (is_string($index) && $index == $searchfor) {
                $result[] = $data;
            }

            if (is_array($ar[$index])) {
                $this->findElmArray($data, $searchfor);
            }
        }
        return $result;
    }

    private static function setStatic($key, $object_data)
    {
        if (!self::$init_cache) {
            Registry::registerCache('yandex_delivery_cache_static', YD_CACHE_STATIC, Registry::cacheLevel('time'));
        }

        if (!empty($object_data)) {
            Registry::set('yandex_delivery_cache_static.' . $key, $object_data);
        }

        return true;
    }

    private static function getStatic($key)
    {
        if (!self::$init_cache) {
            Registry::registerCache('yandex_delivery_cache_static', YD_CACHE_STATIC, Registry::cacheLevel('time'));
        }

        return Registry::get('yandex_delivery_cache_static.' . $key);
    }
}
