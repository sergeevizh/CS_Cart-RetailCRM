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

use Tygh\Shippings\IService;
use Tygh\Http;

/**
 * UPS shipping service
 */
class Pecom implements IService
{
    /**
     * Availability multithreading in this module
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

        $cost = $this->_getRates($response);
        if (empty($this->_error_stack) && !empty($cost)) {
            $return['cost'] = $cost;
            $this->_getPeriods($response);

        } else {
            $return['error'] = $this->processErrors($response);

        }

        return $return;
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
        $volume = 0;
        $maximus = array(
            'length' => 0,
            'width' => 0,
            'height' => 0,
        );

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

        return array($length / 100, $width / 100, $height / 100);
    }

    private function _getRates($response)
    {
        $shipping_info = $this->_shipping_info['service_params'];
        $tarif = $shipping_info['tarif'];

        if (!empty($response[$tarif][2])) {
            $cost = $response[$tarif][2];
        } else {
            $cost = false;
            $this->_internalError(__('rus_pecom.not_rate_delivery'));
        }

        if (!empty($cost)) {
            if ($shipping_info['take'] == 'Y' && !empty($response['take'][2])) {
                $cost += $response['take'][2];
            }

            if ($shipping_info['deliver'] == 'Y' && !empty($response['deliver'][2])) {
                $cost += $response['deliver'][2];
            }

            if ($shipping_info['package_hard'] == 'Y' && !empty($response['ADD'][2])) {
                $cost += $response['ADD'][2];
            }

            if ($shipping_info['pal'] == 'Y' && !empty($response['ADD_2'][2])) {
                $cost += $response['ADD_2'][2];
            }

            if ($shipping_info['insurance'] == 'Y' && !empty($response['ADD_3'][2])) {
                $cost += $response['ADD_3'][2];
            }

            if (!empty($response['alma_auto'][2])) {
                $cost += $response['alma_auto'][2];
            }
        }

        return $cost;
    }

    /**
     * Gets error message from shipping service server
     *
     * @param  string $resonse Reponse from Shipping service server
     * @return string Text of error or false if no errors
     */
    public function processErrors($json_response)
    {
        if (!empty($json_response['rsp']['err'])) {
            $error = $json_response['rsp']['err']['code'] . ': ' . $json_response['rsp']['err']['msg'];
        } else {
            $error = __('service_not_available');
        }

        if (!empty($this->_error_stack)) {
            foreach ($this->_error_stack as $_error) {
                $error .= '; ' . $_error;
            }
        }

        return $error;
    }

