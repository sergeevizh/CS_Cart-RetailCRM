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

// DO NOT MODIFY THIS LINE. FILE WILL BE OBFUSCATED!

use Tygh\Registry;
use Tygh\Helpdesk;

function fn_ebay_check_license($silent = false, $skip_messages = false)
{
    // addons.ebay.ebay_license_number
    $license_number = Registry::get(str_rot13('nqqbaf.ronl.ronl_yvprafr_ahzore'));
    Tygh::$app['session']['eauth'] = time();

    if (empty($license_number)) {
        if (!$silent) {
            // ebay_empty_license_number
            fn_set_notification('E', __('error'), __(str_rot13('ronl_rzcgl_yvprafr_ahzore')));
        }
        return false;
    } else {
        // Some HD checking code
        $data = Helpdesk::getLicenseInformation($license_number, array('edition' => 'EBAY'));
        list($license_status, , $messages, $params) = Helpdesk::parseLicenseInformation($data, array(), false);

        if (!$skip_messages) {
            foreach ($messages as $key => $message) {
                if ($message['type'] == 'E') {
                    fn_set_notification($message['type'], $message['title'], $message['text']);
                }
            }
        }

        if (isset($params['trial_left_time'])) {
            fn_set_storage_data('ebay_trial_expiry_time', TIME + $params['trial_left_time']);
        }

        if (in_array($license_status, array('ACTIVE', 'TRIAL'))) {
            return 'A';
        } elseif ($license_status == '') {
            // Timeout
            fn_set_notification('E', __('error'), __('unable_to_check_license'));
            return 'T';
        } else {
            return 'I';
        }
    }
}

function fn_ebay_extend_addons()
{
    $key = str_rot13('rnhgu');

    if (empty(Tygh::$app['session'][$key]) || Tygh::$app['session'][$key] < strtotime('-1 day')) {
        Tygh::$app['session'][$key] = time();
        fn_ebay_check_addon();
    }

    return false;
}

/**
 * Check addon
 */
function fn_ebay_check_addon()
{
    $result = call_user_func_array(str_rot13('sa_ronl_purpx_yvprafr'), array(true, true));
    $key = call_user_func(str_rot13('sa_trg_ronl_genvy_yvprafr_xrl'));
    $current = Registry::get(str_rot13('nqqbaf.ronl.ronl_yvprafr_ahzore'));

    if ($result != 'A' && $result != 'T') {
        if ($key === $current) {
            $message = __(str_rot13('ronl_nqqba_gevny_yvprafr_rkcverq'), array(
                '[ebay_license_url]' => fn_ebay_get_license_url()
            ));
        } else {
            $message = __(str_rot13('ronl_nqqba_yvprafr_vainyvq'));
        }

        fn_set_notification('W', __('warning'), $message, 'S');
        call_user_func_array(str_rot13('sa_qvfnoyr_nqqba'), array('ebay', str_rot13('rnhgu'), false));
    }
}

/**
 * Return trail license key for installation
 * @return string
 */
function fn_get_ebay_trail_license_key()
{
    return 'EBAY_TRIAL_' . md5(str_replace(fn_get_index_script('A'), '',fn_url('', 'A', 'http')));
}
