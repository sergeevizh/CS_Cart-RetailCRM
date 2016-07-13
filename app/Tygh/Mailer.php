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

namespace Tygh;
use Tygh\Tools\Url;

class Mailer extends \PHPMailer
{
    private static $_mailer;
    private static $default_settings;

    public static function sendMail($params, $area = AREA, $lang_code = CART_LANGUAGE)
    {
        if (empty($params['to']) || empty($params['from']) || (empty($params['tpl']) && empty($params['body']))) {
            return false;
        }

        fn_disable_live_editor_mode();

        $to = array();
        $reply_to = array();
        $cc = array();

        $mailer = self::instance(!empty($params['mailer_settings']) ? $params['mailer_settings'] : array());

        fn_set_hook('send_mail_pre', $mailer, $params, $area, $lang_code);

        $mailer->ClearReplyTos();
        $mailer->ClearCCs();
        $mailer->ClearAttachments();
        $mailer->Sender = '';

        $params['company_id'] = !empty($params['company_id']) ? $params['company_id'] : 0;
        $company_data = $mailer->getCompanyData($params['company_id'], $lang_code);

        foreach (array('reply_to', 'to', 'cc') as $way) {
            if (!empty($params[$way])) {
                foreach ((array) $params[$way] as $way_ar) {
                    ${$way}[] = !empty($company_data[$way_ar]) ? $company_data[$way_ar] : $way_ar;
                }
            }
        }

        if (!empty($reply_to)) {
            $reply_to = $mailer->formatEmails($reply_to);
            foreach ($reply_to as $rep_to) {
                $mailer->AddReplyTo($rep_to);
            }
        }

        if (!empty($cc)) {
            $cc = $mailer->formatEmails($cc);
            foreach ($cc as $c) {
                $mailer->AddCC($c);
            }
        }

        $from = $mailer->getEmailFrom($params['from'], $params['company_id'], $lang_code);

        if (empty($to) || empty($from['email'])) {
            return false;
        }
        list($from['email']) = $mailer->formatEmails($from['email']);

        $mailer->SetFrom($from['email'], $from['name']);
        $mailer->IsHTML(isset($params['is_html']) ? $params['is_html'] : true);
        $mailer->CharSet = CHARSET;

        // Pass data to template
        foreach ($params['data'] as $k => $v) {
            \Tygh::$app['view']->assign($k, $v);
        }
        \Tygh::$app['view']->assign('company_data', $company_data);

        $company_id = isset($params['company_id']) ? $params['company_id'] : null;

        if (!empty($params['tpl'])) {
            // Get template name for subject and render it
            $tpl_ext = fn_get_file_ext($params['tpl']);
            $subj_tpl = str_replace('.' . $tpl_ext, '_subj.' . $tpl_ext, $params['tpl']);
            $subject = \Tygh::$app['view']->displayMail($subj_tpl, false, $area, $company_id, $lang_code);

            // Render template for body
            $body = \Tygh::$app['view']->displayMail($params['tpl'], false, $area, $company_id, $lang_code);

        } else {
            $subject = $params['subj'];
            $body = $params['body'];
        }

        $mailer->Body = $mailer->attachImages($body);
        $mailer->Subject = trim($subject);

        if (!empty($params['attachments'])) {
            foreach ($params['attachments'] as $name => $file) {
                $mailer->AddAttachment($file, $name);
            }
        }

        $to = $mailer->formatEmails($to);

        foreach ($to as $v) {
            $mailer->ClearAddresses();
            $mailer->AddAddress($v, '');
            $result = $mailer->Send();
            if (!$result) {
                fn_set_notification('E', __('error'), __('error_message_not_sent') . ' ' . $mailer->ErrorInfo);
            }

            fn_set_hook('send_mail', $mailer);
        }

        return $result;
    }

    private static function instance($mailer_settings = array())
    {
        if (empty(self::$default_settings)) {
            self::$default_settings = Settings::instance()->getValues('Emails');
        }

        if (empty($mailer_settings)) {
            $mailer_settings = self::$default_settings;
        }

        if (empty(self::$_mailer)) {
            self::$_mailer = new Mailer();
            self::$_mailer->LE = (defined('IS_WINDOWS')) ? "\r\n" : "\n";
            self::$_mailer->PluginDir = Registry::get('config.dir.lib') . 'vendor/phpmailer/phpmailer';
        }

        if ($mailer_settings['mailer_send_method'] == 'smtp') {
            self::$_mailer->IsSMTP();
            self::$_mailer->SMTPAuth = ($mailer_settings['mailer_smtp_auth'] == 'Y') ? true : false;
            self::$_mailer->Host = $mailer_settings['mailer_smtp_host'];
            self::$_mailer->Username = $mailer_settings['mailer_smtp_username'];
            self::$_mailer->Password = $mailer_settings['mailer_smtp_password'];
            self::$_mailer->SMTPSecure = $mailer_settings['mailer_smtp_ecrypted_connection'];

        } elseif ($mailer_settings['mailer_send_method'] == 'sendmail') {
            self::$_mailer->IsSendmail();
            self::$_mailer->Sendmail = $mailer_settings['mailer_sendmail_path'];

        } else {
            self::$_mailer->IsMail();
        }

        return self::$_mailer;

    }

