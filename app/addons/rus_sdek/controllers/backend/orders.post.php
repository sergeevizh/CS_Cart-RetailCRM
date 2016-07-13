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
use Tygh\Http;
use Tygh\Shippings\RusSdek;
use Tygh\Mailer;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

$sdek_delivery = fn_get_schema('sdek', 'sdek_delivery', 'php', true);
$currency_sdek = fn_get_schema('sdek', 'currency_sdek', 'php', true);
$currencies = Registry::get('currencies');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $params = $_REQUEST;
    $order_info = fn_get_order_info($params['order_id'], false, true, true, true);
    $default_currency = (!empty($order_info['secondary_currency'])) ? $order_info['secondary_currency'] : CART_PRIMARY_CURRENCY;

    if ($mode == 'sdek_order_delivery') {
        if (empty($params['add_sdek_info'])) {
            return false;
        }

        foreach ($params['add_sdek_info'] as $shipment_id => $sdek_info) {
            list($_shipments, $search) = fn_get_shipments_info(array('order_id' => $params['order_id'], 'advanced_info' => true, 'shipment_id' => $shipment_id));

            $shipment = reset($_shipments);

            $params_shipping = array(
                'shipping_id' => $shipment['shipping_id'],
                'Date' => date("Y-m-d", $shipment['shipment_timestamp'])
            );

            $data_auth = RusSdek::dataAuth($params_shipping);

            if (empty($data_auth)) {
                continue;
            }

            $order_for_sdek = $sdek_info['Order'];

            $lastname = "";
            if (!empty($order_info['lastname'])) {
                $lastname = $order_info['lastname'];

            } elseif (!empty($order_info['s_lastname'])) {
                $lastname = $order_info['s_lastname'];

            } elseif (!empty($order_info['b_lastname'])) {
                $lastname = $order_info['b_lastname'];
            }
            $firstname = "";
            if (!empty($order_info['firstname'])) {
                $firstname = $order_info['firstname'];

            } elseif (!empty($order_info['s_firstname'])) {
                $firstname = $order_info['s_firstname'];

            } elseif (!empty($order_info['b_firstname'])) {
                $firstname = $order_info['b_firstname'];
            }

            $order_for_sdek['RecipientName'] = $lastname . ' ' . $firstname;

            if (!empty($order_info['phone'])) {
                $order_for_sdek['Phone'] = $order_info['phone'];

            } elseif (!empty($order_info['s_phone'])) {
                $order_for_sdek['Phone'] = $order_info['s_phone'];

            } elseif (!empty($order_info['b_phone'])) {
                $order_for_sdek['Phone'] = $order_info['b_phone'];
            }

            if (empty($order_for_sdek['Phone'])) {
                $order_for_sdek['Phone'] = '-';
            }

            if ($order_info['b_country'] != 'RU' && $order_info['s_country'] != 'RU') {
                $data_auth['ForeignDelivery'] = 1;

                if (!empty($currency_sdek[$order_info['s_country']])) {
                    $data_auth['Currency'] = $currency_sdek[$order_info['s_country']];
                } elseif (!empty($currency_sdek[$order_info['b_country']])) {
                    $data_auth['Currency'] = $currency_sdek[$order_info['b_country']];
                } else {
                    $data_auth['Currency'] = CART_PRIMARY_CURRENCY;
                }

                $order_for_sdek['SellerAddress'] = Registry::get('runtime.company_data.city') . ', ' . Registry::get('runtime.company_data.address');
                $order_for_sdek['ShipperName'] = Registry::get('runtime.company_data.company');
                $order_for_sdek['ShipperAddress'] = $order_for_sdek['SellerAddress'];
            }

            $sdek_products = array();
            $weight = 0;

            foreach ($shipment['products'] as $item_id => $amount) {
                $data_product = $order_info['products'][$item_id];

                $product_weight = db_get_field("SELECT weight FROM ?:products WHERE product_id = ?i", $data_product['product_id']);

                if (!empty($product_weight) && $product_weight != 0) {
                    $product_weight = $product_weight;
                } else {
                    $product_weight = 0.01;
                }

                if (!empty($data_product['price']) && $data_product['price'] != 0) {
                    $price = $data_product['price'] - ($data_product['price']/$order_info['subtotal'] * $order_info['subtotal_discount']);
                    $total = $price * $amount;
                } else {
                    $price = "0.00";
                    $total = "0.00";
                }

                $ware_key = (!empty($data_product['product_code'])) ? $data_product['product_code'] : $data_product['product_id'];
                $sdek_products[] = array(
                    'ware_key' => $ware_key,
                    'product' => $data_product['product'],
                    'price' => $price,
                    'amount' => $amount,
                    'total' => $total,
                    'weight' => $amount * $product_weight,
                    'order_id' => $params['order_id'],
                    'shipment_id' => $shipment_id,
                );
                $weight = $weight + ($amount * $product_weight);
            }

            $weight = $weight * Registry::get('settings.General.weight_symbol_grams');

            $order_for_sdek['SellerName'] = Registry::get('runtime.company_data.company');

            $data_auth['Number'] = $params['order_id'] . '_' . $shipment_id;

            $data_auth['OrderCount'] = "1";

            $xml = RusSdek::arraySimpleXml('DeliveryRequest', $data_auth, 'open');

            $order_for_sdek['Number'] = $params['order_id'] . '_' . $shipment_id;
            $order_for_sdek['DateInvoice'] = date("Y-m-d", $shipment['shipment_timestamp']);
            $order_for_sdek['RecipientEmail'] = $order_info['email'];
            $order_for_sdek['DeliveryRecipientCost'] = (!empty($order_for_sdek['DeliveryRecipientCost'])) ? $order_for_sdek['DeliveryRecipientCost'] : "0.00";

            if (!empty($data_auth['Currency'])) {
                if (!empty($currencies[$data_auth['Currency']])) {
                    $order_for_sdek['DeliveryRecipientCost'] = fn_format_price_by_currency($order_for_sdek['DeliveryRecipientCost'], $data_auth['Currency'], $default_currency);
                } else {
                    $order_for_sdek['DeliveryRecipientCost'] = '0.00';
                }
            }

            $xml .= RusSdek::arraySimpleXml('Order', $order_for_sdek, 'open');

            if (!empty($sdek_info['Address'])) {
                $xml .= RusSdek::arraySimpleXml('Address', $sdek_info['Address']);
            }

            $sdek_barcode = (!empty($sdek_info['barcode'])) ? $sdek_info['barcode'] : "_";

            $package_for_xml = array (
                'Number' => $shipment_id,
                'BarCode' => $sdek_barcode,
                'Weight' => $weight
            );
            $xml .= RusSdek::arraySimpleXml('Package', $package_for_xml, 'open');

            foreach ($sdek_products as $k => $product) {
                $payment = '0.00';
                if (!empty($sdek_info['use_imposed']) && $sdek_info['use_imposed'] == 'Y') {
                    $payment = (!empty($sdek_info['CashDelivery'])) ? $sdek_info['CashDelivery'] : '0.00';

                    if (!empty($sdek_info['use_product']) && $sdek_info['use_product'] == 'Y') {
                        $payment += $product['price'];
                    }
                }

                $cost = $product['price'];
                if (!empty($data_auth['Currency'])) {
                    if (!empty($currencies[$data_auth['Currency']])) {
                        $cost = fn_format_price_by_currency($cost, $data_auth['Currency'], $default_currency);
                        $payment = fn_format_price_by_currency($payment, $data_auth['Currency'], $default_currency);
                    } else {
                        $cost = '0.00';
                        $payment = '0.00';
                    }
                }

                if ($cost == 0) {
                    $cost = '0.00';
                }

                if ($payment == 0) {
                    $payment = '0.00';
                }

                $product_for_xml = array (
                    'WareKey' => $product['ware_key'],
                    'Cost' => $cost,
                    'Payment' => $payment,
                    'Weight' => $product['weight'] * Registry::get('settings.General.weight_symbol_grams'),
                    'Amount' => $product['amount'],
                    'Comment' => $product['product'],
                );

                if ($order_info['b_country'] != 'RU' && $order_info['b_country'] != 'RU') {
                    $product_for_xml['CostEx'] = $cost;
                    $product_for_xml['PaymentEx'] = $payment;
                }

                $xml .= RusSdek::arraySimpleXml('Item', $product_for_xml);
            }

            $xml .= '</Package>';

            if (!empty($sdek_info['Schedule']['TimeBeg']) && !empty($sdek_info['Schedule']['TimeEnd'])) {
                $xml .= '<Schedule>';

                if (!empty($sdek_info['Schedule']['DeliveryRecipientCost'])) {
                    unset($sdek_info['Schedule']['DeliveryRecipientCost']);
                }
                $count_schedule = db_get_field("SELECT count(*) FROM ?:rus_sdek_call_recipient ");
                $sdek_info['Schedule']['ID'] = $count_schedule + 1;
                $sdek_info['Schedule']['Date'] = date("Y-m-d", strtotime($sdek_info['Schedule']['Date']));
                $xml .= RusSdek::arraySimpleXml('Attempt', $sdek_info['Schedule']);

                $call_recipient = array(
                    'order_id' => $params['order_id'],
                    'shipment_id' => $shipment_id,
                    'timestamp' => TIME,
                    'shipment_date' => $sdek_info['Schedule']['Date'],
                    'timebag' => $sdek_info['Schedule']['TimeBeg'],
                    'timeend' => $sdek_info['Schedule']['TimeEnd'],
                    'recipient_name' => $sdek_info['Schedule']['RecipientName'],
                    'phone' => $sdek_info['Schedule']['Phone'],
                    'call_comment' => $sdek_info['Schedule']['Comment'],
                );

                if (!empty($sdek_delivery[$sdek_info['Order']['TariffTypeCode']]['terminals']) && $sdek_delivery[$sdek_info['Order']['TariffTypeCode']]['terminals'] == 'N') {
                    $call_recipient['address'] = $sdek_info['Address']['Street'];
                } else {
                    $call_recipient['pvz_code'] = $sdek_info['Address']['PvzCode'];
                }

                $xml .= '</Schedule>';
            }

            if (!empty($sdek_info['CallCourier']['Date']) && !empty($sdek_info['CallCourier']['TimeBeg']) && !empty($sdek_info['CallCourier']['TimeEnd'])) {
                $xml .= '<CallCourier>';

                $sdek_info['CallCourier']['Date'] = date("Y-m-d", strtotime($sdek_info['CallCourier']['Date']));
                $sdek_info['CallCourier']['SendCityCode'] = $sdek_info['Order']['SendCityCode'];
                $sdek_info['CallCourier']['SendPhone'] = Registry::get('runtime.company_data.phone');
                $sdek_info['CallCourier']['SenderName'] = Registry::get('runtime.company_data.company');
                $xml .= RusSdek::arraySimpleXml('Call', $sdek_info['CallCourier'], 'open');

                $address_send = array(
                    'Street' => Registry::get('runtime.company_data.address'),
                    'House' => '-',
                    'Flat' => '-',
                );
                $xml .= RusSdek::arraySimpleXml('SendAddress', $address_send);

                $call_courier = array(
                    'order_id' => $params['order_id'],
                    'shipment_id' => $shipment_id,
                    'timestamp' => TIME,
                    'call_courier_date' => $sdek_info['CallCourier']['Date'],
                    'timebag' => $sdek_info['CallCourier']['TimeBeg'],
                    'timeend' => $sdek_info['CallCourier']['TimeEnd'],
                    'lunch_timebag' => $sdek_info['CallCourier']['LunchBeg'],
                    'lunch_timeend' => $sdek_info['CallCourier']['LunchEnd'],
                    'weight' => $weight,
                    'comment_courier' => $sdek_info['CallCourier']['Comment'],
                );

                $xml .= '</Call>';
                $xml .= '</CallCourier>';
            }

            $xml .= '</Order>';
            $xml .= '</DeliveryRequest>';

            $response = RusSdek::xmlRequest('http://gw.edostavka.ru:11443/new_orders.php', $xml, $data_auth);
            
            $result = RusSdek::resultXml($response);

            if (empty($result['error'])) {

                $register_data = array(
                    'order_id' => $params['order_id'],
                    'shipment_id' => $shipment_id,
                    'dispatch_number' => $result['number'],
                    'data' => date("Y-m-d", $shipment['shipment_timestamp']),
                    'data_xml' => $xml,
                    'timestamp' => TIME,
                    'status' => 'S',
                    'tariff' => $sdek_info['Order']['TariffTypeCode'],
                    'file_sdek' => $shipment_id . '/' . $params['order_id'] . '.pdf',
                    'notes' => $sdek_info['Order']['Comment'],
                );

                if (!empty($result['number'])) {
                    db_query('UPDATE ?:shipments SET tracking_number = ?s WHERE shipment_id = ?i', $result['number'], $shipment_id);
                }

                if (!empty($sdek_delivery[$sdek_info['Order']['TariffTypeCode']]['terminals']) && ($sdek_delivery[$sdek_info['Order']['TariffTypeCode']]['terminals'] == 'N')) {
                    $register_data['address'] = $sdek_info['Address']['Street'];
                } else {
                    $register_data['address_pvz'] = $sdek_info['Address']['PvzCode'];
                }

                $register_id = db_query('INSERT INTO ?:rus_sdek_register ?e', $register_data);

                foreach ($sdek_products as $sdek_product) {
                    $sdek_product['register_id'] = $register_id;
                    db_query('INSERT INTO ?:rus_sdek_products ?e', $sdek_product);
                }

                if (!empty($call_recipient)) {
                    db_query('INSERT INTO ?:rus_sdek_call_recipient ?e', $call_recipient);
                }

                if (!empty($call_courier)) {
                    db_query('INSERT INTO ?:rus_sdek_call_courier ?e', $call_courier);
                }

                fn_sdek_get_ticket_order($data_auth, $params['order_id'], $shipment_id);
            }

            $date_status = RusSdek::orderStatusXml($data_auth, $params['order_id'], $shipment_id);

            RusSdek::addStatusOrders($date_status);
        }

    } elseif ($mode == 'sdek_order_delete') {
        foreach ($params['add_sdek_info'] as $shipment_id => $sdek_info) {
            list($_shipments) = fn_get_shipments_info(array('order_id' => $params['order_id'], 'advanced_info' => true, 'shipment_id' => $shipment_id));
            $shipment = reset($_shipments);
            $params_shipping = array(
                'shipping_id' => $shipment['shipping_id'],
                'Date' => date("Y-m-d", $shipment['shipment_timestamp']),
            );

            $data_auth = RusSdek::dataAuth($params_shipping);
            if (empty($data_auth)) {
                continue;
            }

            $data_auth['Number'] = $params['order_id'] . '_' . $shipment_id;
            $data_auth['OrderCount'] = "1";
            $xml = '            ' . RusSdek::arraySimpleXml('DeleteRequest', $data_auth, 'open');
            $order_sdek = array (
                'Number' => $params['order_id'] . '_' . $shipment_id
            );
            $xml .= '            ' . RusSdek::arraySimpleXml('Order', $order_sdek);
            $xml .= '            ' . '</DeleteRequest>';

            $response = RusSdek::xmlRequest('http://gw.edostavka.ru:11443/delete_orders.php', $xml, $data_auth);
            $result = RusSdek::resultXml($response);

            if (empty($result['error'])) {
                $param_search = db_quote(' WHERE order_id = ?i AND shipment_id = ?i ', $params['order_id'], $shipment_id);
                db_query('DELETE FROM ?:rus_sdek_products ?p ', $param_search);
                db_query('DELETE FROM ?:rus_sdek_register ?p ', $param_search);
                db_query('DELETE FROM ?:rus_sdek_status ?p ', $param_search);
                db_query('DELETE FROM ?:rus_sdek_call_recipient ?p ', $param_search);
                db_query('DELETE FROM ?:rus_sdek_call_courier ?p ', $param_search);
            }
        }

    } elseif ($mode == 'sdek_order_status') {
        foreach ($params['add_sdek_info'] as $shipment_id => $sdek_info) {
            list($_shipments) = fn_get_shipments_info(array('order_id' => $params['order_id'], 'advanced_info' => true, 'shipment_id' => $shipment_id));
            $shipment = reset($_shipments);
            $params_shipping = array(
                'shipping_id' => $shipment['shipping_id'],
                'Date' => date("Y-m-d", $shipment['shipment_timestamp']),
            );
            $data_auth = RusSdek::dataAuth($params_shipping);
            if (empty($data_auth)) {
                continue;
            }
            $date_status = RusSdek::orderStatusXml($data_auth, $params['order_id'], $shipment_id);
            RusSdek::addStatusOrders($date_status);
        }

    } elseif ($mode == 'sdek_call_recipient') {
        foreach ($params['add_sdek_info'] as $shipment_id => $sdek_info) {
            list($_shipments) = fn_get_shipments_info(array('order_id' => $params['order_id'], 'advanced_info' => true, 'shipment_id' => $shipment_id));
            $shipment = reset($_shipments);
            $params_shipping = array(
                'shipping_id' => $shipment['shipping_id'],
                'Date' => date("Y-m-d", $shipment['shipment_timestamp']),
            );
            $data_auth = RusSdek::dataAuth($params_shipping);
            if (empty($data_auth)) {
                continue;
            }

            $count_schedule = db_get_field("SELECT count(*) FROM ?:rus_sdek_call_recipient ");
            $sdek_info['Schedule']['ID'] = $count_schedule + 1;
            $sdek_info['Schedule']['Date'] = date("Y-m-d", strtotime($sdek_info['Schedule']['Date']));
            $call_recipient = array(
                'order_id' => $params['order_id'],
                'shipment_id' => $shipment_id,
                'timestamp' => TIME,
                'shipment_date' => $sdek_info['Schedule']['Date'],
                'timebag' => $sdek_info['Schedule']['TimeBeg'],
                'timeend' => $sdek_info['Schedule']['TimeEnd'],
                'recipient_name' => $sdek_info['Schedule']['RecipientName'],
                'phone' => $sdek_info['Schedule']['Phone'],
                'call_comment' => $sdek_info['Schedule']['Comment'],
            );

            $data_auth['OrderCount'] = "1";
            $xml = RusSdek::arraySimpleXml('ScheduleRequest', $data_auth, 'open');

            $order_for_sdek['Number'] = $params['order_id'] . '_' . $shipment_id;
            $order_for_sdek['Date'] = date("Y-m-d", $shipment['shipment_timestamp']);
            $xml .= RusSdek::arraySimpleXml('Order', $order_for_sdek, 'open');

            $sdek_info['Schedule'] = array_diff($sdek_info['Schedule'], array('', '0.00'));
            $xml .= RusSdek::arraySimpleXml('Attempt', $sdek_info['Schedule']);

            if (!empty($sdek_info['Address'])) {
                $xml .= RusSdek::arraySimpleXml('Address', $sdek_info['Address']);
            }

            $xml .= '</Order>';
            $xml .= '</ScheduleRequest>';

            $response = RusSdek::xmlRequest('http://gw.edostavka.ru:11443/new_schedule.php', $xml, $data_auth);
            
            $result = RusSdek::resultXml($response);

            if (empty($result['error'])) {

                if (!empty($sdek_delivery[$sdek_info['Order']['TariffTypeCode']]['terminals']) && ($sdek_delivery[$sdek_info['Order']['TariffTypeCode']]['terminals'] == 'Y')) {
                    $call_recipient['address'] = $sdek_info['Address']['Street'];
                } else {
                    $call_recipient['pvz_code'] = $sdek_info['Address']['PvzCode'];
                }

                $call_recipient = array_diff($call_recipient, array('', '0.00'));

                $call_id = db_get_field('SELECT call_id FROM ?:rus_sdek_call_recipient WHERE order_id = ?i AND shipment_id =?i ', $params['order_id'], $shipment_id);
                if (!empty($call_id)) {
                    db_query('UPDATE ?:rus_sdek_call_recipient SET ?u WHERE order_id = ?i AND shipment_id =?i ', $call_recipient, $params['order_id'], $shipment_id);
                } else {
                    db_query('INSERT INTO ?:rus_sdek_call_recipient ?e', $call_recipient);
                }

                fn_sdek_get_ticket_order($data_auth, $params['order_id'], $shipment_id);
            }

            $date_status = RusSdek::orderStatusXml($data_auth, $params['order_id'], $shipment_id);

            RusSdek::addStatusOrders($date_status);
        }
    } elseif ($mode == 'sdek_call_courier'){
        foreach ($params['add_sdek_info'] as $shipment_id => $sdek_info) {
            if (!empty($sdek_info['CallCourier']['Date']) && !empty($sdek_info['CallCourier']['TimeBeg']) && !empty($sdek_info['CallCourier']['TimeEnd'])) {
                list($_shipments) = fn_get_shipments_info(array('order_id' => $params['order_id'], 'advanced_info' => true, 'shipment_id' => $shipment_id));
                $shipment = reset($_shipments);
                $params_shipping = array(
                    'shipping_id' => $shipment['shipping_id'],
                    'Date' => date("Y-m-d", $shipment['shipment_timestamp']),
                );
                $data_auth = RusSdek::dataAuth($params_shipping);
                if (empty($data_auth)) {
                    continue;
                }

                $total_weight = db_get_field("SELECT SUM(weight) FROM ?:rus_sdek_products WHERE order_id = ?i AND shipment_id = ?i", $params['order_id'], $shipment_id);
                if (!empty($total_weight) && $total_weight != 0) {
                    $total_weight = $total_weight;
                } else {
                    $total_weight = SDEK_DEFAULT_WEIGHT;
                }

                $sdek_info['CallCourier'] = array_diff($sdek_info['CallCourier'], array('', '0.00'));
                unset($data_auth['Number']);
                $data_auth['CallCount'] = "1";
                $xml = RusSdek::arraySimpleXml('CallCourier', $data_auth, 'open');

                $sdek_info['CallCourier']['Date'] = date("Y-m-d", strtotime($sdek_info['CallCourier']['Date']));
                $sdek_info['CallCourier']['SendCityCode'] = $sdek_info['Order']['SendCityCode'];
                $sdek_info['CallCourier']['SendPhone'] = Registry::get('runtime.company_data.phone');
                $sdek_info['CallCourier']['SenderName'] = Registry::get('runtime.company_data.company');
                $sdek_info['CallCourier']['Weight'] = $total_weight * Registry::get('settings.General.weight_symbol_grams');
                $xml .= RusSdek::arraySimpleXml('Call', $sdek_info['CallCourier'], 'open');

                $address_send = array(
                    'Street' => Registry::get('runtime.company_data.address'),
                    'House' => '-',
                    'Flat' => '-',
                );
                $xml .= RusSdek::arraySimpleXml('Address', $address_send);

                $xml .= '</Call>';
                $xml .= '</CallCourier>';

                $response = RusSdek::xmlRequest('http://gw.edostavka.ru:11443/call_courier.php', $xml, $data_auth);
                
                $result = RusSdek::resultXml($response);

                if (empty($result['error'])) {
                    $call_courier = array(
                        'order_id' => $params['order_id'],
                        'shipment_id' => $shipment_id,
                        'timestamp' => TIME,
                        'call_courier_date' => $sdek_info['CallCourier']['Date'],
                        'timebag' => $sdek_info['CallCourier']['TimeBeg'],
                        'timeend' => $sdek_info['CallCourier']['TimeEnd'],
                        'weight' => $total_weight,
                    );

                    $call_courier['lunch_timebag'] = !empty($sdek_info['CallCourier']['LunchBeg']) ? $sdek_info['CallCourier']['LunchBeg'] : '';
                    $call_courier['lunch_timeend'] = !empty($sdek_info['CallCourier']['LunchEnd']) ? $sdek_info['CallCourier']['LunchEnd'] : '';
                    $call_courier['comment_courier'] = !empty($sdek_info['CallCourier']['Comment']) ? $sdek_info['CallCourier']['Comment'] : '';

                    db_query('INSERT INTO ?:rus_sdek_call_courier ?e', $call_courier);
                }

                $date_status = RusSdek::orderStatusXml($data_auth, $params['order_id'], $shipment_id);

                RusSdek::addStatusOrders($date_status);
            }
        }
    }

    if ($mode == 'update_details') {
        $order_info = fn_get_order_info($params['order_id'], false, true, true);
        $force_notification = fn_get_notification_rules($params);

        if (!empty($force_notification['C']) && !empty($params['update_shipping'])) {
            foreach ($params['update_shipping'] as $shipping) {
                foreach ($shipping as $shipment_id => $shipment_data) {
                    if ($shipment_data['carrier'] == 'sdek') {
                        $d_shipment = db_get_row("SELECT * FROM ?:shipments WHERE shipment_id = ?i ", $shipment_id);
                        $products = db_get_hash_array("SELECT item_id, amount FROM ?:shipment_items WHERE order_id = ?i AND shipment_id = ?i ", 'item_id', $params['order_id'], $shipment_id);

                        foreach ($products as $item_id => $product) {
                            $shipment_data['products'][$item_id] = $product['amount'];
                        }

                        $shipment = array(
                            'shipment_id' => $shipment_id,
                            'timestamp' => $d_shipment['timestamp'],
                            'shipping' => db_get_field('SELECT shipping FROM ?:shipping_descriptions WHERE shipping_id = ?i AND lang_code = ?s', $d_shipment['shipping_id'], $order_info['lang_code']),
                            'tracking_number' => $shipment_data['tracking_number'],
                            'carrier' => $shipment_data['carrier'],
                            'comments' => $d_shipment['comments'],
                            'items' => $shipment_data['products'],
                        );

                        Mailer::sendMail(array(
                            'to' => $order_info['email'],
                            'from' => 'company_orders_department',
                            'data' => array(
                                'shipment' => $shipment,
                                'order_info' => $order_info,
                            ),
                            'tpl' => 'shipments/shipment_products.tpl',
                            'company_id' => $order_info['company_id'],
                        ), 'C', $order_info['lang_code']);
                    }
                }
            }
        }
    }

    $url = fn_url("orders.details&order_id=" . $params['order_id'], 'A', 'current');
    if (defined('AJAX_REQUEST') && !empty($url)) {
        Registry::get('ajax')->assign('force_redirection', $url);
        exit;
    }

    return array(CONTROLLER_STATUS_OK, $url);
}

