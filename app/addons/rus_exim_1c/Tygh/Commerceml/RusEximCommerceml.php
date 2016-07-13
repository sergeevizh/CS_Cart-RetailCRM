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

namespace Tygh\Commerceml;

use Tygh\Registry;
use Tygh\Storage;
use Tygh\Commerceml\Logs;
use Tygh\Enum\ProductFeatures;

class RusEximCommerceml
{
    public static $url;
    public static $path_commerceml;
    public static $url_commerceml;
    public static $url_images;
    public static $default_category;
    public static $company_id;
    public static $cml;
    public static $s_commerceml;

    public static $categories_commerceml = array();
    public static $features_commerceml = array();

    public static function addMessageLog($message)
    {
        $log = new Logs();
        $log->write("Data : " . date("d-m-Y h:i:s", TIME) . " - " . $message);
    }

    public static function showMessageError($message)
    {
        self::addMessageLog($message);

        header('WWW-Authenticate: Basic realm="Authorization required"');
        header('HTTP/1.0 401 Unauthorized');
        fn_echo($message);
    }

    public static function exportDataCheckauth($service_exchange)
    {
        self::addMessageLog("Send data checkauth: " . \Tygh::$app['session']->getName());

        fn_echo("success\n");

        if ($service_exchange == 'exim_class') {
            fn_echo($service_exchange . "\n");
        } else {
            fn_echo(\Tygh::$app['session']->getName() . "\n");
        }

        fn_echo(\Tygh::$app['session']->getID());
    }

    public static function exportDataInit()
    {
        self::addMessageLog("Send file limit: " . FILE_LIMIT);

        fn_echo("zip=no\n");
        fn_echo('file_limit=' . FILE_LIMIT . "\n");
    }

    public static function getDirCommerceML()
    {
        self::$path_commerceml = fn_get_files_dir_path() . 'exim/1C_' . date('dmY') . '/';
        self::$url_commerceml = Registry::get('config.http_location') . '/' . fn_get_rel_dir(self::$path_commerceml);
        self::$url_images = Storage::instance('images')->getAbsolutePath('from_1c/');

        return array(self::$path_commerceml, self::$url_commerceml, self::$url_images);
    }

    public static function getParamsCommerceml()
    {
        self::$cml = fn_get_schema('cml_fields', 'fields_names');
        self::$s_commerceml = Registry::get('addons.rus_exim_1c');

        return array(self::$cml, self::$s_commerceml);
    }

    public static function checkAllwedAccess($user_data)
    {
        if (empty($user_data['usergroups'])) {
            return true;
        }

        foreach ($user_data['usergroups'] as $usergroup) {
            $privilege = db_get_field("SELECT privilege FROM ?:usergroup_privileges WHERE usergroup_id = ?i AND privilege = 'exim_1c'", $usergroup['usergroup_id']);

            if ((!empty($privilege)) && ($usergroup['status'] == 'A')) {
                return true;
            }
        }

        return false;
    }

    public static function getCompanyStore($user_data)
    {
        self::$company_id = 0;

        if (PRODUCT_EDITION == 'ULTIMATE') {
            if (Registry::get('runtime.simple_ultimate')) {
                self::$company_id = Registry::get('runtime.forced_company_id');
            } else {
                if ($user_data['company_id'] == 0) {
                    self::addMessageLog("For import used store administrator");
                    fn_echo('SHOP IS NOT SIMPLE');

                    exit;
                } else {
                    self::$company_id = $user_data['company_id'];
                    Registry::set('runtime.company_id', self::$company_id);
                }
            }

        } elseif ($user_data['user_type'] == 'V') {
            if ($user_data['company_id'] == 0) {
                self::addMessageLog("For import used store administrator");
                fn_echo('SHOP IS NOT SIMPLE');

                exit;
            } else {
                self::$company_id = $user_data['company_id'];
                Registry::set('runtime.company_id', self::$company_id);
            }

        } else {
            Registry::set('runtime.company_id', self::$company_id);
        }
    }

    public static function getFileCommerceml($filename)
    {
        self::addMessageLog("Parsing file data " . $filename);

        $xml = @simplexml_load_file(self::$path_commerceml . $filename);
        if ($xml === false) {
            self::addMessageLog("Can not read file " . $filename);

            return false;
        }

        return $xml;
    }

    public static function xmlCheckValidate($file_path)
    {
        $t_commerceml = $t_product = false;
        $xml_validate = true;
        $xml = new \XMLReader();
        if (file_exists($file_path) && ($xml->open($file_path)) && (filesize($file_path) != 0)) {
            while (@$xml->read()) {
                if($xml->nodeType == \XMLReader::END_ELEMENT){
                    if ($xml->name === self::$cml['commerceml']) {
                        $t_commerceml = true;
                    }
                    if (($xml->name === self::$cml['catalog']) || ($xml->name === self::$cml['packages']) || ($xml->name === self::$cml['document'])) {
                        $t_product = true;
                    }
                }
            }

            if (!$t_commerceml || !$t_product) {
                $xml_validate = false;
            }
        }

        return $xml_validate;
    }

    public static function createImportFile($filename)
    {
        self::addMessageLog("Loadding data file " . $filename);

        $file_mode = 'w';
        list($path_commerceml, $url_commerceml, $url_images) = self::getDirCommerceML();
        if (!is_dir($path_commerceml)) {
            fn_mkdir($path_commerceml);
            @chmod($path_commerceml, 0777);
        }
        $file_path = $path_commerceml . $filename;

        $xml_validate = self::xmlCheckValidate($file_path);

        if (self::isFileProductImage($filename)) {
            if (!is_dir($url_images)) {
                fn_mkdir($url_images);
            }
            $file_path = $url_images . $filename;
        }

        $export_data = fn_get_contents('php://input');
        if ((!$xml_validate) || empty($export_data)) {
            $file_mode = 'a';
        }

        $file = @fopen($file_path, $file_mode);
        if (!$file) {
            self::addMessageLog("File " . $filename . " can not create");
            return false;
        }
        fwrite($file, $export_data);
        fclose($file);
        @chmod($file_path, 0777);

        return true;
    }

    public static function importDataProductFile($import_data, $user_data, $service_exchange, $lang_code, $manual = false)
    {
        self::addMessageLog("Started import date to file import.xml, parameter service_exchange = " . $service_exchange);

        $cml = self::$cml;
        $import_params = array(
            'user_data' => $user_data,
            'service_exchange' => $service_exchange,
            'lang_code' => $lang_code,
            'manual' => $manual
        );

        if (empty(\Tygh::$app['session']['exim_1c']['import_products'])) {
            if (self::$s_commerceml['exim_1c_allow_import_categories'] == 'Y') {
                self::importCategoriesFile($import_data->{$cml['classifier']}->{$cml['groups']}, $import_params);
            }

            if (self::$s_commerceml['exim_1c_allow_import_features'] == 'Y') {
                self::importFeaturesFile($import_data->{$cml['classifier']}->{$cml['properties']}, $import_params);
            }
        }

        if (isset($import_data -> {$cml['catalog']} -> {$cml['products']})) {
            self::importProductsFile($import_data -> {$cml['catalog']} -> {$cml['products']}, $import_params);
        } else {
            fn_echo("success\n");
        }
    }

    public static function importCategoriesFile($data_categories, $import_params, $parent_id = 0)
    {
        $categories_import = array();
        $cml = self::$cml;
        $default_category = self::$s_commerceml['exim_1c_default_category'];
        if (isset($data_categories -> {$cml['group']})) {
            foreach ($data_categories -> {$cml['group']} as $_group) {
                if (self::$s_commerceml['exim_1c_import_type_categories'] == 'name') {
                    $category_id = db_get_field("SELECT category_id FROM ?:category_descriptions WHERE category = ?s", strval($_group -> {$cml['name']}));
                } else {
                    $category_id = db_get_field("SELECT category_id FROM ?:categories WHERE external_id = ?s", strval($_group -> {$cml['id']}));
                }
                $category_data = array(
                    'category' => strval($_group -> {$cml['name']}),
                    'lang_code' => $import_params['lang_code'],
                    'timestamp' => time(),
                    'company_id' => self::$company_id,
                    'external_id' => strval($_group -> {$cml['id']})
                );

                if (empty($category_id)) {
                    $category_id = 0;
                    $category_data['status'] = 'A';
                    $category_data['parent_id'] = $parent_id;
                    self::addMessageLog("New category: " . $category_data['category']);
                }

                if ($import_params['user_data']['user_type'] != 'V') {
                    $category_id = fn_update_category($category_data, $category_id, $import_params['lang_code']);
                    self::addMessageLog("Add category: " . $category_data['category']);
                } else {
                    $category_id = $default_category;
                    $id = db_get_field("SELECT category_id FROM ?:category_descriptions WHERE lang_code = ?s AND category = ?s", $import_params['lang_code'], strval($_group -> {$cml['name']}));
                    if (!empty($id)) {
                        $category_id = $id;
                    }
                }

                $categories_import[strval($_group -> {$cml['id']})] = $category_id;
                if (isset($_group -> {$cml['groups']} -> {$cml['group']})) {
                    self::importCategoriesFile($_group -> {$cml['groups']}, $import_params, $category_id);
                }
            }
            if (!empty(self::$categories_commerceml)) {
                $_categories_commerceml = self::$categories_commerceml;
                self::$categories_commerceml = fn_array_merge($_categories_commerceml, $categories_import);
            } else {
                self::$categories_commerceml = $categories_import;
            }
        }
    }

