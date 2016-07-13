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

namespace Tygh\Ym;

use Tygh\Registry;
use Tygh\Ym\Offers;
use Tygh\Tools\SecurityHelper;

class Yml2 implements IYml2
{

    const ITERATION_ITEMS = 100;
    const ITERATION_OFFERS = ITERATION_OFFERS;
    const IMAGES_LIMIT = 10;
    const ARCHIVES_LIMIT = 10;

    protected $company_id;
    protected $options = array();
    protected $lang_code = DESCR_SL;
    protected $offset = 0;
    protected $debug = false;
    protected $yml2_product_export = 0;
    protected $yml2_product_skip = 0;
    protected $price_id;
    protected $price_list;

    protected $offer = null;
    protected $offers = array();
    protected $log = null;

    protected $exclude_category_ids = array();
    protected $hidden_category_ids = array();
    protected $export_category_ids = array();

    protected $filename = 'ym';
    protected $filepath = '';
    protected $filepath_temp = '';

    public function __construct($company_id, $price_id = 0, $lang_code = DESCR_SL, $offset = 0, $debug = false, $options = array())
    {
        $this->company_id = $company_id;
        $this->lang_code  = $lang_code;
        $this->offset     = (int) $offset;
        $this->debug      = $debug;

        if (!empty($price_id)) {
            $this->price_id = $price_id;
            $this->price_list = fn_yml_get_price_list($price_id);

            if (!empty($options)) {
                $this->options = $options;
            } else {
                $this->options = fn_yml_get_options($price_id);
            }

            $this->log = new Logs('csv', $price_id);

            $this->filepath = $this->getFilePath();
            $this->filepath_temp = $this->getTempFilePath();
        }

        $this->yml2_product_export = fn_get_storage_data('yml2_product_export_' . $this->price_id);
        $this->yml2_product_skip = fn_get_storage_data('yml2_product_skip_' . $this->price_id);

        if (!empty($this->options)) {
            $this->filename = $this->filename . '_' . $this->options['price_id'];

            $this->options['company_id'] = $this->company_id;

            if (!empty($this->options['export_categories'])) {
                $this->export_category_ids = explode(',', $this->options['export_categories']);
            }

            if (!empty($this->options['exclude_categories_ext'])) {
                $this->exclude_category_ids = explode(',', $this->options['exclude_categories_ext']);
            }

            if (!empty($this->options['hidden_categories'])) {
                $this->hidden_category_ids = explode(',', $this->options['hidden_categories']);
            }

            if (!empty($this->options['export_hidden_categories']) && $this->options['export_hidden_categories'] == 'Y' && !empty($this->options['hidden_categories_ext'])) {
                $hidden_category_ids_ext = explode(',', $this->options['hidden_categories_ext']);
                $this->export_category_ids = array_merge($this->export_category_ids, $hidden_category_ids_ext);
            }

            $this->options['offer_type_categories'] = $this->getYMLCategories('yml2_offer_type');
            $this->options['yml2_model_categories'] = $this->getYMLCategories('yml2_model');
            $this->options['yml2_type_prefix_categories'] = $this->getYMLCategories('yml2_type_prefix');
            $this->options['market_category'] = $this->getYMLCategories('yml2_market_category');

            $this->options['yml2_model_select'] = $this->getYMLCategories('yml2_model_select');
            foreach($this->options['yml2_model_select'] as $category_id => $select) {
                $select = explode('.', $select);

                $this->options['yml2_model_select'][$category_id] = array();
                if (!fn_is_empty($select)) {
                    $this->options['yml2_model_select'][$category_id]['type'] = $select[0];
                    $this->options['yml2_model_select'][$category_id]['value'] = $select[1];
                }
            }

            $this->options['yml2_type_prefix_select'] = $this->getYMLCategories('yml2_type_prefix_select');
            foreach($this->options['yml2_type_prefix_select'] as $category_id => $select) {
                $select = explode('.', $select);

                $this->options['yml2_type_prefix_select'][$category_id] = array();
                if (!fn_is_empty($select)) {
                    $this->options['yml2_type_prefix_select'][$category_id]['type'] = $select[0];
                    $this->options['yml2_type_prefix_select'][$category_id]['value'] = $select[1];
                }
            }

        }
    }