    /**
     * Prepare request information
     *
     * @return array Prepared data
     */
    public function getRequestData()
    {
        $weight_data = fn_expand_weight($this->_shipping_info['package_info']['W']);
        $shipping_settings = $this->_shipping_info['service_params'];
        $packages = $this->_shipping_info['package_info']['packages'];

        list($length, $width, $height) = $this->getPackageValues();
        $volume = round($length * $width * $height, 2);

        $data['places'][] = array(
            $width,
            $length,
            $height,
            $volume,
            $this->_shipping_info['package_info']['W'],
            1,
            $shipping_settings['package_hard'] == "Y" ? 1 : 0
        );

        $data['take'] = array(
            'town' => $this->_getCityId('from'),
            'tent' => $shipping_settings['take_tent'] == "Y" ? 1 : 0 ,
            'gidro' => $shipping_settings['take_gidro'] == "Y" ? 1 : 0,
            'speed' => $shipping_settings['take_speed'] == "Y" ? 1 : 0,
            'moscow' => $shipping_settings['take_moscow'],
        );

        $data['deliver'] = array(
            'town' => $this->_getCityId('to'),
            'tent' => $shipping_settings['deliver_tent'] == "Y" ? 1 : 0,
            'gidro' => $shipping_settings['deliver_gidro'] == "Y" ? 1 : 0,
            'speed' => $shipping_settings['deliver_speed'] == "Y" ? 1 : 0,
            'moscow' => $shipping_settings['deliver_moscow'],
        );

        if ($shipping_settings['pal'] == 'Y') {
            $data['pal'] = 1;
        } else {
            $data['pal'] = 0;
        }

        if ($shipping_settings['insurance'] == 'Y') {
            $data['strah'] = $this->_shipping_info['package_info']['C'];
        }

        $url = 'http://pecom.ru/bitrix/components/pecom/calc/ajax.php';
        $request_data = array(
            'method' => 'get',
            'url' => $url,
            'data' => $data,
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
        $data = $this->getRequestData();
        $key = md5(serialize($data['data']));
        $pecom_data = fn_get_session_data($key);
        if (empty($pecom_data)) {
            $response = Http::get($data['url'], $data['data']);
            $response = json_decode($response, true);
            fn_set_session_data($key, $response);
        } else {
            $response = $pecom_data;
        }

        return $response;
    }

    public function _getCityID($type)
    {
        if ($type == 'from') {
            $destination = $this->_shipping_info['package_info']['origination'];
        } elseif ($type == 'to') {
            $destination = $this->_shipping_info['package_info']['location'];
        }

        $cities_simple = fn_get_schema('pecom', 'cities_simple');

        if (!empty($cities_simple[$destination['city']])) {
            $city_code = $cities_simple[$destination['city']];
        }

        if (empty($city_code)) {
            $cities_full = fn_get_schema('pecom', 'cities_full');
            $alt_city = $destination['city'] . ' (' . fn_get_state_name($destination['state'], $destination['country'], 'RU') . ')';

            if (isset($cities_full[$alt_city])) {
                $city_code = $cities_full[$alt_city];

            } elseif (isset($cities_full[$destination['city']])) {
                $city_code = $cities_full[$destination['city']];

            } else {
                $city_code = false;
                $this->_internalError(__('rus_pecom.not_city_code'));
            }
        }

        return $city_code;
    }

    private function _getPeriods($response)
    {
        $delivery_time = '';

        $shipping_info = $this->_shipping_info['service_params'];
        $group_key = $this->_shipping_info['keys']['group_key'];
        $shipping_id = $this->_shipping_info['keys']['shipping_id'];
        $tarif = $shipping_info['tarif'];

        if (!empty($response['periods']) && ($tarif == 'auto')) {
            $_SESSION['cart']['shippings_extra']['data'][$group_key][$shipping_id]['periods'] = $response['periods'];
            $response['periods'] = str_replace('<br/>', ' ', $response['periods']);
            $delivery_time = strip_tags($response['periods']);
        }

        if (!empty($response['aperiods']) && ($tarif == 'avia')) {
            $aperiods = explode("<br/>", $response['aperiods']);

            foreach ($aperiods as $aperiod) {
                if (strpos($aperiod, 'ss') !== false) {
                    if ($shipping_info['take'] == 'Y' && $shipping_info['deliver'] == 'N') {
                        $delivery_time = strip_tags($aperiod);
                    }
                }

                if (strpos($aperiod, 'sd') !== false) {
                    if ($shipping_info['take'] == 'N' && $shipping_info['deliver'] == 'N') {
                        $delivery_time = strip_tags($aperiod);
                    }
                }

                if (strpos($aperiod, 'ds') !== false) {
                    if ($shipping_info['take'] == 'N' && $shipping_info['deliver'] == 'Y') {
                        $delivery_time = strip_tags($aperiod);
                    }
                }

                if (strpos($aperiod, 'dd') !== false) {
                    if ($shipping_info['take'] == 'Y' && $shipping_info['deliver'] == 'Y') {
                        $delivery_time = strip_tags($aperiod);
                    }
                }
            }
        }

        if (!empty($delivery_time)) {
            $_SESSION['cart']['shippings_extra']['data'][$group_key][$shipping_id]['delivery_time'] = $delivery_time;
        }

        return $delivery_time;
    }

    public function prepareAddress($address)
    {
        
    }
}