if ($mode == 'details') {
    $params = $_REQUEST;
    $order_info = Tygh::$app['view']->getTemplateVars('order_info');

    $sdek_info = $sdek_pvz = false; 
    if (!empty($order_info['shipping'])) {
        foreach ($order_info['shipping'] as $shipping) {
            if (($shipping['module'] == 'sdek') && !empty($shipping['office_id'])) {
                $sdek_pvz = $shipping['office_id'];                
            }
        }        
    }

    list($all_shipments) = fn_get_shipments_info(array('order_id' => $params['order_id'], 'advanced_info' => true));

    if (!empty($all_shipments)) {

        $sdek_shipments = $data_shipments = array();

        foreach ($all_shipments as $key => $_shipment) {
            if ($_shipment['carrier'] == 'sdek') {
                $sdek_shipments[] = $_shipment;
            }
        }

        if (!empty($sdek_shipments)) {

            $offices = array();
            $rec_city = (!empty($order_info['s_city'])) ? $order_info['s_city'] : $order_info['b_city'];
            $rec_city_code = db_get_field("SELECT city_code FROM ?:rus_city_sdek_descriptions as a LEFT JOIN ?:rus_cities_sdek as b ON a.city_id=b.city_id WHERE city = ?s", $rec_city);

            $lastname = "";
            if (!empty($order_info['lastname'])) {
                $lastname = $order_info['lastname'];

            } elseif (!empty($order_info['s_lastname'])) {
                $lastname = $order_info['s_lastname'];

            } elseif (!empty($order_info['b_lastname'])) {
                $lastname = $order_info['b_lastname'];
            }
            $firstname = "";
            if (!empty($order_info['firstname'])) {
                $firstname = $order_info['firstname'];

            } elseif (!empty($order_info['s_firstname'])) {
                $firstname = $order_info['s_firstname'];

            } elseif (!empty($order_info['b_firstname'])) {
                $firstname = $order_info['b_firstname'];
            }

            $fio = $lastname . ' ' . $firstname;

            $phone = "";
            if (!empty($order_info['phone'])) {
                $phone = $order_info['phone'];

            } elseif (!empty($order_info['s_phone'])) {
                $phone = $order_info['s_phone'];

            } elseif (!empty($order_info['b_phone'])) {
                $phone = $order_info['b_phone'];
            }

            foreach ($sdek_shipments as $key => $shipment) {

                $data_sdek = db_get_row("SELECT register_id, order_id, timestamp, status, tariff as tariff_id, address_pvz, address, file_sdek, notes FROM ?:rus_sdek_register WHERE order_id = ?i and shipment_id = ?i", $shipment['order_id'], $shipment['shipment_id']);

                $data_shipping = fn_get_shipping_info($shipment['shipping_id'], DESCR_SL);

                $module = db_get_field("SELECT module FROM ?:shipping_services WHERE service_id = ?i", $data_shipping['service_id']);

                if (!empty($data_shipping['service_params']) && ($module == 'sdek')) {

                    if (!empty($data_sdek)) {
                        $data_shipments[$shipment['shipment_id']] = $data_sdek;

                        $data_status = db_get_array("SELECT * FROM ?:rus_sdek_status WHERE order_id = ?i AND shipment_id = ?i ", $params['order_id'], $shipment['shipment_id']);
                        if (!empty($data_status)) {
                            foreach ($data_status as $k => $status) {
                                $status['city'] = db_get_field("SELECT city FROM ?:rus_city_sdek_descriptions as a LEFT JOIN ?:rus_cities_sdek as b ON a.city_id=b.city_id WHERE b.city_code = ?s", $status['city_code']);
                                $status['date'] = date("d-m-Y  H:i:s", $status['timestamp']);
                                $data_shipments[$shipment['shipment_id']]['sdek_status'][$status['id']] = array(
                                    'id' => $status['id'],
                                    'date' => $status['date'],
                                    'status' => $status['status'],
                                    'city' => $status['city'],
                                );
                            }
                        }

                    } else {

                        $cost = fn_sdek_calculate_cost_by_shipment($order_info, $data_shipping, $shipment, $rec_city_code);

                        $data_shipments[$shipment['shipment_id']] = array(
                            'order_id' => $shipment['order_id'],
                            'comments' => $shipment['comments'],
                            'delivery_cost' => $cost,
                            'tariff_id' => $data_shipping['service_params']['tariffid'],
                            'address_pvz' => $sdek_pvz,
                        );
                        $data_shipments[$shipment['shipment_id']]['address'] = (!empty($order_info['s_address'])) ? $order_info['s_address'] : $order_info['b_address'];
                    }

                    $data_shipments[$shipment['shipment_id']]['send_city_code'] = $data_shipping['service_params']['from_city_id'];
                    $data_shipments[$shipment['shipment_id']]['shipping'] = $shipment['shipping'];

                    $data_call_recipients = db_get_row("SELECT * FROM ?:rus_sdek_call_recipient WHERE order_id = ?i and shipment_id = ?i", $shipment['order_id'], $shipment['shipment_id']);

                    if (!empty($data_call_recipients)) {
                        $data_shipments[$shipment['shipment_id']]['new_schedules'] = $data_call_recipients;
                        $data_shipments[$shipment['shipment_id']]['new_schedules']['date'] = strtotime($data_call_recipients['shipment_date']);
                        $data_shipments[$shipment['shipment_id']]['address_pvz'] = $data_call_recipients['pvz_code'];
                        $data_shipments[$shipment['shipment_id']]['address'] = $data_call_recipients['address'];
                    } else {
                        $data_shipments[$shipment['shipment_id']]['new_schedules'] = array(
                            'recipient_name' => $fio,
                            'phone' => $phone,
                            'date' => TIME,
                            'recipient_cost' => '0.00',
                            'timebag' => '',
                            'timeend' => '',
                        );
                    }

                    $data_call_couriers = db_get_array("SELECT * FROM ?:rus_sdek_call_courier WHERE order_id = ?i and shipment_id = ?i", $shipment['order_id'], $shipment['shipment_id']);

                    $data_shipments[$shipment['shipment_id']]['call_couriers'][] = array(
                        'date' => TIME,
                    );

                    if (!empty($data_call_couriers)) {
                        $data_shipments[$shipment['shipment_id']]['call_couriers'] = array_merge($data_shipments[$shipment['shipment_id']]['call_couriers'], $data_call_couriers);
                    }

                    if (!empty($sdek_delivery[$data_shipping['service_params']['tariffid']]['terminals']) && ($sdek_delivery[$data_shipping['service_params']['tariffid']]['terminals'] == 'Y')) {
                        
                        $type_terminals = 'PVZ';
                        if (!empty($sdek_delivery[$data_shipping['service_params']['tariffid']]['postomat'])) {
                            $type_terminals = 'POSTOMAT';
                        }

                        if (!empty($rec_city_code)) {
                            $offices = RusSdek::pvzOffices(array('cityid' => $rec_city_code, 'type' => $type_terminals));
                        }

                        $data_shipments[$shipment['shipment_id']]['offices'] = $offices;
                    }
                }
            }

            if (!empty($data_shipments)) {

                Registry::set('navigation.tabs.sdek_orders', array (
                    'title' => __('shippings.sdek.sdek_orders'),
                    'js' => true
                ));

                Tygh::$app['view']->assign('data_shipments', $data_shipments);
                Tygh::$app['view']->assign('sdek_pvz', $sdek_pvz);
                Tygh::$app['view']->assign('rec_city_code', $rec_city_code);
                Tygh::$app['view']->assign('order_id', $params['order_id']);
            }
        }
    }

} elseif ($mode == 'sdek_get_ticket') {

    $params = $_REQUEST;

    $file = $params['order_id'] . '.pdf';

    $path = fn_get_files_dir_path() . 'sdek/' . $params['shipment_id'] . '/';

    fn_get_file($path . $file);

    if (defined('AJAX_REQUEST') && !empty($url)) {
        Registry::get('ajax')->assign('force_redirection', $url);
        exit;
    }

    return array(CONTROLLER_STATUS_OK);
}

function fn_sdek_get_ticket_order($data_auth, $order_id, $chek_id)
{
    unset($data_auth['Number']);
    $xml = '            ' . RusSdek::arraySimpleXml('OrdersPrint', $data_auth, 'open');
    $order_sdek = array (
        'Number' => $order_id . '_' . $chek_id,
        'Date' => $data_auth['Date']
    );
    $xml .= '            ' . RusSdek::arraySimpleXml('Order', $order_sdek);
    $xml .= '            ' . '</OrdersPrint>';

    $response = RusSdek::xmlRequest('http://gw.edostavka.ru:11443/orders_print.php', $xml, $data_auth);

    $download_file_dir = fn_get_files_dir_path() . '/sdek' . '/' . $chek_id . '/';

    fn_rm($download_file_dir);
    fn_mkdir($download_file_dir);

    $name = $order_id . '.pdf';

    $download_file_path = $download_file_dir . $name;
    if (!fn_is_empty($response)) {
        fn_put_contents($download_file_path, $response);
    }
}