    public function get()
    {
        if ($this->debug) {
            $this->generate();
        }

        if (!file_exists($this->filepath)) {
            fn_echo(__('yml2_file_not_exist', array('[url]' => fn_url('yml.generate', 'C', 'http'))));
            return false;
        }

        $this->sendResult($this->filepath);
    }

    public function view()
    {
        $filename = $this->getCacheFileName();

        if (!file_exists($filename) || $this->debug) {
            $this->generate($filename);
        }

        $this->sendResult($filename);
    }

    protected function getPathDir()
    {
        $path = Registry::get('config.dir.files');

        if (!empty($this->company_id)) {
            $path .=  $this->company_id . '/';
        }

        fn_mkdir($path);

        return $path;
    }

    public function getFilePath()
    {
        return $this->getPathDir() . 'yml/' . $this->filename . '_' . $this->options['price_id'] . '.yml';
    }

    public function getTempFilePath()
    {
        return $this->getPathDir() . 'yml/' . $this->filename . '_' . $this->options['price_id'] . '_generation.yml';
    }

    public function getCacheFileName()
    {
        $this->options['price_id'] = isset($this->options['price_id']) ? $this->options['price_id'] : '';
        $path = sprintf('%syml/%s_' . $this->filename . '_' . $this->options['price_id'] . '.yml',
            fn_get_cache_path(false, 'C', $this->company_id),
            $this->company_id
        );

        return $path;
    }

    public function clearCache()
    {
        return fn_rm($this->getCacheFileName());
    }

    public static function clearCaches($company_ids = null)
    {

        if (is_null($company_ids)) {
            if (Registry::get('runtime.company_id') || Registry::get('runtime.simple_ultimate')) {
                $company_ids = Registry::get('runtime.company_data.company_id');
            } else {
                $company_ids = array_keys(fn_get_short_companies());
            }
        }

        $price_id = db_get_field("SELECT DISTINCT param_id FROM ?:yml_param WHERE param_type = 'price_list'");

        foreach ((array) $company_ids as $company_id) {
            $self = new self($company_id, $price_id);
            $self->clearCache();
        }
    }