    public static function importFeaturesFile($data_features, $import_params)
    {
        $cml = self::$cml;
        $features_import = array();
        if (isset($data_features -> {$cml['property']})) {
            $promo_text = trim(self::$s_commerceml['exim_1c_property_product']);
            $shipping_params = self::getShippingFeatures();
            $features_list = fn_explode("\n", self::$s_commerceml['exim_1c_features_list']);
            $deny_or_allow_list = self::$s_commerceml['exim_1c_deny_or_allow'];
            $company_id = self::$company_id;

            foreach ($data_features -> {$cml['property']} as $_feature) {
                $_variants = array();
                $feature_data = array();
                $feature_name = strval($_feature -> {$cml['name']});

                if ($deny_or_allow_list == 'do_not_import') {
                    if (in_array($feature_name, $features_list)) {
                        self::addMessageLog("Feature is not added (do not import): " . $feature_name);
                        continue;
                    }

                } elseif ($deny_or_allow_list == 'import_only') {
                    if (!in_array($feature_name, $features_list)) {
                        self::addMessageLog("Feature is not added (import only): " . $feature_name);
                        continue;
                    }
                }

                $feature_id = db_get_field("SELECT feature_id FROM ?:product_features WHERE external_id = ?s", strval($_feature -> {$cml['id']}));
                $new_feature = false;
                if (empty($feature_id)) {
                    $new_feature = true;
                    $feature_id = 0;
                }

                $feature_data = self::dataFeatures($feature_name, $feature_id, strval($_feature -> {$cml['type_field']}), strval($_feature -> {$cml['id']}), $import_params);
                if (self::displayFeatures($feature_name, $shipping_params)) {
                    if ($promo_text != $feature_name) {
                        $feature_id = fn_update_product_feature($feature_data, $feature_id);
                        self::addMessageLog("Feature is added: " . $feature_name);
                        if ($new_feature) {
                            db_query("INSERT INTO ?:ult_objects_sharing VALUES ($company_id, $feature_id, 'product_features')");
                        }
                    } else {
                        fn_delete_feature($feature_id);
                        $feature_id = 0;
                    }
                } else {
                    fn_delete_feature($feature_id);
                    $feature_id = 0;
                }

                if (!empty($_feature -> {$cml['variants_values']})) {
                    $_feature_data = $_feature -> {$cml['variants_values']} -> {$cml['directory']};
                    foreach ($_feature_data as $_variant) {
                        $_variants[strval($_variant -> {$cml['id_value']})]['id'] = strval($_variant -> {$cml['id_value']});
                        $_variants[strval($_variant -> {$cml['id_value']})]['value'] = strval($_variant -> {$cml['value']});
                    }
                }

                $features_import[strval($_feature -> {$cml['id']})]['id'] = $feature_id;
                $features_import[strval($_feature -> {$cml['id']})]['name'] = $feature_name;
                $features_import[strval($_feature -> {$cml['id']})]['type'] = $feature_data['feature_type'];
                if (!empty($_variants)) {
                    $features_import[strval($_feature -> {$cml['id']})]['variants'] = $_variants;
                }
            }
        }

        $feature_data = array();
        if (self::$s_commerceml['exim_1c_used_brand'] == 'field_brand') {
            $company_id = self::$company_id;
            $feature_id = db_get_field("SELECT feature_id FROM ?:product_features WHERE external_id = ?s AND company_id = ?i", "brand1c", $company_id);
            $new_feature = false;
            if (empty($feature_id)) {
                $new_feature = true;
                $feature_id = 0;
            }
            $feature_data = self::dataFeatures($cml['brand'], $feature_id, ProductFeatures::EXTENDED, "brand1c", $import_params);
            $_feature_id = fn_update_product_feature($feature_data, $feature_id);
            self::addMessageLog("Feature brand is added");
            if ($feature_id == 0) {
                db_query("INSERT INTO ?:ult_objects_sharing VALUES ($company_id, $_feature_id, 'product_features')");
            }
            $features_import['brand1c']['id'] = (!empty($feature_id)) ? $feature_id : $_feature_id;
            $features_import['brand1c']['name'] = $cml['brand'];
        }

        if (!empty($features_import)) {
            if (!empty($features_commerceml)) {
                $_features_commerceml = self::$features_commerceml;
                self::$features_commerceml = fn_array_merge($_features_commerceml, $features_import);
            } else {
                self::$features_commerceml = $features_import;
            }
        }
    }

    public static function dataFeatures($feature_name, $feature_id, $f_type, $external_id, $import_params)
    {
        $feature_type = db_get_field("SELECT feature_type FROM ?:product_features WHERE external_id = ?s", $external_id);

        $data = array(
            'variants' => array(),
            'description' => $feature_name,
            'company_id' => self::$company_id,
            'external_id' => $external_id,
            'feature_type' => $feature_type
        );

        $feature_type = ProductFeatures::TEXT_SELECTBOX;
        if ($f_type == 'Число') {
            $feature_type = ProductFeatures::NUMBER_SELECTBOX;
        }

        if (self::$s_commerceml['exim_1c_used_brand'] == 'feature_product') {
            $brand_feature = trim(self::$s_commerceml['exim_1c_property_for_manufacturer']);
            if (!empty($brand_feature) && ($brand_feature == $feature_name)) {
                $feature_type = ProductFeatures::EXTENDED;
                $data['feature_type'] = $feature_type;
            }
        }

        if ($f_type == ProductFeatures::EXTENDED) {
            $feature_type = ProductFeatures::EXTENDED;
            $data['feature_type'] = $feature_type;
        }

        if (empty($feature_id)) {
            $data['position'] = 0;
            $data['parent_id'] = 0;
            $data['prefix'] = '';
            $data['suffix'] = '';
            $data['display_on_catalog'] = "Y";
            $data['display_on_product'] = "Y";
            $data['prefix'] = '';
            $data['suffix'] = '';
            $data['parent_id'] = 0;
            $data['position'] = 0;
            $data['feature_type'] = $feature_type;
        }

        return $data;
    }

    public static function displayFeatures($feature_name, $shipping_params)
    {
        foreach ($shipping_params as $s_param) {
            if (in_array($feature_name, $s_param['fields'])) {
                if ($s_param['display'] == 'Y') {
                    return true;
                } else {
                    return false;
                }
            }
        }

        return true;
    }

    public static function getShippingFeatures()
    {
        return array(
            array(
                'name' => 'weight_property',
                'fields' => fn_explode("\n", self::$s_commerceml['exim_1c_weight_property']),
                'display' => self::$s_commerceml['exim_1c_display_weight'],
            ),
            array(
                'name' => 'free_shipping',
                'fields' => fn_explode("\n", self::$s_commerceml['exim_1c_free_shipping']),
                'display' => self::$s_commerceml['exim_1c_display_free_shipping'],
            ),
            array(
                'name' => 'shipping_cost',
                'fields' => fn_explode("\n", self::$s_commerceml['exim_1c_shipping_cost']),
                'display' => '',
            ),
            array(
                'name' => 'number_of_items',
                'fields' => fn_explode("\n", self::$s_commerceml['exim_1c_number_of_items']),
                'display' => '',
            ),
            array(
                'name' => 'box_length',
                'fields' => fn_explode("\n", self::$s_commerceml['exim_1c_box_length']),
                'display' => '',
            ),
            array(
                'name' => 'box_width',
                'fields' => fn_explode("\n", self::$s_commerceml['exim_1c_box_width']),
                'display' => '',
            ),
            array(
                'name' => 'box_height',
                'fields' => fn_explode("\n", self::$s_commerceml['exim_1c_box_height']),
                'display' => '',
            ),
        );
    }

    public static function importProductsFile($data_products, $import_params)
    {
        $cml = self::$cml;
        $type_import_products = self::$s_commerceml['exim_1c_import_products'];
        $allow_import_features = self::$s_commerceml['exim_1c_allow_import_features'];
        $add_tax = self::$s_commerceml['exim_1c_add_tax'];
        $schema_version = self::$s_commerceml['exim_1c_schema_version'];
        $type_link = self::$s_commerceml['exim_1c_import_type'];

        if (!empty(self::$features_commerceml)) {
            $features_commerceml = self::$features_commerceml;
        }

        if (!empty(self::$categories_commerceml)) {
            $categories_commerceml = self::$categories_commerceml;
        } else {
            $categories_commerceml = db_get_hash_single_array("SELECT external_id, category_id FROM ?:categories WHERE external_id <> ''", array('external_id', 'category_id'));
        }

        if (!isset(\Tygh::$app['session']['exim_1c']['import_products'])) {
            $product_pos_start = 0;
        } else {
            $product_pos_start = \Tygh::$app['session']['exim_1c']['import_products'];
        }

        $offers_pos = 0;
        $progress = false;

        foreach ($data_products -> {$cml['product']} as $_product) {

            $offers_pos++;
            if ($offers_pos < $product_pos_start) {
                continue;
            }

            if ($offers_pos - $product_pos_start + 1 > COUNT_1C_IMPORT && $import_params['service_exchange'] != 'exim_class') {
                $progress = true;
                break;
            }

            if (empty($_product -> {$cml['name']})) {
                self::addMessageLog('Name is not set for product with id: ' . $_product -> {$cml['id']});
                continue;
            }

            $ids = fn_explode('#', $_product -> {$cml['id']});
            $guid_product = array_shift($ids);
            $combination_id = 0;
            if (!empty($ids)) {
                $combination_id = reset($ids);
            }

            $article = strval($_product -> {$cml['article']});
            $barcode = strval($_product -> {$cml['bar']});

            $product_data = array();
            if ($type_link == 'article') {
                $product_data = db_get_row("SELECT product_id, update_1c FROM ?:products WHERE product_code = ?s", $article);

            } elseif ($type_link == 'barcode') {
                $product_data = db_get_row("SELECT product_id, update_1c FROM ?:products WHERE product_code = ?s", $barcode);

            } else {
                $product_data = db_get_row("SELECT product_id, update_1c FROM ?:products WHERE external_id = ?s", $guid_product);
            }

            $product_update = !empty($product_data['update_1c']) ? $product_data['update_1c'] : 'Y';
            $product_id = (!empty($product_data['product_id'])) ? $product_data['product_id'] : 0;
            if (!empty($_product -> attributes() -> {$cml['status']}) && strval($_product -> attributes() -> {$cml['status']}) == 'Удален') {
                if ($product_id != 0) {
                    fn_delete_product($product_id);
                    self::addMessageLog('Deleted product: ' . strval($_product -> {$cml['name']}));
                }

                continue;
            }

            if (!empty($_product -> {$cml['status']}) && strval($_product -> {$cml['status']}) == 'Удален') {
                if ($product_id != 0) {
                    fn_delete_product($product_id);
                    self::addMessageLog('Deleted product: ' . strval($_product -> {$cml['name']}));
                }

                continue;
            }

            if ($type_import_products == 'all_products' || (($type_import_products == 'new_products' || $type_import_products == 'new_update_products') && $product_id == 0) || ($type_import_products == 'update_products' && $product_id != 0)) {
                if ($product_update == 'Y' || $product_id == 0) {
                    $product = self::dataProductFile($_product, $product_id, $guid_product, $categories_commerceml, $import_params);

                    if ($product_id == 0) {
                        self::newDataProductFile($product, $import_params);
                    }

                    if ((isset($_product -> {$cml['properties_values']} -> {$cml['property_values']}) || isset($_product -> {$cml['manufacturer']})) && ($allow_import_features == 'Y') && (!empty(self::$features_commerceml))) {
                        self::dataProductFeatures($_product, $product, $import_params);
                    }

                    if (isset($_product -> {$cml['taxes_rates']}) && ($add_tax == 'Y')) {
                        $product['tax_ids'] = self::addProductTaxes($_product -> {$cml['taxes_rates']}, $product_id, $import_params['lang_code']);
                    }

                    $product_id = fn_update_product($product, $product_id, $import_params['lang_code']);

                    self::addMessageLog('Added product: ' . $product['product'] . ' commerceml_id: ' . strval($_product -> {$cml['id']}));

                    // Import product features
                    if (!empty($product['features'])) {
                        $variants_data['product_id'] = $product_id;
                        $variants_data['lang_code'] = $import_params['lang_code'];
                        $variants_data['category_id'] = $product['category_id'];
                        self::addProductFeatures($product['features'], $variants_data, $import_params);
                    }

                    // Import images
                    $image_main = true;
                    if (isset($_product -> {$cml['image']})) {
                        foreach ($_product -> {$cml['image']} as $image) {
                            $filename = fn_basename(strval($image));
                            self::addProductImage($filename, $image_main, $product_id, $import_params);
                            $image_main = false;
                        }
                    }

                    // Import combinations
                    if (isset($_product -> {$cml['product_features']} -> {$cml['product_feature']}) && $schema_version == '2.07') {
                        self::addProductCombinationsNewSchema($_product -> {$cml['product_features']} -> {$cml['product_feature']}, $product_id, $import_params, $combination_id);
                    }
                }
            }

            if (empty($import_params['service_exchange'])) {
                fn_echo(' ');
            } else {
                fn_echo('');
            }
        }

        if ($progress) {
            if (!isset(\Tygh::$app['session']['exim_1c'])) {
                \Tygh::$app['session']['exim_1c'] = array();
            }

            \Tygh::$app['session']['exim_1c']['import_products'] = $offers_pos;
            fn_echo("progress\n");
            fn_echo('processed: ' . \Tygh::$app['session']['exim_1c']['import_products'] . "\n");

            if ($import_params['manual']) {
                fn_redirect(Registry::get('config.current_url'));
            }

        } else {
            fn_echo("success\n");
            unset(\Tygh::$app['session']['exim_1c']['import_products']);
        }
    }

