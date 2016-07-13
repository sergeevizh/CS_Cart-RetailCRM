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
use Tygh\Commerceml\Logs;
use Tygh\Commerceml\RusEximCommerceml;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

$log = new Logs();
list($cml, $s_commerceml) = RusEximCommerceml::getParamsCommerceml();

if ($s_commerceml['status'] != 'A') {
    RusEximCommerceml::showMessageError("Addon Commerceml disabled");
    exit;
}

if (!empty($_SERVER['PHP_AUTH_USER'])) {
    $_data['user_login'] = $_SERVER['PHP_AUTH_USER'];
} else {
    RusEximCommerceml::showMessageError("Enter login and password user");
    exit;
}

list($status, $user_data, $user_login, $password, $salt) = fn_auth_routines($_data, array());

if ($user_login != $_SERVER['PHP_AUTH_USER'] || empty($user_data['password']) || $user_data['password'] != fn_generate_salted_password($_SERVER['PHP_AUTH_PW'], $salt)) {
    RusEximCommerceml::showMessageError("Error in login or password user");
    exit;
}

if (!RusEximCommerceml::checkAllwedAccess($user_data)) {
    RusEximCommerceml::showMessageError("Privileges for user not setted");
    exit;
}

RusEximCommerceml::getCompanyStore($user_data);

$type = $mode = '';
$service_exchange = '';
if (isset($_REQUEST['type'])) {
    $type = $_REQUEST['type'];
}

if (isset($_REQUEST['mode'])) {
    $mode = $_REQUEST['mode'];
}

if (isset($_REQUEST['service_exchange'])) {
    $service_exchange = $_REQUEST['service_exchange'];
}

$filename = (!empty($_REQUEST['filename'])) ? fn_basename($_REQUEST['filename']) : '';
$lang_code = (!empty($s_commerceml['exim_1c_lang'])) ? $s_commerceml['exim_1c_lang'] : CART_LANGUAGE;

if ($type == 'catalog') {
    if ($mode == 'checkauth') {
        RusEximCommerceml::exportDataCheckauth($service_exchange);

    } elseif ($mode == 'init') {
        RusEximCommerceml::exportDataInit();

    } elseif ($mode == 'file') {
        if (RusEximCommerceml::createImportFile($filename) === false) {
            fn_echo("failure");
            exit;
        }
        fn_echo("success\n");

    } elseif ($mode == 'import') {
        $fileinfo = pathinfo($filename);

        $xml = RusEximCommerceml::getFileCommerceml($filename);
        if ($xml === false) {
            fn_echo("failure");
            exit;
        }

        $manual = !empty($_REQUEST['manual']);

        if (strpos($fileinfo['filename'], 'import') !== false) {
            if ($s_commerceml['exim_1c_import_products'] != 'not_import') {
                RusEximCommerceml::importDataProductFile($xml, $user_data, $service_exchange, $lang_code, $manual);
            } else {
                fn_echo("success\n");
            }
        }

        if (strpos($fileinfo['filename'], 'offers') !== false) {
            if ($s_commerceml['exim_1c_only_import_offers'] == 'Y') {
                RusEximCommerceml::importDataOffersFile($xml, $service_exchange, $lang_code, $manual);
            } else {
                fn_echo("success\n");
            }
        }
    }

} elseif (($type == 'sale') && ($user_data['user_type'] != 'V') && ($s_commerceml['exim_1c_check_prices'] != 'Y')) {
    if ($mode == 'checkauth') {
        RusEximCommerceml::exportDataCheckauth($service_exchange);

    } elseif ($mode == 'init') {
        RusEximCommerceml::exportDataInit();

    } elseif ($mode == 'file') {
        if (RusEximCommerceml::createImportFile($filename) === false) {
            fn_echo("failure");
            exit;
        }

        if (($s_commerceml['exim_1c_import_statuses'] == 'Y') && (strpos($filename, 'orders') == 0)) {
            $xml = RusEximCommerceml::getFileCommerceml($filename);
            if ($xml === false) {
                fn_echo("failure");
                exit;
            }

            RusEximCommerceml::importFileOrders($xml, $lang_code);
        }

        fn_echo("success\n");

    } elseif ($mode == 'query') {
        if ($s_commerceml['exim_1c_all_product_order'] == 'Y') {
            RusEximCommerceml::exportAllProductsToOrders($user_data, $lang_code);
        } else {
            RusEximCommerceml::exportDataOrders($lang_code);
        }

    } elseif ($mode == 'success') {
        fn_echo("success");
    }
}

exit;