    public function generate($filepath = '')
    {
        @ignore_user_abort(1);
        @set_time_limit(0);
        register_shutdown_function(array($this, 'shutdownHandler'));

        if (!empty($filepath)) {
            $this->filepath_temp = $filepath;
        }

        fn_mkdir(dirname($this->filepath_temp));

        $continue = false;
        if (file_exists($this->filepath_temp) && $this->offset > 0) {
            $continue = true;
        }

        if ($continue) {
            $this->log->write(Logs::INFO, '', 'Continue ' . date('d.m.Y H:i:s', time()) . '. Offset ' . $this->offset);

        } else {
            $status_generate = fn_get_storage_data('yml2_status_generate_' . $this->price_id);
            if ($status_generate == 'active' && file_exists($this->filepath_temp)) {
                fn_echo(__("yml_export.generation_was_started"));
                exit();
            }

            fn_rm($this->filepath_temp);
            $this->offset = 0;

            $this->log->write(Logs::INFO, '', 'Start ' . date('d.m.Y H:i:s', time()));
            fn_set_storage_data('yml2_export_start_time_' . $this->price_id, time());
        }

        fn_set_storage_data('yml2_status_generate_' . $this->price_id, 'active');

        $file = fopen($this->filepath_temp, 'ab');

        if (!$continue) {
            $this->head($file);
        }

        $this->body($file);
        $this->bottom($file);

        fclose($file);

        $this->log->write(Logs::INFO, '', 'Finish ' .  date('d.m.Y H:i:s', time()));
        $this->log->write(Logs::INFO, '', 'Product export ' . $this->yml2_product_export . '. Product skip ' . $this->yml2_product_skip);

        $data = array(
            '[export]' => $this->yml2_product_export,
            '[skip]' => $this->yml2_product_skip,
            '[cron]' => defined('CONSOLE') ? 'Cron. ' : ''
        );

        fn_log_event('yml_export', 'export', array ('message' => __('text_log_action_export', $data)));

        if ($this->options['detailed_generation'] == 'Y') {

            $path = $this->log->getTempLogFile();

            if($path) {
                $log = fopen($path, 'r');
                $line = fgets($log);
                $info_line = true;

                while (!feof($log)) {
                    $line = fgets($log);

                    if (empty($line)) {
                        continue;
                    }

                    $data = explode(';', $line);

                    if ($data[0] == '[INFO]' && !$info_line) {
                        fn_echo(NEW_LINE);

                    } elseif ($data[0] != '[INFO]' && $info_line) {
                        fn_echo(NEW_LINE);
                    }

                    $data[1] = isset($data[1]) ? $data[1] : '';
                    $data[2] = isset($data[2]) ? $data[2] : '';

                    fn_echo($data[0] . $data[1] . $data[2] . NEW_LINE);

                    $info_line = ($data[0] == '[INFO]');
                }

                fclose($log);
            }
        }

        $this->log->rotate();

        if (empty($filepath)) {
            $this->backupYml();
            if (file_exists($this->filepath_temp)) {
                fn_rm($this->filepath);
                fn_rename($this->filepath_temp, $this->filepath);
            }
        }

        fn_set_storage_data('yml2_product_export_' . $this->price_id);
        fn_set_storage_data('yml2_product_skip_' . $this->price_id);

        fn_set_storage_data('yml2_export_start_time_' . $this->price_id);
        fn_set_storage_data('yml2_export_count_' . $this->price_id);
        fn_set_storage_data('yml2_export_offset_' . $this->price_id);

        fn_set_storage_data('yml2_export_time_' . $this->price_id, time());
        fn_set_storage_data('yml2_status_generate_' . $this->price_id, 'finish');
    }

    protected function head($file)
    {
        $yml2_header = array(
            '<?xml version="1.0" encoding="' . $this->options['export_encoding'] . '"?>',
            '<!DOCTYPE yml2_catalog SYSTEM "shops.dtd">',
            '<yml_catalog date="' . date('Y-m-d G:i') . '">',
            '<shop>'
        );

        $yml2_data = array(
            'name' => $this->getShopName(),
            'company' => SecurityHelper::escapeHtml(Registry::get('settings.Company.company_name')),
            'url' => Registry::get('config.http_location'),
            'platform' => PRODUCT_NAME,
            'version' => PRODUCT_VERSION,
            'agency' => 'Agency',
            'email' => Registry::get('settings.Company.company_orders_department'),
        );

        $this->buildCurrencies($yml2_data);

        $this->buildCategories($yml2_data);

        if (!fn_is_empty($this->options['delivery_options'])) {
            foreach($this->options['delivery_options'] as $option) {
                $option_attr = 'option@cost=' . $option['cost'] . '@days=' . $option['days'];
                if (!empty($option['order_before'])) {
                    $option_attr .= '@order-before=' . $option['order_before'];
                }
                $yml2_data['delivery-options'][$option_attr] = '';
            }
        }

        fwrite($file, implode(PHP_EOL, $yml2_header) . PHP_EOL);
        fwrite($file, fn_yml_array_to_yml($yml2_data));
        fwrite($file, '<offers>' . PHP_EOL);
    }

    protected function body($file)
    {
        $this->generateOffers($file);
    }

    protected function bottom($file)
    {
        fwrite($file, '</offers>' . PHP_EOL);
        fwrite($file, '</shop>' . PHP_EOL);
        fwrite($file, '</yml_catalog>' . PHP_EOL);
    }