    public static function dataProductFile($d_product, $product_id, $external_id, $categories_commerceml, $import_params)
    {
        $cml = self::$cml;
        $import_product_name = self::$s_commerceml['exim_1c_import_product_name'];
        $import_product_code = self::$s_commerceml['exim_1c_import_product_code'];
        $import_full_description = self::$s_commerceml['exim_1c_import_full_description'];
        $import_short_description = self::$s_commerceml['exim_1c_import_short_description'];
        $import_page_title = self::$s_commerceml['exim_1c_page_title'];
        $default_category = self::$s_commerceml['exim_1c_default_category'];
        $allow_import_categories = self::$s_commerceml['exim_1c_allow_import_categories'];

        $product = array();
        $product['external_id'] = $external_id;

        $full_name = $product_code = $html_description = '';
        foreach ($d_product -> {$cml['value_fields']} -> {$cml['value_field']} as $reckvizit) {
            if (strval($reckvizit -> {$cml['name']}) == $cml['full_name']) {
                $full_name = strval($reckvizit -> {$cml['value']});
            }
            if (strval($reckvizit -> {$cml['name']}) == $cml['code']) {
                $product_code = strval($reckvizit -> {$cml['value']});
            }
            if (strval($reckvizit -> {$cml['name']}) == $cml['html_description']) {
                $html_description = strval($reckvizit -> {$cml['value']});
            }
        }

        if (!empty($d_product -> {$cml['image']})) {
            foreach ($d_product -> {$cml['image']} as $file_description) {
                $filename = fn_basename(strval($file_description));
                if (self::checkFileDescription($filename)){
                    $html_description = @file_get_contents(self::$path_commerceml . $filename);
                }
            }
        }

        // Import product name
        $product['product'] = strval($d_product -> {$cml['name']});
        if (($import_product_name == 'full_name') && (!empty($full_name))) {
            $product['product'] = $full_name;
        }

        // Import product code
        $article = strval($d_product -> {$cml['article']});
        $product['product_code'] = !empty($article) ? $article : '';
        if ($import_product_code == 'code') {
            $product['product_code'] = $product_code;
        } elseif ($import_product_code == 'bar') {
            $product['product_code'] = strval($d_product -> {$cml['bar']});
        }

        // Import product full description
        if ($import_full_description == 'description') {
            $product['full_description'] = nl2br($d_product -> {$cml['description']});

        } elseif ($import_full_description == 'html_description') {
            $product['full_description'] = $html_description;

        } elseif ($import_full_description == 'full_name') {
            $product['full_description'] = $full_name;
        }

        // Import product short description
        if ($import_short_description == 'description') {
            $product['short_description'] = nl2br($d_product -> {$cml['description']});

        } elseif ($import_short_description == 'html_description') {
            $product['short_description'] = $html_description;

        } elseif ($import_short_description == 'full_name') {
            $product['short_description'] = $full_name;
        }

        // Import page title
        if ($import_page_title == 'name') {
            $product['page_title'] = trim($d_product -> {$cml['name']}, " -");

        } elseif ($import_page_title == 'full_name') {
            $product['page_title'] = trim($full_name, " -");
        }

        $product['company_id'] = ($import_params['user_data']['user_type'] == 'V') ? $import_params['user_data']['company_id'] : self::$company_id;

        $category_id = 0;
        if ($allow_import_categories == 'Y') {
            if (!empty($d_product -> {$cml['groups']} -> {$cml['id']})) {
                $category_id = !empty($categories_commerceml[strval($d_product -> {$cml['groups']} -> {$cml['id']})]) ? $categories_commerceml[strval($d_product -> {$cml['groups']} -> {$cml['id']})] : 0;
            }
        }

        if (!empty($product_id)) {
            $product['category_ids'] = db_get_fields("SELECT category_id FROM ?:products_categories WHERE product_id = ?i", $product_id);

            if ($category_id == 0) {
                $category_id = db_get_field("SELECT category_id FROM ?:products_categories WHERE product_id = ?i", $product_id);
            }
        }

        if ($category_id == 0) {
            if (!empty($default_category)) {
                $category_id = $default_category;
            } else {
                $category_id = self::getDefaultCategory();
            }
        }

        $product['category_id'] = $category_id;

        if ($category_id != 0) { 
            $product['category_ids'][] = $category_id;
            $product['category_ids'] = array_unique($product['category_ids']);
        }

        return $product;
    }

    public static function newDataProductFile(&$product, $import_params)
    {
        $type_import_products = self::$s_commerceml['exim_1c_import_products'];

        $product['price'] = '0.00';
        $product['list_price'] = '0.00';
        $product['lower_limit'] = 1;
        $product['details_layout'] = 'default';
        $product['lang_code'] = $import_params['lang_code'];
        $product['status'] = 'A';
        if ($type_import_products == 'new_products' || $type_import_products == 'new_update_products') {
            $product['status'] = 'N';
        }
    }

    public static function checkFileDescription($filename)
    {
        $file_array = fn_explode('.', $filename);
        if (is_array($file_array)) {
            $type = mb_strtolower(array_pop($file_array));
            if (in_array($type, array('txt', 'html'))) {
                return true;
            }
        }

        return false;
    }

    public static function getDefaultCategory()
    {
        $default_category = self::$s_commerceml['exim_1c_default_category'];
        $default_category = db_get_field("SELECT category_id FROM ?:categories WHERE category_id = ?i", $default_category);
        if (!empty($default_category)) {
            return $default_category;
        } else {
            if (empty(self::$default_category)) {
                $category_data = array(
                    'category' => 'Default category',
                    'status' => 'D',
                    'parent_id' => 0,
                    'company_id' => self::$company_id
                );
                self::$default_category = fn_update_category($category_data, 0);
                Registry::set('addons.rus_exim_1c.exim_1c_default_category', self::$default_category);
            }

            return self::$default_category;
        }
    }

    public static function addProductFeatures($data_features, $variants_data, $import_params)
    {
        foreach ($data_features as $p_feature) {
            $variant_feature = array_merge($p_feature, $variants_data);

            if (!empty($variants_data['category_id'])) {
                $feature_categories = fn_explode(',', db_get_field("SELECT categories_path FROM ?:product_features WHERE feature_id = ?i", $p_feature['feature_id']));
                if (!in_array($variants_data['category_id'], $feature_categories)) {
                    $feature_categories[] = $variants_data['category_id'];
                    $feature_categories = array_diff($feature_categories, array(''));
                    db_query("UPDATE ?:product_features SET categories_path = ?s WHERE feature_id = ?i", implode(',', $feature_categories), $p_feature['feature_id']);
                }
            }

            self::addFeatureValues($variant_feature);
        }
    }

    public static function addFeatureValues($variants_data)
    {
        if (!empty($variants_data['variant_id'])) {
            $variant_id = db_get_field("SELECT variant_id FROM ?:product_features_values WHERE feature_id = ?i AND product_id = ?i", $variants_data['feature_id'], $variants_data['product_id']);
            if (!empty($variant_id)) {
                db_query("DELETE FROM ?:product_features_values WHERE feature_id = ?i AND product_id = ?i", $variants_data['feature_id'], $variants_data['product_id']);
            }
            db_query("REPLACE INTO ?:product_features_values ?e", $variants_data);
        }
    }

    public static function dataProductFeatures($data_product, &$product, $import_params)
    {
        $property_for_promo_text = trim(self::$s_commerceml['exim_1c_property_product']);
        $cml = self::$cml;
        $features_commerceml = self::$features_commerceml;
        $product['promo_text'] = '';

        if (!empty($data_product -> {$cml['properties_values']} -> {$cml['property_values']})) {
            foreach ($data_product -> {$cml['properties_values']} -> {$cml['property_values']} as $_feature) {
                $variant_data = array();
                $feature_id = strval($_feature -> {$cml['id']});
                if ((!isset($features_commerceml[$feature_id])) || empty($_feature -> {$cml['value']})) {
                    continue;
                }

                $p_feature_name = strval($_feature -> {$cml['value']});
                $feature_name = trim($features_commerceml[$feature_id]['name'], " ");
                if (!empty($features_commerceml[$feature_id])) {
                    $product_params = self::dataShippingParams($p_feature_name, $feature_name, $import_params);
                    if (!empty($product_params)) {
                        $product = array_merge($product, $product_params);
                    }

                    if (!empty($property_for_promo_text) && ($property_for_promo_text == $feature_name)) {
                        if (!empty($features_commerceml[$feature_id]['variants'])) {
                            $product['promo_text'] = $features_commerceml[$feature_id]['variants'][$p_feature_name]['value'];
                        } else {
                            $product['promo_text'] = $p_feature_name;
                        }
                    }
                }

                if (!empty($features_commerceml[$feature_id]['id'])) {
                    $variant_data['feature_id'] = $features_commerceml[$feature_id]['id'];
                    $variant_data['feature_types'] = $features_commerceml[$feature_id]['type'];
                    $variant_data['lang_code'] = $import_params['lang_code'];

                    $d_variants = fn_get_product_feature_data($variant_data['feature_id'], true, false, $import_params['lang_code']);

                    if ($d_variants['feature_id'] == $variant_data['feature_id']) {
                        $variant_data = $d_variants;
                    }

                    if ($variant_data['feature_type'] == ProductFeatures::NUMBER_SELECTBOX) {
                        $p_feature_name = str_replace(',', '.', $p_feature_name);
                        $variant_data['value_int'] = $p_feature_name;
                    }

                    $is_id = false;
                    $variant = '';
                    if (!empty($features_commerceml[$feature_id]['variants'])) {
                        foreach ($features_commerceml[$feature_id]['variants'] as $_variant) {
                            if ($p_feature_name == $_variant['id']) {
                                $variant = $_variant['value'];
                                $is_id = true;
                                break;
                            }
                        }

                        if (!$is_id) {
                            $variant = $p_feature_name;
                        }
                    } else {
                        $variant = $p_feature_name;
                    }
                    $variant_data['variant'] = $variant;

                    list($d_variant, $params_variant) = self::checkFeatureVariant($variant_data['feature_id'], $variant_data['variant'], $import_params['lang_code']);
                    if (!empty($d_variant)) {
                        $variant_data['variant_id'] = $d_variant;
                    } else {
                        $variant_data['variant_id'] = fn_add_feature_variant($variant_data['feature_id'], array('variant' => $variant));
                    }

                    $product['features'][$feature_id] = $variant_data;
                }
            }
        }

        $variant_data = array();
        if (self::$s_commerceml['exim_1c_used_brand'] == 'field_brand') {
            if (isset($data_product -> {$cml['manufacturer']})) {
                $variant_data['feature_id'] = $features_commerceml['brand1c']['id'];
                $variant_data['lang_code'] = $import_params['lang_code'];
                $variant_id = db_get_field("SELECT variant_id FROM ?:product_feature_variants WHERE feature_id = ?i AND external_id = ?s", $variant_data['feature_id'], strval($data_product -> {$cml['manufacturer']} -> {$cml['id']}));
                $variant = strval($data_product -> {$cml['manufacturer']} -> {$cml['name']});
                if (empty($variant_id)) {
                    $variant_data['variant_id'] = fn_add_feature_variant($variant_data['feature_id'], array('variant' => $variant));
                    db_query("UPDATE ?:product_feature_variants SET external_id = ?s WHERE variant_id = ?i", strval($data_product -> {$cml['manufacturer']} -> {$cml['id']}), $variant_data['variant_id']);
                } else {
                    $variant_data['variant_id'] = $variant_id;
                }

                $product['features'][$variant_data['feature_id']] = $variant_data;
            }
        }
    }

