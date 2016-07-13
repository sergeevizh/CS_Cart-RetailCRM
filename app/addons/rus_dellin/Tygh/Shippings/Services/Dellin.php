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
use Tygh\Bootstrap;

/**
 * Dellin shipping service
 */
class Dellin implements IService
{
    /**
     * Abailability multithreading in this module
     *
     * @var array $_allow_multithreading
     */
    private $_allow_multithreading = false;

    /**
     * Current Company id environment
     *
     * @var int $company_id
     */
    public $company_id = 0;

    public $session_id = 0;

    public $url_params = array(
        'headers' => array('Content-Type: application/json'),
        'timeout' => 5
    );

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
    public function processErrors($return)
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
    }

    /**
     * Prepare request information
     *
     * @return array Prepared data
     */
    public function getRequestData()
    {
        $request_data =array();
        $weight_data = fn_expand_weight($this->_shipping_info['package_info']['W']);
        $shipping_settings = $this->_shipping_info['service_params'];
        $origination = $this->_shipping_info['package_info']['origination'];
        $location = $this->_shipping_info['package_info']['location'];
        $packages = fn_get_schema('dellin', 'packages', 'php', true);
        $services = fn_get_schema('dellin', 'services', 'php', true);
        $symbol_weight = Registry::get('settings.General.weight_symbol_grams');

        $post = array (
            'appKey' => $shipping_settings['appkey']
        );

        $url_cities = "https://api.dellin.ru/v1/public/cities.json";
        $c_file = $this->_addDellinCities($url_cities, $post);

        if ($c_file) {
            if (!empty($shipping_settings['individual_calculator']) && ($shipping_settings['individual_calculator'] == 'Y')) {
                if (!empty($shipping_settings['login']) && !empty($shipping_settings['password'])) {
                    $post['login'] = $shipping_settings['login'];
                    $post['password'] = $shipping_settings['password'];
                }

                $url_login = "https://api.dellin.ru/v1/customers/login.json";
                $response = Http::post($url_login, json_encode($post), $this->url_params);
                $data_session = (array) json_decode($response);

                if (!empty($data_session['sessionID'])) {
                    unset($post['login']);
                    unset($post['password']);
                    $post['sessionID'] = $data_session['sessionID'];
                    $this->session_id = $data_session['sessionID'];
                }
            }

            $post['derivalPoint'] = '';
            if (!empty($origination['city'])) {
                $post['derivalPoint'] = db_get_field("SELECT code_kladr FROM ?:rus_dellin_cities WHERE city LIKE ?l", "%" . $origination['city'] . "%");
            }

            $post['arrivalPoint'] = '';
            if (!empty($location['city'])) {
                $post['arrivalPoint'] = db_get_field("SELECT code_kladr FROM ?:rus_dellin_cities WHERE city LIKE ?l", "%" . $location['city'] . "%");
            }

            $post['derivalDoor'] = ($shipping_settings['derival_door'] == 'Y') ? true : false;
            $post['arrivalDoor'] = ($shipping_settings['arrival_door'] == 'Y') ? true : false;

            if (!empty($packages[$shipping_settings['package']])) {
                $post['packages'] = $packages[$shipping_settings['package']];
            }

            if (!empty($shipping_settings['derival_services'])) {
                foreach ($shipping_settings['derival_services'] as $service) {
                    if (!empty($services[$service])) {
                        $post['derivalServices'][] = $services[$service];
                    }
                }
            }

            if (!empty($shipping_settings['arrival_services'])) {
                foreach ($shipping_settings['arrival_services'] as $service) {
                    if (!empty($services[$service])) {
                        $post['arrivalServices'][] = $services[$service];
                    }
                }
            }

            $weight = round($weight_data['plain'] * $symbol_weight / 1000, 3);
            $length = !empty($shipping_settings['length']) ? $shipping_settings['length'] : 10;
            $width = !empty($shipping_settings['width']) ? $shipping_settings['width'] : 10;
            $height = !empty($shipping_settings['height']) ? $shipping_settings['height'] : 10;

            $packages = (!empty($this->_shipping_info['package_info']['packages'])) ? $this->_shipping_info['package_info']['packages'] : array();
            $packages_count = count($packages);

            if ($packages_count > 0) {
                $p_weight = 0;
                $p_length = $p_width = $p_height = 0;
                foreach ($packages as $id => $package) {
                    $package_length = empty($package['shipping_params']['length']) ? $length : $package['shipping_params']['length'];
                    $package_width = empty($package['shipping_params']['width']) ? $width : $package['shipping_params']['width'];
                    $package_height = empty($package['shipping_params']['height']) ? $height : $package['shipping_params']['height'];
                    $product_weight = fn_expand_weight($package['weight']);
                    $package_weight = round($product_weight['plain'] * $symbol_weight / 1000, 3);

                    $p_length += $package_length;
                    $p_width += $package_width;
                    $p_height += $package_height;
                    $p_weight += $package_weight;
                }

                $length = $p_length;
                $width = $p_width;
                $height = $p_height;
                $weight = $p_weight;

                $post['statedValue'] = $this->_shipping_info['package_info']['C'];
            }

            $post['sizedWeight'] = $weight;
            $post['length'] = $length / 100;
            $post['width'] = $width / 100;
            $post['height'] = $height / 100;
            $post['sizedVolume'] = $post['length'] * $post['width'] * $post['height'];
            $post['quantity'] = (!empty($packages_count)) ? $packages_count : 1;

            $url = 'https://api.dellin.ru/v1/public/calculator.json';
            $request_data = array(
                'method' => 'post',
                'url' => $url,
                'data' => $post,
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

        $response = Http::post($data['url'], json_encode($data['data']), $this->url_params);

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
        $shipping_settings = $this->_shipping_info['service_params'];

        $result = json_decode($response);
        $data_dellin = json_decode(json_encode($result), true);

        if (!empty($data_dellin['errors'])) {
            if (is_array($data_dellin['errors'])) {
                foreach ($data_dellin['errors'] as $error) {
                    if (is_array($error)) {
                        foreach ($error as $message) {
                            $return['error'] .= '; ' . $message;
                        }
                    } else {
                        $return['error'] .= '; ' . $error;
                    }
                }
            } else {
                $return['error'] = $data_dellin['errors'];
            }

        } elseif (!empty($data_dellin['price'])) {
            $return['cost'] = $data_dellin['price'];
            $return['delivery_time'] = '1 ' . __('day');
            if (!empty($data_dellin['time']['value'])) {
                $return['delivery_time'] = (!empty($data_dellin['time']['nominative'])) ? $data_dellin['time']['nominative'] : '1 ' . __('day');
            }

            $arrival_terminals = array();
            foreach ($data_dellin['arrival']['terminals'] as $terminal) {
                $arrival_terminals[md5($terminal['name'])] = $terminal;
                $arrival_terminals[md5($terminal['name'])]['code'] = md5($terminal['name']);
            }

            $return['derival_terminals'] = $data_dellin['derival']['terminals'];
            $return['arrival_terminals'] = $arrival_terminals;

            $this->_fillSessionData($return['derival_terminals'], $return['arrival_terminals']);

            if ($shipping_settings['avia_delivery'] && !empty($data_dellin['air']['price'])) {
                $return['cost'] += $data_dellin['air']['price'];
            }

            if ($shipping_settings['small_delivery'] && !empty($data_dellin['small']['price'])) {
                $return['cost'] += $data_dellin['small']['price'];
            }

            if ($shipping_settings['express_delivery'] && !empty($data_dellin['express']['price'])) {
                $return['cost'] += $data_dellin['express']['price'];
            }
        }

        return $return;
    }

    private function _fillSessionData($derival_terminals, $arrival_terminals)
    {
        $group_key = $this->_shipping_info['keys']['group_key'];
        $shipping_id = $this->_shipping_info['keys']['shipping_id'];

        $_SESSION['cart']['shippings_extra']['data'][$group_key][$shipping_id] = array(
            'derival_terminals' => $derival_terminals,
            'arrival_terminals' => $arrival_terminals
        );

        return true;
    }

    public function _addDellinCities($url_cities, $post)
    {
        $file_dir = fn_get_files_dir_path() . "dellin/";
        fn_mkdir($file_dir);
        @chmod($file_dir, 0777);
        $file_path = $file_dir . date("Y-m-d", TIME) . '_cities.csv';
        $data_dellin = db_get_array("SELECT * FROM ?:rus_dellin_cities");

        if (!file_exists($file_path) || empty($data_dellin)) {
            $response = Http::post($url_cities, json_encode($post), $this->url_params);
            $result = (array) json_decode($response);

            if (!empty($result['errors']) && empty($result['url'])) {
                if (AREA == 'A') {
                    fn_set_notification('E', __('warning'), __('shipping.rus_dellin.file_not_upload'));
                }
            } else {
                file_put_contents($file_path, file_get_contents($result['url']));

                if (filesize($file_path) == 0) {
                    if (AREA == 'A') {
                        fn_set_notification('E', __('warning'), __('shipping.rus_dellin.file_not_upload'));
                    }

                    return false;
                }

                if (!empty($result['url'])) {
                    $max_line_size = 65536; // 64 Кб
                    $data_city = array();
                    $delimiter = ',';
                    $encoding = fn_detect_encoding($result['url'], 'F', CART_LANGUAGE);

                    if (!empty($encoding)) {
                        $result['url'] = fn_convert_encoding($encoding, 'UTF-8', $result['url'], 'F');
                    } else {
                        fn_set_notification('W', __('warning'), __('text_exim_utf8_file_format'));
                    }

                    $f = false;
                    if ($result['url'] !== false) {
                        $f = fopen($result['url'], 'rb');
                    }

                    if ($f) {
                        $import_schema = fgetcsv($f, $max_line_size, $delimiter);
                        $schema_size = sizeof($import_schema);
                        $skipped_lines = array();
                        $line_it = 1;
                        while (($data = fn_fgetcsv($f, $max_line_size, $delimiter)) !== false) {
                            $line_it ++;
                            if (fn_is_empty($data)) {
                                continue;
                            }

                            if (sizeof($data) != $schema_size) {
                                $skipped_lines[] = $line_it;
                                continue;
                            }

                            $data = str_replace(array('\r', '\n', '\t', '"'), '', $data);
                            $data_city = array_combine($import_schema, Bootstrap::stripSlashes($data));
                            if (!empty($data_city)) {
                                $dellin_city = array(
                                    'number_city' => $data_city['id'],
                                    'code_kladr' => str_replace(' ', '', $data_city['codeKLADR']),
                                    'is_terminal' => $data_city['isTerminal']
                                );

                                $first_pos = strpos($data_city['name'], '(');
                                $end_pos = strpos($data_city['name'], ')') - $first_pos;
                                if (!empty($first_pos)) {
                                    $dellin_city['state'] = str_replace(array("(", ")"), "", substr($data_city['name'], $first_pos, $end_pos));
                                    $dellin_city['city'] = str_replace(array('(' . $dellin_city['state'] . ')', '"'), "", $data_city['name']);
                                } else {
                                    $dellin_city['state'] = str_replace(array('г.', 'г', 'г. ', 'г '), '', $data_city['name']);
                                    $dellin_city['city'] = $data_city['name'];
                                }

                                $dellin_city['city_id'] = db_get_field("SELECT city_id FROM ?:rus_dellin_cities WHERE code_kladr = ?s", $dellin_city['code_kladr']);
                                db_query("REPLACE INTO ?:rus_dellin_cities ?e", $dellin_city);
                            }
                        }
                    }
                }
            }
        }

        return true;
    }

    public function prepareAddress($address)
    {
        
    }

}