    protected function sendResult($filename)
    {
        header("Content-Type: text/xml;charset=" . $this->options['export_encoding']);

        readfile($filename);
        exit;
    }

    protected function getShopName()
    {
        $shop_name = $this->options['shop_name'];

        if (empty($shop_name)) {
            if (fn_allowed_for('ULTIMATE')) {
                $shop_name = fn_get_company_name($this->company_id);
            } else {
                $shop_name = Registry::get('settings.Company.company_name');
            }
        }



        return SecurityHelper::escapeHtml($shop_name);
    }

    protected function buildCurrencies(&$yml2_data)
    {
        $currencies = Registry::get('currencies');

        if (CART_PRIMARY_CURRENCY != "RUB" && CART_PRIMARY_CURRENCY != "UAH" && CART_PRIMARY_CURRENCY != "BYR" && CART_PRIMARY_CURRENCY != "KZT") {

            if (!empty($currencies['RUB'])) {
                $v_coefficient = $currencies['RUB']['coefficient'];
                $default_currencies = 'RUB';

            } elseif (!empty($currencies['UAH'])) {
                $v_coefficient = $currencies['UAH']['coefficient'];
                $default_currencies = 'UAH';

            } elseif (!empty($currencies['BYR'])) {
                $v_coefficient = $currencies['BYR']['coefficient'];
                $default_currencies = 'BYR';

            } elseif (!empty($currencies['KZT'])) {
                $v_coefficient = $currencies['KZT']['coefficient'];
                $default_currencies = 'KZT';

            } else {
                $v_coefficient = 1;
                $default_currencies = CART_PRIMARY_CURRENCY;
            }
            $primary_coefficient = $currencies[CART_PRIMARY_CURRENCY]['coefficient'];

            foreach ($currencies as $cur) {
                if ($this->currencyIsValid($cur['currency_code']) && $cur['status'] == 'A') {
                    if ($default_currencies == $cur['currency_code']) {
                        $coefficient = '1.0000';
                        $yml2_data['currencies']['currency@id=' . $cur['currency_code'] . '@rate=' . $coefficient] = '';

                    } else {
                        $coefficient = $cur['coefficient'] * $primary_coefficient / $v_coefficient;
                        $yml2_data['currencies']['currency@id=' . $cur['currency_code'] . '@rate=' . $coefficient] = '';
                    }
                }
            }

        } else {
            foreach ($currencies as $cur) {
                if ($this->currencyIsValid($cur['currency_code']) && $cur['status'] == 'A') {
                    $yml2_data['currencies']['currency@id=' . $cur['currency_code'] . '@rate=' . $cur['coefficient']] = '';
                }
            }
        }
    }

    protected function currencyIsValid($currency)
    {
        $currencies = array(
            'RUR',
            'RUB',
            'UAH',
            'BYR',
            'KZT',
            'USD',
            'EUR'
        );

        return in_array($currency, $currencies);
    }

    protected function buildCategories(&$yml2_data)
    {
        $params = array (
            'simple' => false,
            'plain' => true
        );

        list($categories_tree, ) = fn_get_categories($params, $this->lang_code);

        if (!empty($this->options['hidden_categories_data'])) {
            foreach($this->options['hidden_categories_data'] as $category_id => $category_data) {
                $categories_tree[] = $category_data;
            }
        }

        foreach ($categories_tree as $cat) {
            if (isset($cat['category_id']) && in_array($cat['category_id'], $this->export_category_ids) && !in_array($cat['category_id'], $this->exclude_category_ids)) {

                if ($cat['parent_id'] == 0) {
                    $yml2_data['categories']['category@id=' . $cat['category_id']] = SecurityHelper::escapeHtml($cat['category']);

                } else {
                    $yml2_data['categories']['category@id=' . $cat['category_id'] . '@parentId=' . $cat['parent_id']] = SecurityHelper::escapeHtml($cat['category']);
                }
            }
        }
    }