    public static function checkFeatureVariant($feature_id, $variant, $lang_code)
    {
        $variant_exists = db_get_field(
            "SELECT ?:product_feature_variant_descriptions.variant_id"
            . " FROM ?:product_feature_variant_descriptions"
            . " LEFT JOIN ?:product_feature_variants ON ?:product_feature_variant_descriptions.variant_id = ?:product_feature_variants.variant_id"
            . " WHERE ?:product_feature_variants.feature_id = ?i AND ?:product_feature_variant_descriptions.variant = ?s AND ?:product_feature_variant_descriptions.lang_code = ?s",
            $feature_id, $variant, $lang_code
        );
        $result = (!empty($variant_exists)) ? false : true;

        return array($variant_exists, $result);
    }

    public static function dataShippingParams($p_feature_name, $features_name, $import_params)
    {
        $product_params = array();
        $shipping_params = self::getShippingFeatures();
        foreach ($shipping_params as $shipping_param) {
            if (in_array($features_name, $shipping_param['fields'])) {
                if ($shipping_param['name'] == 'weight_property') {
                    $_value = preg_replace('/,/i', '.', $p_feature_name);
                    $product_params['weight'] = (float) $_value;
                }

                if ($shipping_param['name'] == 'free_shipping') {
                    if ($p_feature_name == self::$cml['yes']) {
                        $product_params['free_shipping'] = 'Y';
                    } else {
                        $product_params['free_shipping'] = '';
                    }
                }

                if ($shipping_param['name'] == 'shipping_cost') {
                    $_value = preg_replace('/,/i', '.', $p_feature_name);
                    $product_params['shipping_freight'] = (float) $_value;
                }

                if ($shipping_param['name'] == 'number_of_items') {
                    $product_params['min_items_in_box'] = (int) $p_feature_name;
                    $product_params['max_items_in_box'] = (int) $p_feature_name;
                }

                if ($shipping_param['name'] == 'box_length') {
                    $product_params['box_length'] = (int) $p_feature_name;
                }

                if ($shipping_param['name'] == 'box_width') {
                    $product_params['box_width'] = (int) $p_feature_name;
                }

                if ($shipping_param['name'] == 'box_height') {
                    $product_params['box_height'] = (int) $p_feature_name;
                }
            }
        }

        return $product_params;
    }

    public static function addProductTaxes($product_tax, $product_id, $lang_code)
    {
        $tax_ids = array();
        $cml = self::$cml;
        if (!empty($product_id)) {
            $_tax_ids = db_get_field("SELECT tax_ids FROM ?:products WHERE product_id = ?i", $product_id);
            $tax_ids = fn_explode(',', $_tax_ids);
        }

        $product_taxes = db_get_fields("SELECT tax_id FROM ?:rus_exim_1c_taxes WHERE tax_1c = ?s", strval($product_tax -> {$cml['tax_rate']} -> {$cml['rate_t']}));
        $tax_ids = array_merge($tax_ids, $product_taxes);
        $tax_ids = array_unique($tax_ids);
        $tax_ids = array_diff($tax_ids, array('', ' ', null));

        return $tax_ids;
    }

    public static function addProductImage($filename, $image_main, $product_id, $import_params)
    {
        $url_images = self::$url_images;
        if (file_exists($url_images . mb_strtolower($filename))) {
            $filename = mb_strtolower($filename);
        }

        $all_images_is_additional = self::$s_commerceml['exim_1c_all_images_is_additional'];
        if (self::isFileProductImage($filename)) {
            $images_type = 'A';
            if ($image_main && ($all_images_is_additional != 'Y')) {
                $images_type = 'M';
                $image_main = false;
            }

            self::updateProductImage($filename, $product_id, $images_type, $import_params['lang_code']);
        }
    }

    public static function isFileProductImage($filename)
    {
        $file_array = fn_explode('.', $filename);
        if (is_array($file_array)) {
            $type = mb_strtolower(array_pop($file_array));
            if (in_array($type, array('jpg', 'jpeg', 'png', 'gif'))) {
                return true;
            }
        }

        return false;
    }

    public static function updateProductImage($filename, $product_id, $type, $lang_code)
    {
        $url_images = self::$url_images;

        if (file_exists($url_images . $filename)) {
            $detail_file = fn_explode('.', $filename);
            $type_file = array_shift($detail_file);
            $condition = db_quote(" AND images.image_path LIKE ?s", "%" . $type_file . "%");
            $images = db_get_array(
                "SELECT images.image_id, images_links.pair_id"
                . " FROM ?:images AS images"
                . " LEFT JOIN ?:images_links AS images_links ON images.image_id = images_links.detailed_id"
                . " WHERE images_links.object_id = ?i $condition", $product_id);

            if (!empty($images) && !empty($type)) {
                foreach ($images as $k_image => $image) {
                    db_query("UPDATE ?:images_links SET type = ?s WHERE pair_id = ?i", $type, $image['pair_id']);
                    $images[$k_image]['type'] = $type;
                }
            }

            $image_data[] = array(
                'name' => $filename,
                'path' => $url_images . $filename,
                'size' => filesize($url_images . $filename),
            );

            if (!empty($images)) {
                $pair_data = $images;
            } else {
                $pair_data[] = array(
                    'pair_id' => '',
                    'type' => $type,
                    'object_id' => 0
                );
            }

            $pair_ids = fn_update_image_pairs(array(), $image_data, $pair_data, $product_id, 'product', array(), 1, $lang_code);
        }
    }

    public static function addProductCombinationsNewSchema($combinations, $product_id, $import_params, $combination_id = 0, $variant_data = array())
    {
        $add_options_combination = array();
        $amount = 0;
        $cml = self::$cml;
        $import_mode = self::$s_commerceml['exim_1c_import_mode_offers'];

        $option_params = array(
            'product_id' => $product_id,
            'option_name' => self::$s_commerceml['exim_1c_import_option_name'],
            'company_id' => self::$company_id,
            'option_type' => self::$s_commerceml['exim_1c_type_option'],
            'required' => 'N',
            'inventory' => 'Y',
            'multiupload' => 'M'
        );

        $option_id = self::dataProductOption($product_id, $import_params['lang_code']);

        if (empty($combination_id)) {
            $option_data = $option_params;
            foreach ($combinations as $_combination) {
                if (!empty($option_id)) {
                    list($data_variant, $r_data_variants) = self::dataProductVariants($option_id, $import_params['lang_code'], strval($_combination -> {$cml['name']}), strval($_combination -> {$cml['id']}));
                }

                $variant_id = (!empty($data_variant['variant_id'])) ? $data_variant['variant_id'] : 0;
                if (!empty($r_data_variants)) {
                    $option_data['variants'] = $r_data_variants;
                }

                $option_data['variants'][] = array(
                    'variant_name' => strval($_combination -> {$cml['name']}),
                    'variant_id' => $variant_id,
                    'external_id' => strval($_combination -> {$cml['id']})
                );

                $option_id = fn_update_product_option($option_data, $option_id, $import_params['lang_code']);
                self::addMessageLog('Added option = ' . $option_data['option_name'] . ', variant = ' . strval($_combination -> {$cml['name']}));

                list($data_variant, $r_data_variants) = self::dataProductVariants($option_id, $import_params['lang_code'], strval($_combination -> {$cml['name']}), strval($_combination -> {$cml['id']}));
                if (!empty($data_variant['variant_id'])) {
                    $add_options_combination[$option_id] = $data_variant['variant_id'];
                    self::addNewCombination($product_id, strval($_combination -> {$cml['id']}), $add_options_combination, $import_params);
                }
            }

        } else {
            if ($import_mode == 'standart') {
                $option_data = $option_params;
                $d_variant = array(
                    'variant_name' => '',
                    'lang_code' => $import_params['lang_code'],
                    'external_id' => $combination_id
                );
                foreach ($combinations as $_combination) {
                    $d_variant['variant_name'] .= strval($_combination -> {$cml['name']}) . ': ' . strval($_combination -> {$cml['value']}) . '; ';
                }

                if (!empty($variant_data['price'])) {
                    if (self::$s_commerceml['exim_1c_option_price'] == 'N') {
                        $d_variant['modifier'] = $variant_data['price'];
                    } else {
                        $d_variant['modifier'] = '0.00';
                    }
                } 

                if (!empty($option_id)) {
                    list($data_variant, $option_data['variants']) = self::dataProductVariants($option_id, $import_params['lang_code'], $d_variant['variant_name'], $combination_id);
                }

                $d_variant['variant_id'] = (!empty($data_variant['variant_id'])) ? $data_variant['variant_id'] : 0;
                $option_data['variants'][] = $d_variant;
                $option_id = fn_update_product_option($option_data, $option_id, $import_params['lang_code']);
                self::addMessageLog('Added option = ' . $option_data['option_name'] . ', variant = ' . $d_variant['variant_name']);

                list($data_variant, $r_data_variants) = self::dataProductVariants($option_id, $import_params['lang_code'], $d_variant['variant_name'], $combination_id);
                if (!empty($data_variant['variant_id'])) {
                    $add_options_combination[$option_id] = $data_variant['variant_id'];
                }

            } elseif ($import_mode == 'global_option') {
                $option_params['product_id'] = 0;
                foreach ($combinations as $_combination) {
                    $option_data = $option_params;
                    $combination_name = strval($_combination -> {$cml['name']});
                    $combination_value = strval($_combination -> {$cml['value']});

                    $option_id = self::dataProductOption(0, $import_params['lang_code'], $combination_name);
                    $option_data['option_name'] = $combination_name;

                    if (!empty($_combination -> {$cml['id']})) {
                        $option_data['external_id'] = strval($_combination -> {$cml['id']});
                    }

                    $option_data['variants'][] = array(
                        'variant_id' => 0,
                        'variant_name' => $combination_value,
                        'lang_code' => $import_params['lang_code'],
                        'modifier_type' => 'A',
                        'modifier' => 0,
                        'weight_modifier' => 0,
                        'weight_modifier_type' => 'A'
                    );
                    if ($option_id != 0) {
                        list($data_variant, $r_data_variants) = self::dataProductVariants($option_id, $import_params['lang_code'], $combination_value);
                        if (!empty($data_variant)) {
                            $option_data['variants'] = array($data_variant);
                        }

                        if (!empty($r_data_variants)) {
                            $option_data['variants'] = array_merge($option_data['variants'], $r_data_variants);
                        }
                    }

                    $option_id = fn_update_product_option($option_data, $option_id, $import_params['lang_code']);
                    self::addMessageLog('Added option = ' . $option_data['option_name'] . ', variant = ' . $combination_value);
                    db_query("REPLACE INTO ?:product_global_option_links ?e", array(
                        'option_id' => $option_id,
                        'product_id' => $product_id
                    ));

                    list($data_variant, $r_data_variants) = self::dataProductVariants($option_id, $import_params['lang_code'], $combination_value);
                    if (!empty($data_variant['variant_id'])) {
                        $add_options_combination[$option_id] = $data_variant['variant_id'];
                    }
                }

            } elseif ($import_mode == 'individual_option') {
                foreach ($combinations as $_combination) {
                    $option_data = $option_params;
                    $combination_name = strval($_combination -> {$cml['name']});
                    $combination_value = strval($_combination -> {$cml['value']});

                    $option_id = self::dataProductOption($product_id, $import_params['lang_code'], $combination_name);
                    $option_data['option_name'] = $combination_name;

                    if (!empty($_combination -> {$cml['id']})) {
                        $option_data['external_id'] = strval($_combination -> {$cml['id']});
                    }

                    $option_data['variants'][] = array(
                        'option_id' => $option_id,
                        'variant_id' => 0,
                        'variant_name' => $combination_value,
                        'lang_code' => $import_params['lang_code'],
                        'modifier_type' => 'A',
                        'modifier' => 0,
                        'weight_modifier' => 0,
                        'weight_modifier_type' => 'A'
                    );

                    list($data_variant, $r_data_variants) = self::dataProductVariants($option_id, $import_params['lang_code'], $combination_value);

                    if (!empty($data_variant)) {
                        $option_data['variants'] = array($data_variant);
                    }

                    if (!empty($r_data_variants)) {
                        $option_data['variants'] = array_merge($option_data['variants'], $r_data_variants);
                    }

                    $option_id = fn_update_product_option($option_data, $option_id, $import_params['lang_code']);
                    self::addMessageLog('Added option = ' . $option_data['option_name'] . ', variant = ' . $combination_value);
                    list($data_variant, $r_data_variants) = self::dataProductVariants($option_id, $import_params['lang_code'], $combination_value);
                    if (!empty($data_variant)) {
                        $add_options_combination[$option_id] = $data_variant['variant_id'];
                    }
                }

            }

            if (!empty($variant_data['amount'])) {
                $amount = $variant_data['amount'];
            }

            if (!empty($add_options_combination)) {
                self::addNewCombination($product_id, $combination_id, $add_options_combination, $import_params, $amount);
            }
        }
    }

