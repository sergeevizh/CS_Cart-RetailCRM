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

if (defined('PAYMENT_NOTIFICATION')) {

    if (isset($_REQUEST['ordernumber'])) {
        list($order_id) = explode('_', $_REQUEST['ordernumber']);

    } elseif (isset($_REQUEST['orderNumber'])) {
        list($order_id) = explode('_', $_REQUEST['orderNumber']);

    } elseif (isset($_REQUEST['merchant_order_id'])) {
        list($order_id) = explode('_', $_REQUEST['merchant_order_id']);

    } else {
        $order_id = 0;
    }

    $payment_id = db_get_field("SELECT payment_id FROM ?:orders WHERE order_id = ?i", $order_id);
    $processor_data = fn_get_processor_data($payment_id);
    $shop_id = $processor_data['processor_params']['shop_id'];

    if (!empty($processor_data['processor_params']['logging']) && $processor_data['processor_params']['logging'] == 'Y') {
        fn_yandex_money_log_write($mode, 'ym_request.log');
        fn_yandex_money_log_write($_REQUEST, 'ym_request.log');
    }

    if ($mode == 'ok') {

        if (fn_check_payment_script('yandex_money.php', $order_id)) {

            $times = 0;
            while ($times <= YM_MAX_AWAITING_TIME) {

                $_order_id = db_get_field("SELECT order_id FROM ?:order_data WHERE order_id = ?i AND type = 'S'", $order_id);
                if (empty($_order_id)) {
                    break;
                }

                sleep(1);
                $times++;
            }

            $order_status = db_get_field("SELECT status FROM ?:orders WHERE order_id = ?i", $order_id);

            if ($order_status == STATUS_INCOMPLETED_ORDER) {
                fn_change_order_status($order_id, 'O');
            }

            fn_order_placement_routines('route', $order_id, false);
        }

    } elseif ($mode == 'error') {

        $pp_response['order_status'] = 'N';
        $pp_response["reason_text"] = __('text_transaction_cancelled');

        if (fn_check_payment_script('yandex_money.php', $order_id)) {
            fn_finish_payment($order_id, $pp_response, false);
        }

        fn_order_placement_routines('route', $order_id);

    } elseif ($mode == 'check_order') {

        $date_time = date('c');
        $code = 0;
        $invoiceId = $_REQUEST['invoiceId'];

        header("Content-Type: text/xml; charset=utf-8");

        $dom = new DOMDocument('1.0', 'utf-8');
        $item = $dom->createElement('checkOrderResponse');
        $item->setAttribute('performedDatetime', $date_time);
        $item->setAttribute('shopId', $shop_id);
        $item->setAttribute('invoiceId', $invoiceId);

        $order_total = db_get_field("SELECT total FROM ?:orders WHERE order_id = ?i", $order_id);

        if ($_REQUEST['orderSumAmount'] != $order_total) {
            $code = 2;
            $item->setAttribute('orderSumAmount', $order_total);

        } else {

            $hash = $_REQUEST['action'] . ';' . $_REQUEST['orderSumAmount'] . ';' . $_REQUEST['orderSumCurrencyPaycash'] . ';' . $_REQUEST['orderSumBankPaycash'] . ';' . $_REQUEST['shopId'] . ';' . $_REQUEST['invoiceId'] . ';' . $_REQUEST['customerNumber'] . ';' . $processor_data['processor_params']['md5_shoppassword'];
            $hash = md5($hash);
            $hash = strtoupper($hash);

            if ($_REQUEST['md5'] != $hash) {
                $code = 1;
            }
        }

        $item->setAttribute('code', $code);
        $dom->appendChild($item);
        echo($dom->saveXML());

        if (!empty($processor_data['processor_params']['logging']) && $processor_data['processor_params']['logging'] == 'Y') {
            fn_yandex_money_log_write($dom->saveXML(), 'ym_check_order.log');
        }

        exit;

    } elseif ($mode == 'payment_aviso') {

        $date_time = date('c');
        $code = 0;
        $invoiceId = $_REQUEST['invoiceId'];

        $hash = $_REQUEST['action'].';'.$_REQUEST['orderSumAmount'].';'.$_REQUEST['orderSumCurrencyPaycash'].';'.$_REQUEST['orderSumBankPaycash'].';'.$_REQUEST['shopId'].';'.$_REQUEST['invoiceId'].';'.$_REQUEST['customerNumber'].';'.$processor_data['processor_params']['md5_shoppassword'];
        $hash = md5($hash);
        $hash = strtoupper($hash);

        if ($_REQUEST['md5'] == $hash) {

            $order_status = 'P';
            $pp_response = array(
                'order_status' => $order_status,
                'yandex_invoice_id' => $invoiceId,
            );

            if (isset($_REQUEST['merchant_order_id'])) {
                $pp_response['yandex_merchant_order_id'] = $_REQUEST['merchant_order_id'];
            }

            if (
                !empty($processor_data['processor_params']['postponed_payments_enabled'])
                && $processor_data['processor_params']['postponed_payments_enabled'] == 'Y'
            ) {
                $pp_response['order_status'] = $processor_data['processor_params']['unconfirmed_order_status'];
                $pp_response['yandex_postponed_payment'] = true;
            }

            if (fn_check_payment_script('yandex_money.php', $order_id)) {
                fn_finish_payment($order_id, $pp_response);
            }

        } else {
            $code = 1;
            $pp_response['order_status'] = 'N';
            $pp_response["reason_text"] = __('error');

            if (fn_check_payment_script('yandex_money.php', $order_id)) {
                fn_finish_payment($order_id, $pp_response, false);
            }
        }

        header("Content-Type: text/xml; charset=utf-8");

        $dom = new DOMDocument('1.0', 'utf-8');
        $item = $dom->createElement('paymentAvisoResponse');
        $item->setAttribute('performedDatetime', $date_time);
        $item->setAttribute('code', $code);
        $item->setAttribute('invoiceId', $invoiceId);
        $item->setAttribute('shopId', $shop_id);

        $dom->appendChild($item);
        echo($dom->saveXML());

        if (!empty($processor_data['processor_params']['logging']) && $processor_data['processor_params']['logging'] == 'Y') {
            fn_yandex_money_log_write($dom->saveXML(), 'ym_payment_aviso.log');
        }

        exit;
    }

} else {
    if (!defined('BOOTSTRAP')) { die('Access denied'); }

    if ($processor_data['processor_params']['mode'] == 'test') {
        $post_address = "https://demomoney.yandex.ru/eshop.xml";
    } else {
        $post_address = "https://money.yandex.ru/eshop.xml";
    }

    $payment_info['yandex_payment_type'] = mb_strtoupper($payment_info['yandex_payment_type']);
    if (empty($payment_info['yandex_payment_type'])) {
        $payment_type = 'PC';
    } else {
        $payment_type = $payment_info['yandex_payment_type'];
    }

    $phone = '';
    if (!empty($order_info['phone'])) {
        $phone = $order_info['phone'];

    } elseif (!empty($order_info['b_phone'])) {
        $phone = $order_info['b_phone'];

    } elseif (!empty($order_info['s_phone'])) {
        $phone = $order_info['s_phone'];
    }

    $customer_phone = str_replace('+', '', $phone);

    $orderNumber = $order_info['order_id'] . '_' . substr(md5($order_info['order_id'] . TIME), 0, 3);

    $post_data = array(
        'shopId' => $processor_data['processor_params']['shop_id'],
        'Sum' => fn_format_price_by_currency($order_info['total'], CART_PRIMARY_CURRENCY, $processor_data['processor_params']['currency']),
        'scid' => $processor_data['processor_params']['scid'],
        'customerNumber' => $order_info['email'],
        'orderNumber' => $orderNumber,
        'shopSuccessURL' => fn_url("payment_notification.ok?payment=yandex_money&ordernumber=$orderNumber", AREA, 'https'),
        'shopFailURL' => fn_url("payment_notification.error?payment=yandex_money&ordernumber=$orderNumber", AREA, 'https'),
        'cps_email' => $order_info['email'],
        'cps_phone' => $customer_phone,
        'paymentAvisoURL' => fn_url("payment_notification.payment_aviso?payment=yandex_money", AREA, 'https'),
        'checkURL' => fn_url("payment_notification.check_order?payment=yandex_money", AREA, 'https'),
        'paymentType' => $payment_type,
        'cms_name' => 'cscart'
    );

    if (!empty($processor_data['processor_params']['logging']) && $processor_data['processor_params']['logging'] == 'Y') {
        fn_yandex_money_log_write($post_data, 'ym_post_data.log');
    }

    fn_create_payment_form($post_address, $post_data, 'Yandex.Money', false);
}

exit;