    public function attachImages($body)
    {
        $http_location = Registry::get('config.http_location');
        $https_location = Registry::get('config.https_location');
        $http_path = Registry::get('config.http_path');
        $https_path = Registry::get('config.https_path');
        $files = array();

        if (preg_match_all("/(?<=\ssrc=|\sbackground=)('|\")(.*)\\1/SsUi", $body, $matches)) {
            $files = fn_array_merge($files, $matches[2], false);
        }

        if (preg_match_all("/(?<=\sstyle=)('|\").*url\(('|\"|\\\\\\1)(.*)\\2\).*\\1/SsUi", $body, $matches)) {
            $files = fn_array_merge($files, $matches[3], false);
        }

        if (empty($files)) {
            return $body;
        } else {
            $files = array_unique($files);
            foreach ($files as $k => $_path) {
                $cid = 'csimg'.$k;
                $path = str_replace('&amp;', '&', $_path);

                $real_path = '';
                // Replace url path with filesystem if this url is NOT dynamic
                if (strpos($path, '?') === false && strpos($path, '&') === false) {
                    if (($i = strpos($path, $http_location)) !== false) {
                        $real_path = substr_replace($path, Registry::get('config.dir.root'), $i, strlen($http_location));
                    } elseif (($i = strpos($path, $https_location)) !== false) {
                        $real_path = substr_replace($path, Registry::get('config.dir.root'), $i, strlen($https_location));
                    } elseif (!empty($http_path) && ($i = strpos($path, $http_path)) !== false) {
                        $real_path = substr_replace($path, Registry::get('config.dir.root'), $i, strlen($http_path));
                    } elseif (!empty($https_path) && ($i = strpos($path, $https_path)) !== false) {
                        $real_path = substr_replace($path, Registry::get('config.dir.root'), $i, strlen($https_path));
                    }
                }

                if (empty($real_path)) {
                    $real_path = (strpos($path, '://') === false) ? $http_location .'/'. $path : $path;
                }

                list($width, $height, $mime_type) = fn_get_image_size($real_path);

                if (!empty($width)) {
                    $cid .= '.' . fn_get_image_extension($mime_type);
                    $content = fn_get_contents($real_path);
                    $this->addStringEmbeddedImage($content, $cid, $cid, 'base64', $mime_type);

                    $body = preg_replace("/(['\"])" . str_replace("/", "\/", preg_quote($_path)) . "(['\"])/Ss", "\\1cid:" . $cid . "\\2", $body);
                }
            }
        }

        return $body;
    }

    public function formatEmails($emails)
    {
        $result = array();
        foreach ((array) $emails as $email) {
            $email = str_replace(';', ',', $email);
            $res = explode(',', $email);
            foreach ($res as &$v) {
                $v = trim($v);
            }
            $result = array_merge($result, $res);
        }

        $result = array_unique($result);

        foreach ($result as $k => $email) {
            $result[$k] = Url::normalizeEmail($email);
            if (!$result[$k]) {
                unset($result[$k]);
            }
        }

        return $result;
    }

    public static function ValidateAddress($email, $method = 'auto')
    {
        return fn_validate_email($email, false);
    }

    /**
     * Get email from from company
     *
     * @param string|array $from
     * @param int $company_id
     * @param string $lang_code
     * @return array
     */
    public function getEmailFrom($from, $company_id, $lang_code = CART_LANGUAGE)
    {
        $result = array(
            'email' => '',
            'name' => ''
        );
        $company_data = $this->getCompanyData($company_id, $lang_code);

        if (!is_array($from)) {
            if (!empty($company_data[$from])) {
                $result['email'] =  $company_data[$from];
                $result['name'] = strstr($from, 'default_') ? $company_data['default_company_name'] : $company_data['company_name'];
            } elseif (array_key_exists($from, $company_data) && $company_id == 0 && fn_allowed_for('ULTIMATE')) {
                $default_company_id = (int) fn_get_default_company_id();
                $default_company_data = $this->getCompanyData($default_company_id, $lang_code);
                $result['email'] = $default_company_data[$from];
                $result['name'] = strstr($from, 'default_') ? $default_company_data['default_company_name'] : $default_company_data['company_name'];
            } elseif (self::ValidateAddress($from)) {
                $result['email'] = $from;
            }
        } elseif (!empty($from['email'])) {
            if (!empty($company_data[$from['email']])) {
                $result['email'] =  $company_data[$from['email']];
                if (empty($from['name'])) {
                    $from['name'] = strstr($from['email'], 'default_') ? $company_data['default_company_name'] : $company_data['company_name'];
                }
            } else {
                $result['email'] = $from['email'];
            }
            $result['name'] = !empty($company_data[$from['name']]) ? $company_data[$from['name']] : $from['name'];
        }

        return $result;
    }

    /**
     * Get company data
     *
     * @param int $company_id
     * @param string $lang_code
     * @return array
     */
    public function getCompanyData($company_id, $lang_code = CART_LANGUAGE)
    {
        return fn_get_company_placement_info($company_id, $lang_code);
    }
}