    public static function dataProductOption($product_id, $lang_code, $combination_name = "")
    {
        $option_id = 0;

        $condition = $join = '';

        $join = db_quote("LEFT JOIN ?:product_options_descriptions AS options_descriptions ON options.option_id = options_descriptions.option_id ");

        $condition = db_quote(" AND options.product_id = ?i AND options_descriptions.lang_code = ?s ", $product_id, $lang_code);

        if (!empty($combination_name)) {
            $condition .= db_quote(" AND options_descriptions.option_name = ?s", $combination_name);
        }

        $option_id = db_get_field("SELECT options.option_id FROM ?:product_options AS options $join WHERE 1 $condition ");

        return $option_id;
    }

    public static function dataProductVariants($option_id, $lang_code, $variant_name = "", $combination_id = 0)
    {
        $fields = array (
            'variants.variant_id',
            'variants.external_id',
            'variants.modifier_type',
            'variants.modifier',
            'variants.weight_modifier',
            'variants.weight_modifier_type',
            'variants_descriptions.variant_name',
        );

        $condition = $condition2 = $join = '';

        $join = db_quote("LEFT JOIN ?:product_option_variants_descriptions AS variants_descriptions ON variants.variant_id = variants_descriptions.variant_id ");

        $condition = db_quote(" AND variants.option_id = ?i AND variants_descriptions.lang_code = ?s ", $option_id, $lang_code);

        if (!empty($combination_id)) {
            $condition2 = db_quote(" AND variants.external_id = ?s", $combination_id);
        }

        $data_variant = db_get_row("SELECT " . implode(', ', $fields) . " FROM ?:product_option_variants AS variants $join WHERE variants_descriptions.variant_name = ?s $condition $condition2 ", $variant_name);
        $r_data_variants = db_get_array("SELECT " . implode(', ', $fields) . " FROM ?:product_option_variants AS variants $join WHERE variants_descriptions.variant_name <> ?s $condition ", $variant_name);

        return array($data_variant, $r_data_variants);
    }

    public static function addNewCombination($product_id, $combination_id, $add_options_combination, $import_params, $amount = 0)
    {
        $old_combination_hash = db_get_field("SELECT combination_hash FROM ?:product_options_inventory WHERE external_id = ?s", $combination_id);
        $image_pair_id = db_get_field("SELECT pair_id FROM ?:images_links WHERE object_id = ?i", $old_combination_hash);
        db_query("DELETE FROM ?:product_options_inventory WHERE external_id = ?s AND product_id = ?i", $combination_id, $product_id);

        $combination_data = array(
            'product_id' => $product_id,
            'combination_hash' => fn_generate_cart_id($product_id, array('product_options' => $add_options_combination)),
            'combination' => fn_get_options_combination($add_options_combination),
            'external_id' => $combination_id
        );

        if (isset($amount)) {
            $combination_data['amount'] = $amount;
            self::addProductOptionException($add_options_combination, $product_id, $import_params, $amount);
        }

        $variant_combination = db_get_field("SELECT combination_hash FROM ?:product_options_inventory WHERE combination_hash = ?i", $combination_data['combination_hash']);
        if (empty($variant_combination)) {
            db_query("REPLACE INTO ?:product_options_inventory ?e", $combination_data);
        } else {
            db_query("UPDATE ?:product_options_inventory SET ?u WHERE combination_hash = ?i", $combination_data, $combination_data['combination_hash']);
        }

        if (!empty($image_pair_id)) {
            db_query("UPDATE ?:images_links SET object_id = ?i WHERE pair_id = ?i", $combination_data['combination_hash'], $image_pair_id);
        }
    }

    public static function addProductOptionException($add_options_combination, $product_id, $import_params, $amount = 0)
    {
        $s_combination = serialize($add_options_combination);
        $hide_product = self::$s_commerceml['exim_1c_add_out_of_stock'];
        $exception_id = db_get_field("SELECT * FROM ?:product_options_exceptions WHERE product_id = ?i AND combination = ?s", $product_id, $s_combination);
        if (!empty($exception_id)) {
            db_query("DELETE FROM ?:product_options_exceptions WHERE exception_id = ?i", $exception_id);
        }

        if (($amount <= 0) && ($hide_product == 'Y')) {
            $combination = array(
                'product_id' => $product_id,
                'combination' => $s_combination,
            );

            db_query("REPLACE INTO ?:product_options_exceptions ?e", $combination);
        }
    }

    public static function importDataOffersFile($xml, $service_exchange, $lang_code, $manual = false)
    {
        self::addMessageLog("Started import date to file offers.xml, parameter service_exchange = " . $service_exchange);

        $cml = self::$cml;
        $import_params = array(
            'service_exchange' => $service_exchange,
            'lang_code' => $lang_code,
            'manual' => $manual
        );

        if (isset($xml -> {$cml['packages']} -> {$cml['offers']} -> {$cml['offer']})) {
            self::importProductOffersFile($xml -> {$cml['packages']}, $import_params);
        } else {
            fn_echo("success\n");
        }
    }

