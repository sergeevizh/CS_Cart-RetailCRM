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
use Tygh\Registry;
use Tygh\Http;
use Tygh\Shippings\RusPickpoint;

/**
 * Pickpoint shipping service
 */
class Pickpoint implements IService
{
    /**
     * Abailability multithreading in this module
     *
     * @var array $_allow_multithreading
     */
    private $_allow_multithreading = false;

    private $version = "1.0";

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

    public $date_zone;

    public $address_pickpoint;
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
     * @param  string $resonse Reponse from Shipping service server
     * @return string Text of error or false if no errors
     */
    public function processErrors($response)
    {
        return $return;
    }

    /**
     * Sets data to internal class variable
     *
     * @param array $shipping_info
     */
    public function prepareData($shipping_info)
    {
        $this->_shipping_info = $shipping_info;
        $this->company_id = Registry::get('runtime.company_id');

        if(!RusPickpoint::Login()) {
            $this->_internalError(RusPickpoint::$last_error);
        }
    }

    /**
     * Prepare request information
     *
     * @return array Prepared data
     */
    public function getRequestData()
    {
        $data = array();
        $pickpoint_info = Registry::get('addons.rus_pickpoint');
        $login = RusPickpoint::Login();
        $url = RusPickpoint::Url();
        $data_url = RusPickpoint::$data_url;

        $group_key = (isset($this->_shipping_info['keys']['group_key'])) ? $this->_shipping_info['keys']['group_key'] : 0;
        $shipping_id = (isset($this->_shipping_info['keys']['shipping_id'])) ? $this->_shipping_info['keys']['shipping_id'] : 0;

        if ($login) {
            $pickpoint_office = (!empty($_SESSION['cart']['pickpoint_office'])) ? $_SESSION['cart']['pickpoint_office'] : array();
            $weight_data = fn_expand_weight($this->_shipping_info['package_info']['W']);
            $shipping_settings = $this->_shipping_info['service_params'];
            $weight = round($weight_data['plain'] * Registry::get('settings.General.weight_symbol_grams') / 1000, 3);

            $location = !empty($this->_shipping_info['package_info']['location']) ? $this->_shipping_info['package_info']['location'] : '';
            $packages = !empty($this->_shipping_info['package_info']['packages']) ? $this->_shipping_info['package_info']['packages'] : array();
            $origination = !empty($this->_shipping_info['package_info']['origination']) ? $this->_shipping_info['package_info']['origination'] : '';

            $from_state = fn_get_state_name($origination['state'], $origination['country'], 'RU');

            $length = !empty($shipping_settings['pickpoint_length']) ? $shipping_settings['pickpoint_length'] : 10;
            $width = !empty($shipping_settings['pickpoint_width']) ? $shipping_settings['pickpoint_width'] : 10;
            $height = !empty($shipping_settings['pickpoint_height']) ? $shipping_settings['pickpoint_height'] : 10;

            if (!empty($this->_shipping_info['package_info']['packages'])) {
                $packages = $this->_shipping_info['package_info']['packages'];
                $packages_count = count($packages);
                $pickpoint_weight = $pickpoint_length = $pickpoint_width = $pickpoint_height = 0;
                if ($packages_count > 0) {
                    foreach ($packages as $id => $package) {
                        $package_length = empty($package['shipping_params']['box_length']) ? $length : $package['shipping_params']['box_length'];
                        $package_width = empty($package['shipping_params']['box_width']) ? $width : $package['shipping_params']['box_width'];
                        $package_height = empty($package['shipping_params']['box_height']) ? $height : $package['shipping_params']['box_height'];
                        $weight_ar = fn_expand_weight($package['weight']);
                        $package_weight = round($weight_ar['plain'] * Registry::get('settings.General.weight_symbol_grams') / 1000, 3);

                        $pickpoint_weight = $pickpoint_weight + $package_weight;
                        $pickpoint_length = $pickpoint_length + $package_length;
                        $pickpoint_width = $pickpoint_width + $package_width;
                        $pickpoint_height = $pickpoint_height + $package_height;
                    }
                    $length = $pickpoint_length;
                    $width = $pickpoint_width;
                    $height = $pickpoint_height;
                    $weight = $pickpoint_weight;
                }
            } else {
                $packages_count = 1;
                $weight = round($weight_data['plain'] * Registry::get('settings.General.weight_symbol_grams') / 1000, 3);
            }

            $sid = RusPickpoint::$sid;
            $data_zone = array(
                'SessionId' => $sid,
                'FromCity' => $origination['city']
            );
            $url_zone = $url . 'getzone';

            $pickpoint_id = '';
            if (!empty($pickpoint_office[$group_key][$shipping_id]['pickpoint_id'])) {
                $pickpoint_id = $pickpoint_office[$group_key][$shipping_id]['pickpoint_id'];
                $this->address_pickpoint = $pickpoint_office[$group_key][$shipping_id]['address_pickpoint'];

                $data_zone['ToPT'] = $pickpoint_id;
                $pickpoint_zone = RusPickpoint::zonesPickpoint($url_zone, $data_zone, $data_url);
                if (!empty($pickpoint_zone)) {
                    if ($pickpoint_zone['delivery_min'] == $pickpoint_zone['delivery_max']) {
                        $this->date_zone = $pickpoint_zone['delivery_max'] . ' ' . __('days');
                    } else {
                        $this->date_zone = $pickpoint_zone['delivery_min'] . '-' . $pickpoint_zone['delivery_max'] . ' ' . __('days');
                    }
                }
            } else {
                $city = (!empty($location['city'])) ? $location['city'] : '';
                $this->address_pickpoint = RusPickpoint::findPostamatPickpoint($pickpoint_id, $city);
                $data_zone['ToPT'] = $pickpoint_id;
                $pickpoint_zone = RusPickpoint::zonesPickpoint($url_zone, $data_zone, $data_url);
                if (!empty($pickpoint_zone)) {
                    $pickpoint_id = (!empty($pickpoint_zone['to_pt'])) ? $pickpoint_zone['to_pt'] : '';
                    if ($pickpoint_zone['delivery_min'] == $pickpoint_zone['delivery_max']) {
                        $this->date_zone = $pickpoint_zone['delivery_max'] . ' ' . __('days');
                    } else {
                        $this->date_zone = $pickpoint_zone['delivery_min'] . '-' . $pickpoint_zone['delivery_max'] . ' ' . __('days');
                    }
                }
            }

            if (!empty($pickpoint_id) && !empty($this->address_pickpoint)) {
                $_SESSION['cart']['pickpoint_office'][$group_key][$shipping_id]['pickpoint_id'] = $pickpoint_id;
                $_SESSION['cart']['pickpoint_office'][$group_key][$shipping_id]['address_pickpoint'] = $this->address_pickpoint;
            }

            $data = array(
                'SessionId' => $sid,
                'IKN' => $pickpoint_info['ikn'],
                'FromCity' => $origination['city'],
                'FromRegion' => $from_state,
                'PTNumber' => $pickpoint_id,
                'EncloseCount' => $packages_count,
                'Length' => $length,
                'Depth' => $height,
                'Width' => $width,
                'Weight' => $weight
            );
        } else {
            fn_set_notification('E', __('notice'), RusPickpoint::$last_error);
        }

        $request_data = array(
            'method' => 'post',
            'url' => $url . 'calctariff',
            'data' => json_encode($data),
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
        $data = $this->getRequestData();
        $response = Http::post($data['url'], $data['data'], $data['data_url']);

        return $response;
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

        $cost = 0;
        $result = json_decode($response);
        $data_services = json_decode(json_encode($result), true);
        if (isset($data_services['Error']) && ($data_services['Error'] == 1) && !empty($data_services['ErrorMessage'])){
            $this->_internalError($data_services['ErrorMessage']);

        } elseif (isset($data_services['Error']) && !empty($data_services['Error'])) {
            $this->_internalError($data_services['Error']);

        } elseif (isset($data_services['Services'])) {

            foreach ($data_services['Services'] as $service) {
                $cost += $service['Tariff'] + $service['NDS'];
            }

            $shipment = reset($data_services['Services']);

            $_result = array(
                'Tariff_Type' => $shipment['Name'],
                'Total_Dost' => $shipment['Tariff'],
                'Total_DopUsl' => $shipment['NDS'],
                'Cost' => $cost
            );

            if (!empty($_result)) {
                $this->_fillSessionPostamat($_result);
            }

            $return['cost'] = $cost;
            $return['delivery_time'] = $this->date_zone;
        }

        if (!empty($this->_error_stack)) {
            foreach ($this->_error_stack as $error) {
                $return['error'] .= $error . '; ';
            }
        }

        RusPickpoint::Logout();

        return $return;
    }

    private function _fillSessionPostamat($pickpoint_postamat)
    {
        if (isset($this->_shipping_info['keys']['group_key']) && isset($this->_shipping_info['keys']['shipping_id'])) {
            $group_key = $this->_shipping_info['keys']['group_key'];
            $shipping_id = $this->_shipping_info['keys']['shipping_id'];
            $_SESSION['cart']['shippings_extra']['data'][$group_key][$shipping_id]['pickpoint_postamat'] = $pickpoint_postamat;
        }

        return true;
    }

    public function prepareAddress($address)
    {
        
    }
}