    /**
     * Export product features
     */
    protected function getProductFeatures($product)
    {
        $result = array();

        $params = array(
            'product_id' => $product['product_id'],
            'plain' => true,
            'variants' => true,
            'variants_selected_only' => true,
            'existent_only' => true,
            'exclude_group' => true,
        );

        list($product_features, ) = fn_get_product_features($params);

        if (!empty($product_features)) {
            foreach ($product_features as $f) {

                if (in_array($this->options['price_id'], $f['yml2_exclude_prices'])) {
                    continue;
                }

                if ($f['display_on_catalog'] == "Y" || $f['display_on_product'] == "Y" || $f['display_on_header'] == 'Y') {

                    $feature = array(
                        'description' => $f['description'],
                        'feature_id' => $f['feature_id'],
                        'yml2_unit' => trim($f['yml2_variants_unit'])
                    );

                    $ft = $f['feature_type'];

                    if ($ft == "C") {
                        $feature['value'] = ($f['value'] == "Y") ? __("yes") : __("no");

                    } elseif (($ft == "S" || $ft == "N" || $ft == "E") && !empty($f['variant_id'])) {

                        $variant = $f['variants'][$f['variant_id']];
                        $feature['value'] = $variant['variant'];

                        if (!empty($variant['yml2_unit'])) {
                            $feature['yml2_unit'] = trim($variant['yml2_unit']);
                        }

                    } elseif ($ft == "T" && !empty($f['value'])) {
                        $feature['value'] = $f['value'];

                    } elseif ($ft == "M") {
                        if (!empty($f['variants'])) {
                            $_value = '';
                            $counter = count($f['variants']);
                            foreach ($f['variants'] as $_variant) {
                                if ($counter > 1) {
                                    $_value .= $_variant['variant'] . ', ';
                                } else {
                                    $_value = $_variant['variant'];
                                }
                            }

                            $feature['value'] = ($counter > 1) ? substr($_value, 0, -2) : $_value;
                        }

                    } elseif ($ft == "O") {
                        $feature['value'] = $f['value_int'];

                    }

                    $result[] = $feature;
                }
            }
        }

        return !empty($result) ? $result : array();
    }

    protected function escape($data)
    {
        $data = preg_replace('/[^\x{0009}\x{000A}\x{000D}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/u', '', $data);

        return strip_tags($data);
    }

    protected function generateOffers($file)
    {
        $fields = array(
            'p.product_id',
            'p.product_code',
            'd.lang_code',
            'pc.category_id',
            'cd.category',
            'pp.price',
            'p.list_price',
            'p.status',
            'p.amount',
            'p.weight',
            'p.shipping_freight',
            'p.shipping_params',
            'p.free_shipping',
            'd.product',
            'd.short_description',
            'd.full_description',
            'p.company_id',
            'p.tracking',
            'p.list_price',
            'p.yml2_brand',
            'p.yml2_origin_country',
            'p.yml2_store',
            'p.yml2_pickup',
            'p.yml2_delivery',
            'p.yml2_delivery_options',
            'p.yml2_bid',
            'p.yml2_cbid',
            'p.yml2_model',
            'p.yml2_sales_notes',
            'p.yml2_type_prefix',
            'p.yml2_offer_type',
            'p.yml2_market_category',
            'p.yml2_manufacturer_warranty',
            'p.yml2_seller_warranty',
            'p.yml2_expiry',
            'p.yml2_purchase_price',
            'p.yml2_description'
        );

        $fields[] = "(
                SELECT GROUP_CONCAT(IF(pc2.link_type = 'M', CONCAT(pc2.category_id, 'M'), pc2.category_id))
                FROM ?:products_categories as pc2
                WHERE product_id = p.product_id
            ) as category_ids";