    public static function importProductOffersFile($data_offers, $import_params)
    {
        $cml = self::$cml;
        $create_prices = self::$s_commerceml['exim_1c_create_prices'];
        $schema_version = self::$s_commerceml['exim_1c_schema_version'];
        $import_mode = self::$s_commerceml['exim_1c_import_mode_offers'];
        $negative_amount = Registry::get('settings.General.allow_negative_amount');

        if (isset($data_offers -> {$cml['prices_types']} -> {$cml['price_type']})) {
            $price_offers = self::dataPriceOffers($data_offers -> {$cml['prices_types']} -> {$cml['price_type']});

            if ($create_prices == 'Y') {
                $data_prices = db_get_array("SELECT price_1c, type, usergroup_id FROM ?:rus_exim_1c_prices");
                $prices_commerseml = self::dataPriceFile($data_offers -> {$cml['prices_types']} -> {$cml['price_type']}, $data_prices);
            }
        }

        if (!isset(\Tygh::$app['session']['exim_1c']['import_offers'])) {
            $offer_pos_start = 0;
        } else {
            $offer_pos_start = \Tygh::$app['session']['exim_1c']['import_offers'];
        }

        $offers_pos = 0;
        $progress = false;
        $options_data = $global_options_data = array();

        foreach ($data_offers -> {$cml['offers']} -> {$cml['offer']} as $offer) {

            $offers_pos++;
            if ($offers_pos < $offer_pos_start) {
                continue;
            }

            if ($offers_pos - $offer_pos_start + 1 > COUNT_1C_IMPORT && $import_params['service_exchange'] != 'exim_class') {
                $progress = true;
                break;
            }

            $product = array();
            $amount = 0;
            $combination_id = 0;

            $ids = fn_explode('#', strval($offer -> {$cml['id']}));
            $guid_product = array_shift($ids);
            if (!empty($ids)) {
                $combination_id = reset($ids);
            }

            $product_data = db_get_row("SELECT product_id, update_1c, status FROM ?:products WHERE external_id = ?s", $guid_product);
            $product_id = !empty($product_data['product_id']) ? $product_data['product_id'] : 0;
            $update_product = !empty($product_data['update_1c']) ? $product_data['update_1c'] : 'N';

            if (!(self::checkImportPrices($product_data))) {
                continue;
            }

            if (isset($offer -> {$cml['amount']}) && !empty($offer -> {$cml['amount']})) {
                $amount = strval($offer -> {$cml['amount']});

            } elseif (isset($offer -> {$cml['store']})) {
                foreach ($offer -> {$cml['store']} as $store) {
                    $amount += strval($store[$cml['in_stock']]);
                }
            }

            $prices = array();
            if (isset($offer -> {$cml['prices']}) && !empty($price_offers)) {
                $product_prices = self::conversionProductPrices($offer -> {$cml['prices']} -> {$cml['price']}, $price_offers);

                if ($create_prices == 'Y') {
                    $prices = self::dataProductPrice($product_prices, $prices_commerseml);

                } elseif (!empty($product_prices[strval($offer -> {$cml['prices']} -> {$cml['price']} -> {$cml['price_id']})]['price'])) {
                    $prices['base_price'] = $product_prices[strval($offer -> {$cml['prices']} -> {$cml['price']} -> {$cml['price_id']})]['price'];

                } else {
                    $prices['base_price'] = 0;
                }
            }

            if (empty($prices)) {
                $prices['base_price'] = 0;
            }

            if ($amount < 0 && $negative_amount == 'N') {
                $amount = 0;
            }

            $o_amount = $amount;
            if (!empty($product_amount[$product_id])) {
                $o_amount = $o_amount + $product_amount[$product_id]['amount'];
            }
            $product_amount[$product_id]['amount'] = $o_amount;

            $product['status'] = self::updateProductStatus($product_id, $product_data['status'], $product_amount[$product_id]['amount']);
            if (empty($combination_id)) {
                $product['amount'] = $amount;
                db_query("UPDATE ?:products SET ?u WHERE product_id = ?i", $product, $product_id);

                self::addProductPrice($product_id, $prices);
                self::addMessageLog('Added product = ' . strval($offer -> {$cml['name']}) . ', price = ' . $prices['base_price'] . ' and amount = ' . $amount);
            } else {
                $product['tracking'] = 'O';
                db_query("UPDATE ?:products SET ?u WHERE product_id = ?i", $product, $product_id);

                if ($schema_version == '2.07') {
                    self::addProductPrice($product_id, array('base_price' => 0));

                    $option_id = self::dataProductOption($product_id, $import_params['lang_code']);
                    $variant_id = db_get_field("SELECT variant_id FROM ?:product_option_variants WHERE external_id = ?s AND option_id = ?i", $combination_id, $option_id);
                    if (!empty($option_id) && !empty($variant_id)) {
                        $price = $prices['base_price'];
                        if (self::$s_commerceml['exim_1c_option_price'] == 'Y') {
                            $price = '0.00';
                        }
                        db_query("UPDATE ?:product_option_variants SET modifier = ?i WHERE variant_id = ?i", $price, $variant_id);

                        $add_options_combination = array($option_id => $variant_id);
                        self::addNewCombination($product_id, $combination_id, $add_options_combination, $import_params, $amount);
                        self::addMessageLog('Added product = ' . strval($offer -> {$cml['name']}) . ', option_id = ' . $option_id . ', variant_id = ' . $variant_id . ', price = ' . $prices['base_price'] . ' and amount = ' . $amount);

                    } elseif (empty($variant_id) && $import_mode == 'global_option') {
                        $data_combination = db_get_row("SELECT combination_hash, combination FROM ?:product_options_inventory WHERE external_id = ?s AND product_id = ?i", $combination_id, $product_id);
                        $add_options_combination = fn_get_product_options_by_combination($data_combination['combination']);

                        self::addProductOptionException($add_options_combination, $product_id, $import_params, $amount);

                        if (!empty($data_combination['combination_hash'])) {
                            $image_pair_id = db_get_field("SELECT pair_id FROM ?:images_links WHERE object_id = ?i", $data_combination['combination_hash']);
                            db_query("UPDATE ?:product_options_inventory SET amount = ?i WHERE combination_hash = ?i", $amount, $data_combination['combination_hash']);

                            if (!empty($image_pair_id)) {
                                db_query("UPDATE ?:images_links SET object_id = ?i WHERE pair_id = ?i", $data_combination['combination_hash'], $image_pair_id);
                            }
                        }

                        self::addMessageLog('Added global option product = ' . strval($offer -> {$cml['name']}) . ', price = ' . $prices['base_price'] . ' and amount = ' . $amount);

                    } elseif (empty($variant_id) && ($import_mode == 'individual_option')) {
                        $data_combination = db_get_row("SELECT combination_hash, combination FROM ?:product_options_inventory WHERE external_id = ?s AND product_id = ?i", $combination_id, $product_id);
                        $add_options_combination = fn_get_product_options_by_combination($data_combination['combination']);

                        self::addProductOptionException($add_options_combination, $product_id, $import_params, $amount);

                        if (!empty($data_combination['combination_hash'])) {
                            $image_pair_id = db_get_field("SELECT pair_id FROM ?:images_links WHERE object_id = ?i", $data_combination['combination_hash']);
                            db_query("UPDATE ?:product_options_inventory SET amount = ?i WHERE combination_hash = ?i", $amount, $data_combination['combination_hash']);

                            if (!empty($image_pair_id)) {
                                db_query("UPDATE ?:images_links SET object_id = ?i WHERE pair_id = ?i", $data_combination['combination_hash'], $image_pair_id);
                            }
                        }

                        self::addMessageLog('Added individual option product = ' . strval($offer -> {$cml['name']}) . ', price = ' . $prices['base_price'] . ' and amount = ' . $amount);
                    }

                } else {
                    if (!empty($offer -> {$cml['product_features']} -> {$cml['product_feature']})) {
                        $variant_data = array(
                            'amount' => $amount
                        );
                        if ($import_mode == 'standart') {
                            self::addProductPrice($product_id, array('base_price' => 0));
                            $variant_data['price'] = $prices['base_price'];
                        }

                        if (!empty($product_amount[$product_id][$combination_id])) {
                            $amount = $amount + $product_amount[$product_id]['amount'];
                        }
                        $product_amount[$product_id]['amount'] = $amount;

                        self::addProductCombinationsNewSchema($offer -> {$cml['product_features']} -> {$cml['product_feature']}, $product_id, $import_params, $combination_id, $variant_data);

                        self::addMessageLog('Added option product = ' . strval($offer -> {$cml['name']}) . ', price = ' . $prices['base_price'] . ' and amount = ' . $amount);
                    }
                }

                if (self::$s_commerceml['exim_1c_option_price'] == 'Y') {
                    self::addProductPrice($product_id, $prices);
                }
            }

            if (empty($import_params['service_exchange'])) {
                fn_echo(' ');
            } else {
                fn_echo('');
            }
        }

        if ($progress) {
            if (!isset(\Tygh::$app['session']['exim_1c'])) {
                \Tygh::$app['session']['exim_1c'] = array();
            }

            \Tygh::$app['session']['exim_1c']['import_offers'] = $offers_pos;
            fn_echo("progress\n");
            fn_echo('processed: ' . \Tygh::$app['session']['exim_1c']['import_offers'] . "\n");

            if ($import_params['manual']) {
                fn_redirect(Registry::get('config.current_url'));
            }

        } else {
            fn_echo("success\n");
            unset(\Tygh::$app['session']['exim_1c']['import_offers']);
        }
    }

    public static function dataPriceFile($prices_file, &$data_prices)
    {
        $cml = self::$cml;
        $prices_commerseml = array();
        foreach ($prices_file as $_price) {
            foreach ($data_prices as &$d_price) {
                if ($d_price['price_1c'] == strval($_price -> {$cml['name']})) {
                    $d_price['external_id'] = strval($_price -> {$cml['id']});
                    $prices_commerseml[] = $d_price;
                    $d_price['valid'] = true;
                }
            }
        }

        return $prices_commerseml;
    }

    public static function checkImportPrices($product_data)
    {
        $import_prices = true;
        $type_import_products = self::$s_commerceml['exim_1c_import_products'];

        if (empty($product_data['product_id']) || ($product_data['product_id'] == 0)) {
            $import_prices = false;
        }

        if (!empty($product_data['update_1c']) && ($product_data['update_1c'] == 'N')) {
            $import_prices = false;
        }

        if ($type_import_products == 'new_products' && !empty($product_data['status']) && ($product_data['status'] != 'N')) {
            $import_prices = false;
        }

        return $import_prices;
    }

    public static function dataProductPrice($product_prices, $prices_commerseml)
    {
        $cml = self::$cml;
        $prices = array();
        $list_prices = array();
        foreach ($product_prices as $external_id => $p_price) {
            foreach ($prices_commerseml as $p_commerseml) {
                if (!empty($p_commerseml['external_id'])) {
                    if ($external_id == $p_commerseml['external_id']) {
                        if ($p_commerseml['type'] == 'base') {
                            $prices['base_price'] = $p_price['price'];
                        }

                        if (($p_commerseml['type'] == 'list')) {
                            $prices['list_price'] = $p_price['price'];
                            $list_prices[] = $p_price['price'];
                        }

                        if ($p_commerseml['usergroup_id'] > 0) {
                            $prices['qty_prices'][] = array(
                                'price' => $p_price['price'],
                                'usergroup_id' => $p_commerseml['usergroup_id']
                            );
                        }
                    }
                }
            }
        }

        if ($prices['list_price'] < $prices['base_price']) {
            $prices['list_price'] = 0;

            foreach ($list_prices as $list_price) {
                if ($list_price >= $prices['base_price']) {
                    $prices['list_price'] = $list_price;
                }
            }
        }

        return $prices;
    }

    public static function updateProductStatus($product_id, $product_status, $amount)
    {
        $new_status = $product_status;
        $hide_product = self::$s_commerceml['exim_1c_add_out_of_stock'];
        if ($hide_product == 'Y') {
            if (($product_status == 'N' || $product_status == 'H') && $amount > 0) {
                $new_status = 'A';

            } elseif (($product_status == 'N') && ($amount <= 0)) {
                $new_status = 'H';
            }
        } else {
            if ($product_status == 'N') {
                $new_status = 'A';
            }
        }

        db_query("UPDATE ?:products SET status = ?s WHERE product_id = ?i", $product_status, $product_id);

        return $new_status;
    }

    public static function addProductPrice($product_id, $prices)
    {
        if (isset($prices['base_price'])) {
            $price = array(
                'product_id' => $product_id,
                'price' => $prices['base_price'],
                'lower_limit' => 1,
                'usergroup_id' => 0
            );
            db_query("DELETE FROM ?:product_prices WHERE lower_limit = 1 AND usergroup_id = 0 AND product_id = ?i", $product_id);
            $data_price = db_get_field("SELECT * FROM ?:product_prices WHERE lower_limit = 1 AND usergroup_id = 0 AND price = ?d AND product_id = ?i", $prices['base_price'], $product_id);
            if (empty($data_price)) {
                db_query("REPLACE INTO ?:product_prices ?e", $price);
            }
        }

        if (isset($prices['list_price'])) {
            db_query("UPDATE ?:products SET list_price = ?i WHERE product_id = ?i", $prices['list_price'], $product_id);
        }

        if (isset($prices['qty_prices'])) {
            foreach ($prices['qty_prices'] as $_price) {
                $price = array(
                    'product_id' => $product_id,
                    'price' => $_price['price'],
                    'lower_limit' => 1,
                    'usergroup_id' => $_price['usergroup_id'],
                );
                db_query("DELETE FROM ?:product_prices WHERE lower_limit = 1 AND usergroup_id = ?i AND product_id = ?i", $_price['usergroup_id'], $product_id);
                db_query("REPLACE INTO ?:product_prices ?e", $price);
            }

            $data_price = db_get_field("SELECT * FROM ?:product_prices WHERE lower_limit = 1 AND usergroup_id = 0 AND product_id = ?i", $product_id);
            if (empty($data_price)) {
                $price = array(
                    'product_id' => $product_id,
                    'price' => '0.00',
                    'lower_limit' => 1,
                    'usergroup_id' => 0
                );

                db_query("REPLACE INTO ?:product_prices ?e", $price);
            }
        }
    }

