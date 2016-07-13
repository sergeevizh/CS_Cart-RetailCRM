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

if (!defined('BOOTSTRAP')) { die('Access denied'); }

/*
 * PHP options
 */

// Log everything, but do not display
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Set maximum memory limit
if (PHP_INT_SIZE == 4 && (substr(ini_get('memory_limit'), 0, -1) < "64")) {
  // 32bit PHP
  @ini_set('memory_limit', '64M');
} elseif (PHP_INT_SIZE == 8 && (substr(ini_get('memory_limit'), 0, -1) < "256")) {
  // 64bit PHP
  @ini_set('memory_limit', '256M');
}

if (!defined('CONSOLE')) {
    // Set maximum time limit for script execution.
    @set_time_limit(3600);
}

/*
 * Database connection options
 */
$config['db_host'] = 'localhost';
$config['db_name'] = 's3';
$config['db_user'] = 'root';
$config['db_password'] = '123';

$config['database_backend'] = 'mysqli';

// Database tables prefix
$config['table_prefix'] = '';

/*
 * Script location options
 *
 *	Example:
 *	Your url is http://www.yourcompany.com/store/cart
 *	$config['http_host'] = 'www.yourcompany.com';
 *	$config['http_path'] = '/store/cart';
 *
 *	Your secure url is https://secure.yourcompany.com/secure_dir/cart
 *	$config['https_host'] = 'secure.yourcompany.com';
 *	$config['https_path'] = '/secure_dir/cart';
 *
 */

// Host and directory where software is installed on no-secure server
$config['http_host'] = 'local.s3.prefest.ru';
$config['http_path'] = '';

// Host and directory where software is installed on secure server
$config['https_host'] = 'local.s3.prefest.ru';
$config['https_path'] = '';

/*
 * Misc options
 */
// Names of index files for the frontend and backend
$config['customer_index'] = 'index.php';
$config['admin_index']    = 'admin.php';


// DEMO mode
$config['demo_mode'] = false;

// Tweaks
$config['tweaks'] = array (
    // Whether to remove any javascript code from description and name of product, category, etc.
    // Auto - false for ULT, true for MVE.
    'sanitize_user_html' => 'auto',
    'anti_csrf' => true, // protect forms from CSRF attacks
    'disable_block_cache' => false, // used to disable block cache
    'disable_localizations' => true, // Disable Localizations functionality
    'disable_dhtml' => false, // Disable Ajax-based pagination and Ajax-based "Add to cart" button
    'dev_js' => false, // set to true to disable js files compilation
    'redirect_to_cart' => false, // Redirect customer to the cart contents page. Used with the "disable_dhtml" setting.
    'api_https_only' => false, // Allows the use the API functionality only by the HTTPS protocol
    'api_allow_customer' => false, // Allow open API for unauthorized customers
    'lazy_thumbnails' => false, // generate image thumbnails on the fly
    'image_resize_lib' => 'auto', // library to resize images - "auto", "gd" or "imagick"
    'products_found_rows_no_cache_limit' => 100, // Max count of SQL found rows without saving to cache
);

// Key for sensitive data encryption
$config['crypt_key'] = 'xRcTfbzKaF';

// Cache backend
// Available backends: file, sqlite, database, redis, xcache, apc, apcu
// To use sqlite cache the "sqlite3" PHP module should be installed
// To use xcache cache the "xcache" PHP module should be installed
// To use apc cache the "apc" PHP module should be installed
// To use apcu cache the PHP version should be >= 7.x and the "apcu" PHP module should be installed
$config['cache_backend'] = 'file';
$config['cache_redis_server'] = 'localhost';
$config['cache_redis_global_ttl'] = 0; // set this if your cache size reaches Redis server memory size

// Storage backend for sessions. Available backends: database, redis
$config['session_backend'] = 'database';
$config['session_redis_server'] = 'localhost';
$config['cache_apc_global_ttl'] = 0;
$config['cache_xcache_global_ttl'] = 0;

// Set to unique store prefix if you use the same Redis/Xcache/Apc storage
// for serveral cart installations
$config['store_prefix'] = '';

// CDN server backend
$config['cdn_backend'] = 'cloudfront';

// Storage options
$config['storage'] = array(
    'images' => array(
        'prefix' => 'images',
        'dir' => $config['dir']['root'],
        'cdn' => true
    ),
    'downloads' => array(
        'prefix' => 'downloads',
        'secured' => true,
        'dir' => $config['dir']['var']
    ),
    'assets' => array(
        'dir' => & $config['dir']['cache_misc'],
        'prefix' => 'assets',
        'cdn' => true
    ),
    'custom_files' => array(
        'dir' => & $config['dir']['var'],
        'prefix' => 'custom_files'
    )
);

// Default permissions for newly created files and directories
define('DEFAULT_FILE_PERMISSIONS', 0666);
define('DEFAULT_DIR_PERMISSIONS', 0777);

// Maximum number of files, stored in directory. You may change this parameter straight after a store was installed. And you must not change it when the store has been populated with products already.
define('MAX_FILES_IN_DIR', 1000);

// Developer configuration file
if (file_exists(DIR_ROOT . '/local_conf.php')) {
    include_once(DIR_ROOT . '/local_conf.php');
}

// Enable DEV mode if Product status is not empty (like Beta1, dev, etc.)
if (PRODUCT_STATUS != '' && !defined('DEVELOPMENT')) {
    ini_set('display_errors', 'on');
    ini_set('display_startup_errors', true);

    define('DEVELOPMENT', true);
}