        $joins = array(
            db_quote(
                "LEFT JOIN ?:product_descriptions as d ON d.product_id = p.product_id AND d.lang_code = ?s",
                $this->lang_code
            ),
            db_quote(
                "LEFT JOIN ?:product_prices as pp"
                . " ON pp.product_id = p.product_id AND pp.lower_limit = 1 AND pp.usergroup_id = 0"
            ),
            db_quote(
                "LEFT JOIN ?:products_categories as pc ON pc.product_id = p.product_id AND pc.link_type = ?s",
                'M'
            ),
            db_quote(
                "LEFT JOIN ?:category_descriptions as cd ON cd.category_id = pc.category_id AND cd.lang_code = ?s",
                $this->lang_code
            )
        );

        $condition = '';

        if ($this->company_id > 0) {
            $condition .= db_quote(' AND company_id = ?i', $this->company_id);
        }

        $exclude_products_ids = array();
        if (!empty($this->options['exclude_categories_not_logging']) && $this->options['exclude_categories_not_logging'] == 'Y' && !empty($this->exclude_category_ids)) {
            $include_products_ids = db_get_fields(
                "SELECT DISTINCT product_id FROM ?:products_categories WHERE category_id NOT IN (?a)", $this->exclude_category_ids
            );

            if (!empty($include_products_ids)) {
                $condition .= db_quote(' AND product_id IN (?a)', $include_products_ids);
            }
        }

        $products_ids = db_get_fields(
            "SELECT DISTINCT object_id FROM ?:yml_exclude_objects WHERE price_id = ?i AND object_type = 'product'", $this->price_id
        );

        $exclude_products_ids = array_merge($exclude_products_ids, $products_ids);

        if (!empty($exclude_products_ids)) {
            $condition .= db_quote(' AND product_id NOT IN (?a)', $exclude_products_ids);
        }

        $product_ids = db_get_fields("SELECT product_id FROM ?:products WHERE status = ?s $condition", 'A');

        fn_set_storage_data('yml2_export_count_' . $this->price_id, count($product_ids));

        $shared_product_ids = array();
        if (isset($this->options['export_shared_products']) && $this->options['export_shared_products'] == 'Y') {
            $categories_join = db_quote('INNER JOIN ?:categories ON ?:categories.category_id = ?:products_categories.category_id');
            $products_join = db_quote('INNER JOIN ?:products ON ?:products.product_id = ?:products_categories.product_id');
            $shared_product_ids = db_get_fields(
                "SELECT DISTINCT ?:products_categories.product_id FROM ?:products_categories $categories_join $products_join " .
                "WHERE ?:categories.company_id = ?i AND link_type = 'A' AND ?:products.status = 'A' ",
                $this->company_id
            );

            $product_ids = array_merge($product_ids, $shared_product_ids);
        }

        $this->offer = new Offers($this->options, $this->log);

        $offers_count = 0;

        while ($ids = array_slice($product_ids, $this->offset, self::ITERATION_ITEMS)) {
            $processed = 0;
            $this->offset += self::ITERATION_ITEMS;
            $products = db_get_array(
                'SELECT ' . implode(', ', $fields)
                . ' FROM ?:products as p'
                . ' ' . implode(' ', $joins)
                . ' WHERE p.product_id IN(?n)'
                . ' GROUP BY p.product_id'
                , $ids
            );

            $products_images_main = fn_get_image_pairs($ids, 'product', 'M', false, true, $this->lang_code);
            $products_images_additional = fn_get_image_pairs($ids, 'product', 'A', false, true, $this->lang_code);

            $params = array(
                'get_options' => false,
                'get_taxed_prices' => false,
                'detailed_params' => false,
            );
            fn_gather_additional_products_data($products, $params);

            foreach ($products as $k => &$product) {
                $processed++;
                if (in_array($product['product_id'], $shared_product_ids)) {
                    $this->prepareSharedProduct($product);
                }

                $product['product_features'] = $this->getProductFeatures($product);

                if (!$this->preBuild($product, $products_images_main, $products_images_additional)) {
                    $this->yml2_product_skip++;

                    continue;
                }

                list($xml, $product_skip) = $this->offer->build($product);

                if (empty($product_skip)) {
                    $this->yml2_product_export++;
                } else {
                    $this->yml2_product_skip += $product_skip;
                }

                $this->stopGeneration();

                fwrite($file, $xml . "\n");

                if ($processed % static::ITERATION_ITEMS == 0) {
                    fn_echo(__('yml_export.products_processed', array(
                        '[items]' => $this->offset + $processed
                    )) . NEW_LINE);
                }
            }

            $offers_count += count($products);

            fn_set_storage_data('yml2_export_offset_' . $this->price_id, $this->offset);

            if (!defined('CONSOLE') && $offers_count >= self::ITERATION_OFFERS) {
                fn_set_storage_data('yml2_product_export_' . $this->price_id, $this->yml2_product_export);
                fn_set_storage_data('yml2_product_skip_' . $this->price_id, $this->yml2_product_skip);
                fclose($file);
                fn_set_storage_data('yml2_status_generate_' . $this->price_id, 'redirect');
                fn_redirect(fn_yml_get_generate_link($this->price_list) . "/" . $this->offset);
            }
        }