    public static function exportDataOrders($lang_code)
    {
        $params = array(
            'company_id' => self::$company_id,
            'company_name' => true,
            'place' => 'exim_1c',
        );

        $statuses = self::$s_commerceml['exim_1c_order_statuses'];
        if (!empty($statuses)) {
            foreach($statuses as $key => $status) {
                if (!empty($status)) {
                    $params['status'][] = $key;
                }
            }
        }

        list($orders, $search) = fn_get_orders($params);
        header("Content-type: text/xml; charset=utf-8");
        fn_echo("\xEF\xBB\xBF");
        $xml = new \XMLWriter();
        $xml -> openMemory();
        $xml -> startDocument();
        $xml -> startElement(self::$cml['commerce_information']);
        foreach ($orders as $k => $data) {
            $order_data = fn_get_order_info($data['order_id']);
            $xml = self::dataOrderToFile($xml, $order_data, $lang_code);
        }
        $xml -> endElement();
        fn_echo($xml -> outputMemory());
    }

    public static function exportAllProductsToOrders($user_data, $lang_code)
    {
        $params = array(
            'company_id' => self::$company_id,
            'company_name' => true
        );

        $statuses = self::$s_commerceml['exim_1c_order_statuses'];
        $params['status'] = reset($statuses);

        header("Content-type: text/xml; charset=utf-8");
        fn_echo("\xEF\xBB\xBF");
        $xml = new \XMLWriter();
        $xml -> openMemory();
        $xml -> startDocument();
        $xml -> startElement(self::$cml['commerce_information']);

        $payment_id = db_get_field("SELECT payment_id FROM ?:payments");
        $payment_data = fn_get_payment_method_data($payment_id);

        $shipping_id = db_get_field("SELECT shipping_id FROM ?:shippings");
        $shipping_data[] = fn_get_shipping_info($shipping_id, $lang_code);

        $info_taxes = fn_get_taxes($lang_code);
        $d_taxes = fn_calculate_tax_rates($info_taxes, 1, 0, array(), \Tygh::$app['session']['cart']);

        $company_data = fn_get_company_data($params['company_id'], $lang_code);
        $order_data = array(
            'order_id' => $company_data['company_id'] . '_' . $company_data['company'],
            'secondary_currency' => CART_PRIMARY_CURRENCY,
            'notes' => '',
            'status' => $params['status'],
            'payment_method' => $payment_data,
            'shipping' => $shipping_data,
            'fields' => array(),
            'shipping_cost' => 0
        );
        $order_data = array_merge($order_data, $user_data);
        $order_data['company'] = $company_data['company'];
        $order_data['timestamp'] = TIME;

        $product_params = array(
            'filter_params' => array(
                'update_1c' => 'Y'
            )
        );
        list($data_products, $product_params) = fn_get_products($product_params);

        $total = 0;
        foreach ($data_products as $product_id => $data_product) {
            $total += $data_product['price'];
            $data_products[$product_id]['amount'] = 1;
            $data_products[$product_id]['subtotal'] = $data_product['price'];
            $data_products[$product_id]['base_price'] = $data_product['price'];
            $data_products[$product_id]['item_id'] = $product_id;

            if (!empty($data_product['tax_ids'])) {
                $p_taxes = explode(',', $data_product['tax_ids']);

                foreach ($p_taxes as $tax_id) {
                    if (!empty($d_taxes[$tax_id])) {
                        $d_taxes[$tax_id]['applies']['items']['P'][$product_id] = 1;
                    }
                }
            }
        }

        $order_data['total'] = $total;
        $order_data['taxes'] = $d_taxes;
        $order_data['products'] = $data_products;

        $xml = self::dataOrderToFile($xml, $order_data, $lang_code);

        $xml -> endElement();
        fn_echo($xml -> outputMemory());
    }

    public static function dataOrderToFile($xml, $order_data, $lang_code)
    {
        $export_statuses = self::$s_commerceml['exim_1c_export_statuses'];
        $cml = self::$cml;

        $order_xml = array(
            $cml['id'] => $order_data['order_id'],
            $cml['number'] => $order_data['order_id'],
            $cml['date'] => date('Y-m-d', $order_data['timestamp']),
            $cml['time'] => date('H:i:s', $order_data['timestamp']),
            $cml['operation'] => $cml['order'],
            $cml['role'] => $cml['seller'],
            $cml['rate'] => 1,
            $cml['total'] => $order_data['total']
        );
        $order_xml[$cml['currency']] = (!empty($order_data['secondary_currency'])) ? $order_data['secondary_currency'] : CART_PRIMARY_CURRENCY;
        $order_xml[$cml['notes']] = $order_data['notes'];

        $data_status = fn_get_statuses('O', $order_data['status']);
        $status = (!empty($data_status)) ? $data_status[$order_data['status']]['description'] : $order_data['status'];
        $payment = (!empty($order_data['payment_method']['payment'])) ? $order_data['payment_method']['payment'] : "-";
        $shipping = (!empty($order_data['shipping'][0]['shipping'])) ? $order_data['shipping'][0]['shipping'] : "-";

        $order_xml[$cml['contractors']][$cml['contractor']] = self::dataOrderUser($xml, $order_data);

        if (!empty($order_data['subtotal_discount']) && ($order_data['subtotal_discount'] > 0) && ($order_data['subtotal_discount'] < $order_data['subtotal'])) {
            $rate_discounts = $order_data['subtotal_discount'] * 100 / $order_data['subtotal'];
            $order_xml[$cml['discounts']][$cml['discount']] = array(
                $cml['name'] => $cml['orders_discount'],
                $cml['total'] => $order_data['subtotal_discount'],
                $cml['rate_discounts'] => $rate_discounts,
                $cml['in_total'] => 'true'
            );
        }

        $order_xml[$cml['products']] = self::dataOrderProducts($xml, $order_data);

        if ($export_statuses == 'Y') {
            $order_xml[$cml['value_fields']][][$cml['value_field']] = array(
                $cml['name'] => $cml['status_order'],
                $cml['value'] => $status
            );
        }

        $order_xml[$cml['value_fields']][][$cml['value_field']] = array(
            $cml['name'] => $cml['payment'],
            $cml['value'] => $payment
        );

        $order_xml[$cml['value_fields']][][$cml['value_field']] = array(
            $cml['name'] => $cml['shipping'],
            $cml['value'] => $shipping
        );

        $xml = self::parseArrayToXml($xml, array($cml['document'] => $order_xml));

        return $xml;
    }

    public static function dataOrderUser($xml, $order_data)
    {
        $cml = self::$cml;
        $user_id = '0' . $order_data['order_id'];
        $unregistered = $cml['yes'];
        if (!empty($order_data['user_id'])) {
            $user_id = $order_data['user_id'];
            $unregistered = $cml['no'];
        }

        $name_company = (!empty($order_data['company'])) ? $order_data['company'] : $order_data['lastname'] . ' ' . $order_data['firstname'];

        $zipcode = $country = $city = "-";
        if (!empty($order_data['b_zipcode'])) {
            $zipcode = $order_data['b_zipcode'];

        } elseif (!empty($order_data['s_zipcode'])) {
            $zipcode = $order_data['s_zipcode'];
        }

        if (!empty($order_data['b_country_descr'])) {
            $country = $order_data['b_country_descr'];

        } elseif (!empty($order_data['s_country_descr'])) {
            $country = $order_data['s_country_descr'];
        }

        if (!empty($order_data['b_city'])) {
            $b_city = trim($order_data['b_city']);
            if (!empty($b_city)) {
                $city = $b_city;
            }

        } elseif (!empty($order_data['s_city'])) {
            $s_city = trim($order_data['s_city']);
            if (!empty($s_city)) {
                $city = $s_city;
            }
        }

        $address1 = $address2 = "-";
        if (!empty($order_data['b_address'])) {
            $address1 = $order_data['b_address'];

        } elseif (!empty($order_data['s_address'])) {
            $address1 = $order_data['s_address'];

        }

        if (!empty($order_data['b_address_2'])) {
            $address2 = $order_data['b_address_2'];

        } elseif (!empty($order_data['s_address_2'])) {
            $address2 = $order_data['s_address_2'];
        }

        $user_xml = array(
            $cml['id'] => $user_id,
            $cml['unregistered'] => $unregistered,
            $cml['name'] => $name_company,
            $cml['role'] => $cml['seller'],
            $cml['full_name_contractor'] => $order_data['lastname'] . ' ' . $order_data['firstname'],
            $cml['lastname'] => $order_data['lastname'],
            $cml['firstname'] => $order_data['firstname']
        );

        if (!empty($order_data['fields'])) {
            $fields_export = self::exportFieldsToFile($order_data['fields']);
        }

        if (!empty($fields_export)) {
            foreach ($fields_export as $field_export) {
                $user_xml[$field_export['description']] = $field_export['value'];
            }
        }

        $user_xml[$cml['address']][$cml['presentation']] = "$zipcode, $country, $city, $address1 $address2";
        $user_xml[$cml['address']][][$cml['address_field']] = array(
            $cml['type'] => $cml['post_code'],
            $cml['value'] => $zipcode
        );
        $user_xml[$cml['address']][][$cml['address_field']] = array(
            $cml['type'] => $cml['country'],
            $cml['value'] => $country
        );
        $user_xml[$cml['address']][][$cml['address_field']] = array(
            $cml['type'] => $cml['city'],
            $cml['value'] => $city
        );
        $user_xml[$cml['address']][][$cml['address_field']] = array(
            $cml['type'] => $cml['address'],
            $cml['value'] => "$address1 $address2"
        );

        $phone = (!empty($order_data['phone'])) ? $order_data['phone'] : '-';
        $user_xml[$cml['contacts']][][$cml['contact']] = array(
            $cml['type'] => $cml['mail'],
            $cml['value'] => $order_data['email']
        );
        $user_xml[$cml['contacts']][][$cml['contact']] = array(
            $cml['type'] => $cml['work_phone'],
            $cml['value'] => $phone
        );

        return $user_xml;
    }

    public static function dataOrderProducts($xml, $order_data)
    {
        $cml = self::$cml;
        $export_options = self::$s_commerceml['exim_1c_product_options'];

        $add_tax = self::$s_commerceml['exim_1c_add_tax'];
        if (!empty($order_data['taxes']) && $add_tax == 'Y') {
            $data_taxes = self::dataOrderTaxs($order_data['taxes']);
        }

        if (self::$s_commerceml['exim_1c_order_shipping'] == 'Y' && $order_data['shipping_cost'] > 0) {
            $data_product = array(
                $cml['id'] => 'ORDER_DELIVERY',
                $cml['name'] => $cml['delivery_order'],
                $cml['price_per_item'] => $order_data['shipping_cost'],
                $cml['amount'] => 1,
                $cml['total'] => $order_data['shipping_cost'],
                $cml['multiply'] => 1,
            );
            $data_product[$cml['base_unit']]['attribute'] = array(
                $cml['code'] => '796',
                $cml['full_name_unit'] => $cml['item'],
                'text' => $cml['item']
            );
            $data_product[$cml['value_fields']][][$cml['value_field']] = array(
                $cml['name'] => $cml['spec_nomenclature'],
                $cml['value'] => $cml['service']
            );
            $data_product[$cml['value_fields']][][$cml['value_field']] = array(
                $cml['name'] => $cml['type_nomenclature'],
                $cml['value'] => $cml['service']
            );

            $data_products[][$cml['product']] = $data_product;
        }

        $discount = 0;
        if (!empty($order_data['subtotal_discount']) && $order_data['subtotal_discount'] > 0) {
            $discount = $order_data['subtotal_discount'] * 100 / $order_data['subtotal'];
        }

        foreach ($order_data['products'] as $product) {
            $product_discount = 0;
            $product_subtotal = $product['subtotal'];
            $external_id = db_get_field("SELECT external_id FROM ?:products WHERE product_id = ?i", $product['product_id']);
            $external_id = (!empty($external_id)) ? $external_id : $product['product_id'];
            $product_name = $product['product'];
            if (!empty($product['product_options']) && $export_options == 'Y') {
                $combinations = '';
                $name_combinations = array();
                foreach ($product['product_options'] as $option_value) {
                    $combinations[$option_value['option_id']] = $option_value['option_id'] . '_' . $option_value['value'];
                    $name_combinations[$option_value['option_id']] = $option_value['option_name'] . ': ' . $option_value['variant_name'];
                }
                ksort($combinations);
                ksort($name_combinations);
                $combinations = implode('_', $combinations);
                $name_combination = implode('; ', $name_combinations);
                $options_inventory = db_get_row("SELECT external_id, combination_hash FROM ?:product_options_inventory WHERE product_id = ?i AND combination = ?s", $product['product_id'], $combinations);
                if (!empty($options_inventory['external_id'])) {
                    $external_id = $external_id . '#' . $options_inventory['external_id'];
                    $product_name = $product_name . '#' . $name_combination;

                } elseif (!empty($options_inventory['combination_hash'])) {
                    $external_id = $external_id . '#' . $options_inventory['combination_hash'];
                    $product_name = $product_name . '#' . $name_combination;
                }
            }
            $data_product = array(
                $cml['id'] => $external_id,
                $cml['code'] => $product['product_id'],
                $cml['article'] => $product['product_code'],
                $cml['name'] => $product_name,
                $cml['price_per_item'] => $product['base_price'],
                $cml['amount'] => $product['amount'],
                $cml['multiply'] => 1
            );
            $data_product[$cml['base_unit']]['attribute'] = array(
                $cml['code'] => '796',
                $cml['full_name_unit'] => $cml['item'],
                'text' => $cml['item']
            );

            if (!empty($discount)) {
                $product_discount = $product['discount'] + ($product['subtotal'] * $discount / 100);
                if ($product['subtotal'] > $product_discount) {
                    $data_product[$cml['discounts']][][$cml['discount']] = array(
                        $cml['name'] => $cml['product_discount'],
                        $cml['total'] => $product_discount,
                        $cml['in_total'] => 'false'
                    );
                }
            } elseif(isset($product['discount'])) {
                $data_product[$cml['discounts']][][$cml['discount']] = array(
                    $cml['name'] => $cml['product_discount'],
                    $cml['total'] => $product['discount'],
                    $cml['in_total'] => 'true'
                );
            }

            if (!empty($data_taxes['products'][$product['item_id']])) {
                $tax_value = 0;
                $subtotal = $product['subtotal'] - $product_discount;
                foreach ($data_taxes['products'][$product['item_id']] as $product_tax) {
                    $data_product[$cml['taxes_rates']][][$cml['tax_rate']] = array(
                        $cml['name'] => $product_tax['name'],
                        $cml['rate_t'] => $product_tax['value']
                    );

                    if ($product_tax['tax_in_total'] == 'false') {
                        $tax_value = $tax_value + ($subtotal * $product_tax['rate_value'] / 100);
                    }
                }

                $product_subtotal = $product['subtotal'] + $tax_value;
            }
            $data_product[$cml['total']] = $product_subtotal;
            $data_product[$cml['value_fields']][][$cml['value_field']] = array(
                $cml['name'] => $cml['spec_nomenclature'],
                $cml['value'] => $cml['product']
            );
            $data_product[$cml['value_fields']][][$cml['value_field']] = array(
                $cml['name'] => $cml['type_nomenclature'],
                $cml['value'] => $cml['product']
            );

            $data_products[][$cml['product']] = $data_product;
        }

        return $data_products;
    }

    public static function parseArrayToXml($xml, $data_xml)
    {
        if (!empty($data_xml) && is_array($data_xml)) {
            foreach ($data_xml as $name_tag => $data_tag) {
                if (is_numeric($name_tag)) {
                    self::parseArrayToXml($xml, $data_tag);

                } elseif ($name_tag == 'attribute') {
                    foreach ($data_tag as $k_attribute => $v_attribute) {
                        if ($k_attribute == 'text') {
                            $xml->text($v_attribute);
                        } else {
                            $xml->writeAttribute($k_attribute, $v_attribute);
                        }
                    }

                } else {
                    if (is_array($data_tag)) {
                        $xml -> startElement($name_tag);
                        self::parseArrayToXml($xml, $data_tag);
                        $xml -> endElement();
                    } else {
                        $xml -> writeElement($name_tag, $data_tag);
                    }
                }
            }
        }

        return $xml;
    }

    public static function importFileOrders($xml, $lang_code)
    {
        $cml = self::$cml;
        if (isset($xml->{$cml['document']})) {
            $orders_data = $xml->{$cml['document']};

            $statuses = array();
            $data_status = fn_get_statuses('O');
            if (!empty($data_status)) {
                foreach ($data_status as $status) {
                    $statuses[$status['description']] = array(
                        'status' => $status['status'],
                        'description' => $status['description']
                    );
                }
            }

            foreach ($orders_data as $order_data) {
                $order_id = strval($order_data->{$cml['id']});
                foreach ($order_data->{$cml['value_fields']}->{$cml['value_field']} as $data_field) {
                    if (!empty($order_id) && ($data_field->{$cml['name']} == 'Статус заказа') && (!empty($statuses[strval($data_field->{$cml['value']})]))) {
                        db_query("UPDATE ?:orders SET status = ?s WHERE order_id = ?i", $statuses[strval($data_field->{$cml['value']})]['status'], $order_id);
                    }
                }
            }
        }
    }

    public static function exportFieldsToFile($fields_orders)
    {
        $export_fields = array();
        foreach ($fields_orders as $field_id => $field_value) {
            if (!empty($field_value)) {
                $profile_field = fn_get_profile_fields('ALL', array(), CART_LANGUAGE, array('field_id' => $field_id));

                if (!empty($profile_field['checkout_export_1c']) && $profile_field['checkout_export_1c'] == 'Y') {
                    $export_fields[$profile_field['description']]['description'] = $profile_field['description'];
                    $export_fields[$profile_field['description']]['value'] = $field_value;
                }
            }
        }

        return $export_fields;
    }

    public static function dataOrderTaxs($taxes)
    {
        $data_taxes = array();
        $products_tax = array();
        $commerceml_tax = db_get_hash_array("SELECT * FROM ?:rus_exim_1c_taxes", 'tax_id');
        if (!empty($taxes)) {
            foreach ($taxes as $k_tax => $tax) {
                $tax_in_total = ($tax['price_includes_tax'] == 'Y') ? 'true' : 'false';
                $tax_value = (!empty($commerceml_tax[$k_tax])) ? $commerceml_tax[$k_tax]['tax_1c'] : $tax['rate_value'];
                $order_tax = array(
                    'name' => $tax['description'],
                    'value' => $tax_value,
                    'tax_in_total' => $tax_in_total,
                    'rate_value' => $tax['rate_value']
                );

                if (!empty($tax['applies']['items']['P'])) {
                    foreach ($tax['applies']['items']['P'] as $product_item => $product) {
                        $products_tax[$product_item][$k_tax] = $order_tax;
                    }

                    $data_taxes['products'] = $products_tax;
                }

                $data_taxes['orders'][$k_tax] = $order_tax;
            }
        }

        return $data_taxes;
    }

    public static function checkPricesOffers()
    {
        $cml = self::$cml;
        $data_prices = db_get_array("SELECT price_1c, usergroup_id, type FROM ?:rus_exim_1c_prices");
        if (isset($xml->{$cml['prices_types']}->{$cml['price_type']})) {
            $data_prices = self::dataPriceFile($data_offers -> {$cml['prices_types']} -> {$cml['price_type']}, $data_prices);
        }

        return $data_prices;
    }

    public static function dataProductCurrencies()
    {
        $data_currencies = array();
        $product_currencies = db_get_array("SELECT * FROM ?:rus_commerceml_currencies");
        $currencies = Registry::get('currencies');

        foreach ($product_currencies as $product_currency) {
            foreach ($currencies as $currency) {
                if ($product_currency['currency_id'] == $currency['currency_id']) {
                    $data_currencies[$product_currency['commerceml_currency']]['coefficient'] = $currency['coefficient'];
                }
            }
        }

        return $data_currencies;
    }

    public static function dataPriceOffers($prices)
    {
        $cml = self::$cml;
        $price_offers = array();
        $data_currencies = self::dataProductCurrencies();

        foreach ($prices as $price) {
            $price_offers[strval($price->{$cml['price_id']})] = array(
                'currency_id' => strval($price -> {$cml['price_id']}),
                'currency' => strval($price -> {$cml['currency']})
            );

            if (!empty($data_currencies[strval($price -> {$cml['currency']})])) {
                $price_offers[strval($price->{$cml['id']})]['coefficient'] = $data_currencies[strval($price -> {$cml['currency']})]['coefficient'];
            } else {
                $price_offers[strval($price->{$cml['id']})]['coefficient'] = 1;
            }
        }

        return $price_offers;
    }

    public static function conversionProductPrices($p_prices, $price_offers)
    {
        $cml = self::$cml;
        $product_prices = array();

        if (!empty($p_prices) && !empty($price_offers)) {
            foreach ($p_prices as $p_price) {
                $price = strval($p_price -> {$cml['price_per_item']});
                if (!empty($price_offers[strval($p_price -> {$cml['price_id']})]['coefficient'])) {
                    $price = $price * $price_offers[strval($p_price -> {$cml['price_id']})]['coefficient'];
                }

                $product_prices[strval($p_price -> {$cml['price_id']})] = array(
                    'price' => $price
                );
            }
        }

        return $product_prices;
    }
}