        return true;
    }

    public function preBuild(&$product, $products_images_main, $products_images_additional)
    {
        $is_broken = false;

        if ($this->options['export_null_price'] == 'N') {
            $price = !floatval($product['price']) ? fn_parse_price($product['price']) : intval($product['price']);
            if (empty($price)) {
                $this->log->write(Logs::SKIP_PRODUCT, $product, __('yml2_log_product_price_is_empty'));
                $is_broken = true;
            }
        }

        $exclude_categories = array_diff($product['category_ids'], $this->exclude_category_ids);
        if (empty($exclude_categories)) {
            $this->log->write(Logs::SKIP_PRODUCT, $product, __('yml2_log_category_excluded'));
            $is_broken = true;

        } else {
            $export_categories = array_intersect($product['category_ids'], $this->export_category_ids);
            if (empty($export_categories)) {
                $this->log->write(Logs::SKIP_PRODUCT, $product, __('yml2_log_category_not_visible'));
                $is_broken = true;
            }
        }

        $product['product'] = $this->escape($product['product']);
        $product['full_description'] = $this->escape($product['full_description']);
        $product['short_description'] = $this->escape($product['short_description']);

        if ($this->options['export_stock'] == 'Y') {
            if ($product['tracking'] == 'B' && $product['amount'] <= 0) {
                $this->log->write(Logs::SKIP_PRODUCT, $product, __('yml2_log_product_amount_is_empty'));
                $is_broken = true;
            }
        }

        if (!$this->offer->preBuild($product)) {
            $is_broken = true;
        }

        if ($is_broken) {
            return false;
        }

        if (!empty($this->options['utm_link'])) {
            $product['product_url'] = $this->getUTMLink($product, $this->options['utm_link'], 'products.view?product_id=' . $product['product_id']);
        } else {
            $product['product_url'] = 'products.view?product_id=' . $product['product_id'];
        }

        // Images
        $images = array_merge(
            $products_images_main[$product['product_id']],
            $products_images_additional[$product['product_id']]
        );

        $product['images'] = array_slice($images, 0, self::IMAGES_LIMIT);

        return true;
    }

    public function backupYml()
    {
        if (file_exists($this->filepath)) {
            $path = fn_get_files_dir_path() . 'yml/';
            $archive_path = $path . 'archives/';
            fn_mkdir($archive_path);

            $archive_name = 'ym_' . date('dmY_His', TIME) . '.tgz';
            fn_compress_files($archive_name, $this->filename . '.yml', $path);
            fn_rename($path . $archive_name, $archive_path . $archive_name);

            $archives_list = fn_get_dir_contents($archive_path, false, true);
            if (!empty($archives_list) && count($archives_list) > self::ARCHIVES_LIMIT) {
                rsort($archives_list);
                list(, $old_archives) = array_chunk($archives_list, self::ARCHIVES_LIMIT);
                foreach($old_archives as $filename) {
                    fn_rm($archive_path . $filename);
                }
            }
        }
    }

    protected function getUTMLink($product, $utm, $product_url)
    {
        preg_match_all('/\{(.+?)\}/is', $utm, $words, PREG_OFFSET_CAPTURE);

        if (!empty($words[1])) {

            $replace_words = array();

            foreach($words[1] as $index => $word_data) {
                list($word) = $word_data;
                if (isset($product[$word])) {
                    $replace_words[$word] = $product[$word];
                }
            }

            foreach($replace_words as $word => $value) {
                $utm = str_replace("{" . $word . "}", $value, $utm);
            }
        }

        return $product_url . "&" . $utm;
    }

    protected function getYMLCategories($field_name)
    {
        $offer_type_categories = array();
        $categories = db_get_hash_array("SELECT category_id, parent_id, $field_name FROM ?:categories", 'category_id');

        foreach(array_keys($categories) as $category_id) {
            if (empty($categories[$category_id][$field_name])) {
                $offer_type_categories[$category_id] = $this->getYmlField($categories[$category_id]['parent_id'], $categories, $field_name);
            } else {
                $offer_type_categories[$category_id] = $categories[$category_id][$field_name];
            }
        }

        return $offer_type_categories;
    }

    protected function getYmlField($category_id, &$categories, $field_name)
    {
        if (empty($categories[$category_id][$field_name])) {

            if (!empty($categories[$category_id]['parent_id'])) {
                $categories[$category_id][$field_name] = $this->getYmlField($categories[$category_id]['parent_id'], $categories, $field_name);
            } else {
                $categories[$category_id][$field_name] = '';
            }
        }

        return $categories[$category_id][$field_name];
    }

    protected function stopGeneration()
    {
        $status = db_get_field('SELECT `data` FROM ?:storage_data WHERE `data_key` = ?s', 'yml2_status_generate_' . $this->price_id);
        if ($status != 'active') {
            fn_set_storage_data('yml2_status_generate_' . $this->price_id, 'abort');

            if (file_exists($this->filepath_temp)) {
                fn_rm($this->filepath_temp);
            }

            fn_echo(__("yml_export.stop_generate"));
            exit();
        }
    }

    public function shutdownHandler()
    {
        $status = db_get_field('SELECT `data` FROM ?:storage_data WHERE `data_key` = ?s', 'yml2_status_generate_' . $this->price_id);
        fn_set_storage_data('yml2_export_time_' . $this->price_id, time());

        if ($status != 'redirect' || $status != 'finish') {

        }
    }

    public function prepareSharedProduct(&$product)
    {
        if (fn_allowed_for('ULTIMATE') && $this->company_id) {
            $table_name = '?:ult_product_prices';
            $condition = db_quote(' AND prices.company_id = ?i', $this->company_id);
        } else {
            $table_name = '?:product_prices';
            $condition = '';
        }

        $price_data = db_get_row("SELECT DISTINCT prices.product_id, prices.lower_limit, usergroup_id, "
                                 . " IF(prices.percentage_discount = 0, prices.price, prices.price - (prices.price * prices.percentage_discount)/100) as price "
                                 . " FROM $table_name prices WHERE prices.product_id = ?i $condition AND prices.usergroup_id IN (?n) ORDER BY lower_limit",
                                 $product['product_id'], array_merge(array(USERGROUP_ALL), $_SESSION['auth']['usergroup_ids']));
        if (!empty($price_data)) {
            $product['price'] = $product['base_price'] = $price_data['price'];
        }

        $company_product_data = db_get_row("SELECT * FROM ?:ult_product_descriptions WHERE product_id = ?i AND company_id = ?i AND lang_code = ?s",
                                           $product['product_id'], Registry::get('runtime.company_id'), DESCR_SL);
        if (!empty($company_product_data)) {
            unset($company_product_data['company_id']);
            $product = array_merge($product, $company_product_data);
        }
    }

}
