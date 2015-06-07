<?php
/* vim: set ts=4 sw=4 sts=4 et: */
/*****************************************************************************\
+-----------------------------------------------------------------------------+
| X-Cart Software license agreement                                           |
| Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>            |
| All rights reserved.                                                        |
+-----------------------------------------------------------------------------+
| PLEASE READ  THE FULL TEXT OF SOFTWARE LICENSE AGREEMENT IN THE "COPYRIGHT" |
| FILE PROVIDED WITH THIS DISTRIBUTION. THE AGREEMENT TEXT IS ALSO AVAILABLE  |
| AT THE FOLLOWING URL: http://www.x-cart.com/license.php                     |
|                                                                             |
| THIS AGREEMENT EXPRESSES THE TERMS AND CONDITIONS ON WHICH YOU MAY USE THIS |
| SOFTWARE PROGRAM AND ASSOCIATED DOCUMENTATION THAT QUALITEAM SOFTWARE LTD   |
| (hereinafter referred to as "THE AUTHOR") OF REPUBLIC OF CYPRUS IS          |
| FURNISHING OR MAKING AVAILABLE TO YOU WITH THIS AGREEMENT (COLLECTIVELY,    |
| THE "SOFTWARE"). PLEASE REVIEW THE FOLLOWING TERMS AND CONDITIONS OF THIS   |
| LICENSE AGREEMENT CAREFULLY BEFORE INSTALLING OR USING THE SOFTWARE. BY     |
| INSTALLING, COPYING OR OTHERWISE USING THE SOFTWARE, YOU AND YOUR COMPANY   |
| (COLLECTIVELY, "YOU") ARE ACCEPTING AND AGREEING TO THE TERMS OF THIS       |
| LICENSE AGREEMENT. IF YOU ARE NOT WILLING TO BE BOUND BY THIS AGREEMENT, DO |
| NOT INSTALL OR USE THE SOFTWARE. VARIOUS COPYRIGHTS AND OTHER INTELLECTUAL  |
| PROPERTY RIGHTS PROTECT THE SOFTWARE. THIS AGREEMENT IS A LICENSE AGREEMENT |
| THAT GIVES YOU LIMITED RIGHTS TO USE THE SOFTWARE AND NOT AN AGREEMENT FOR  |
| SALE OR FOR TRANSFER OF TITLE. THE AUTHOR RETAINS ALL RIGHTS NOT EXPRESSLY  |
| GRANTED BY THIS AGREEMENT.                                                  |
+-----------------------------------------------------------------------------+
\*****************************************************************************/

/**
 * X-Cart initialization
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Lib
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    233b03f96d1a001701a8875b30c4d46db58f5290, v307 (xcart_4_7_1), 2015-03-05 15:49:22, init.php, aim
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if (!defined('XCART_START')) { header("Location: index.php"); die("Access denied"); }

/**
 * init.php can be loaded in two steps via $xc_load_init_step1/XC_LOAD_INIT_STEP2 vars
 * do not call db_connection etc twice for STEP2
 * do not obtain configs twice for STEP2
 */
if (!defined('XC_LOAD_INIT_STEP2')) {
require_once $xcart_dir . '/prepare.php';
require_once $xcart_dir . '/include/func/func.core.php';

x_load(
    'db',
    'files',
    'compat',
    'gd',
    'clean_urls',
    'image',
    'memcache'
);

func_set_memory_limit('96M');
// smarty3 needs a lot of time to compile all templates_c from scratch
func_set_time_limit(SECONDS_PER_MIN * 5);

/**
 * Allow displaying content in functions, registered in register_shutdown_function()
 */
$zlib_oc = ini_get('zlib.output_compression');

if (!empty($zlib_oc)) {
    define('NO_RSFUNCTION', true);
}

unset($zlib_oc);

if (
    function_exists('date_default_timezone_get')
    && function_exists('date_default_timezone_set')
) {
    @date_default_timezone_set(@date_default_timezone_get());
}

$_phpver = phpversion();
if (version_compare($_phpver, '5.5.0') >= 0) {
    define('X_PHP550_COMPAT', true);
    define('X_PHP540_COMPAT', true);
    define('X_PHP530_COMPAT', true);
} elseif(version_compare($_phpver, '5.4.0') >= 0) {
    define('X_PHP540_COMPAT', true);
    define('X_PHP530_COMPAT', true);
} elseif(version_compare($_phpver, '5.3.0') >= 0) {
    define('X_PHP530_COMPAT', true);
}
$_phpver = null;

if (!@is_readable($xcart_dir . '/config.php')) {
    func_show_error_page("Cannot read config!");
}

require_once $xcart_dir . '/config.php';

if (is_readable($xcart_dir . '/config.demo.php')) {
    include_once $xcart_dir . '/config.demo.php';
}

/**
 * This directive defines if some secured information would be
 * shown on the WEB (file system structure, MySQL internal error)
 * Currently it depends on $debug_mode value.
 */
$display_critical_errors = in_array($debug_mode, array(1, 3));

/**
 * HTTP & HTTPS locations
 */
$http_location    = 'http://' . trim($xcart_http_host, XC_WEB_DS) . (empty($xcart_web_dir) ? '' : (XC_WEB_DS . trim($xcart_web_dir, XC_WEB_DS)));
$https_location   = 'https://' . trim($xcart_https_host, XC_WEB_DS) . (empty($xcart_web_dir) ? '' : (XC_WEB_DS . trim($xcart_web_dir, XC_WEB_DS)));

$current_location = $HTTPS ? $https_location : $http_location;

if (
    (
        !isset($is_install_preview)
        || $is_install_preview != 'Y'
    )
    && !defined('XCART_EXT_ENV')
    && (
        empty($sql_host)
        || $sql_host == '%SQL_HOST%'
        || empty($sql_user)
        || $sql_user == '%SQL_USER%'
        || empty($sql_db)
        || $sql_db == '%SQL_DB%'
        || $sql_password == '%SQL_PASSWORD%'
    )
) {

    $message = "X-Cart software cannot connect to the MySQL database because your MySQL account information is missing from X-Cart's configuration file config.php.";

    $install_script = $xcart_dir . XC_DS . 'install.php';

    $install_script = (is_readable($install_script))
        ? func_get_xcart_home() . '/install.php'
        : false;

    $extra_info = "<p>This may be caused by that X-Cart installation has not been carried out or the file config.php has been edited in a wrong way. ";

    if ($install_script) {
        $extra_info .= "If you think X-Cart installation has not been performed or has not been completed properly, use the link below to run X-Cart's installation script.";
    }

    $extra_info .= "</p>";

    $extra_info .= "<p>If the installation process has been completed, but you are getting this message, the problem is likely caused by incorrect information in your config.php file. Check the file config.php and make sure the SQL database details settings in it are correct.</p>";

    if (false !== $install_script) {

        $extra_info .= "<p><a href='$install_script'>Run the installation script</a></p>";

    }

    func_show_error_page("Cannot connect to the database", $message, $extra_info);
}

$file_temp_dir = $var_dirs['tmp'];

/**
 * SQL tables aliases...
 */

// WARNING!!!
// Do not change the table name prefix in $sql_tbl!
// Otherwise you will not be able to upgrade and reinstall the software.
define('XC_TBL_PREFIX', 'xcart_'); 

$sql_tbl = array (
    'address_book'                      => XC_TBL_PREFIX . 'address_book',
    'amazon_data'                       => XC_TBL_PREFIX . 'amazon_data',
    'amazon_orders'                     => XC_TBL_PREFIX . 'amazon_orders',
    'benchmark_pages'                   => XC_TBL_PREFIX . 'benchmark_pages',
    'categories'                        => XC_TBL_PREFIX . 'categories',
    'categories_lng'                    => XC_TBL_PREFIX . 'categories_lng',
    'categories_subcount'               => XC_TBL_PREFIX . 'categories_subcount',
    'category_bookmarks'                => XC_TBL_PREFIX . 'category_bookmarks',
    'category_memberships'              => XC_TBL_PREFIX . 'category_memberships',
    'category_threshold_bestsellers'    => XC_TBL_PREFIX . 'category_threshold_bestsellers',
    'cc_gestpay_data'                   => XC_TBL_PREFIX . 'cc_gestpay_data',
    'cc_pp3_data'                       => XC_TBL_PREFIX . 'cc_pp3_data',
    'ccprocessor_params'                => XC_TBL_PREFIX . 'ccprocessor_params',
    'ccprocessors'                      => XC_TBL_PREFIX . 'ccprocessors',
    'clean_urls'                        => XC_TBL_PREFIX . 'clean_urls',
    'clean_urls_history'                => XC_TBL_PREFIX . 'clean_urls_history',
    'config'                            => XC_TBL_PREFIX . 'config',
    'contact_fields'                    => XC_TBL_PREFIX . 'contact_fields',
    'counties'                          => XC_TBL_PREFIX . 'counties',
    'countries'                         => XC_TBL_PREFIX . 'countries',
    'country_currencies'                => XC_TBL_PREFIX . 'country_currencies',
    'currencies'                        => XC_TBL_PREFIX . 'currencies',
    'customer_enabled_activities'       => XC_TBL_PREFIX . 'customer_enabled_activities',
    'customer_saved_carts'              => XC_TBL_PREFIX . 'customer_saved_carts',
    'customers'                         => XC_TBL_PREFIX . 'customers',
    'delayed_queries'                   => XC_TBL_PREFIX . 'delayed_queries',
    'delivery'                          => XC_TBL_PREFIX . 'delivery',
    'discount_coupons'                  => XC_TBL_PREFIX . 'discount_coupons',
    'discount_coupons_login'            => XC_TBL_PREFIX . 'discount_coupons_login',
    'discount_memberships'              => XC_TBL_PREFIX . 'discount_memberships',
    'discounts'                         => XC_TBL_PREFIX . 'discounts',
    'download_keys'                     => XC_TBL_PREFIX . 'download_keys',
    'export_ranges'                     => XC_TBL_PREFIX . 'export_ranges',
    'extra_field_values'                => XC_TBL_PREFIX . 'extra_field_values',
    'extra_fields'                      => XC_TBL_PREFIX . 'extra_fields',
    'extra_fields_lng'                  => XC_TBL_PREFIX . 'extra_fields_lng',
    'featured_products'                 => XC_TBL_PREFIX . 'featured_products',
    'form_ids'                          => XC_TBL_PREFIX . 'form_ids',
    'ge_products'                       => XC_TBL_PREFIX . 'ge_products',
    'giftcerts'                         => XC_TBL_PREFIX . 'giftcerts',
    'images_C'                          => XC_TBL_PREFIX . 'images_C',
    'images_D'                          => XC_TBL_PREFIX . 'images_D',
    'images_G'                          => XC_TBL_PREFIX . 'images_G',
    'images_M'                          => XC_TBL_PREFIX . 'images_M',
    'images_P'                          => XC_TBL_PREFIX . 'images_P',
    'images_T'                          => XC_TBL_PREFIX . 'images_T',
    'import_cache'                      => XC_TBL_PREFIX . 'import_cache',
    'internal_banners'                  => XC_TBL_PREFIX . 'internal_banners',
    'iterations'                        => XC_TBL_PREFIX . 'iterations',
    'language_codes'                    => XC_TBL_PREFIX . 'language_codes',
    'languages'                         => XC_TBL_PREFIX . 'languages',
    'languages_alt'                     => XC_TBL_PREFIX . 'languages_alt',
    'login_history'                     => XC_TBL_PREFIX . 'login_history',
    'manufacturers'                     => XC_TBL_PREFIX . 'manufacturers',
    'manufacturers_lng'                 => XC_TBL_PREFIX . 'manufacturers_lng',
    'memberships'                       => XC_TBL_PREFIX . 'memberships',
    'memberships_lng'                   => XC_TBL_PREFIX . 'memberships_lng',
    'modules'                           => XC_TBL_PREFIX . 'modules',
    'newsletter'                        => XC_TBL_PREFIX . 'newsletter',
    'newslist_subscription'             => XC_TBL_PREFIX . 'newslist_subscription',
    'newslists'                         => XC_TBL_PREFIX . 'newslists',
    'old_passwords'                     => XC_TBL_PREFIX . 'old_passwords',
    'order_details'                     => XC_TBL_PREFIX . 'order_details',
    'order_details_stats'               => XC_TBL_PREFIX . 'order_details_stats',
    'order_extras'                      => XC_TBL_PREFIX . 'order_extras',
    'orders'                            => XC_TBL_PREFIX . 'orders',
    'packages_cache'                    => XC_TBL_PREFIX . 'packages_cache',
    'pages'                             => XC_TBL_PREFIX . 'pages',
    'payment_countries'                 => XC_TBL_PREFIX . 'payment_countries',
    'payment_methods'                   => XC_TBL_PREFIX . 'payment_methods',
    'pmethod_memberships'               => XC_TBL_PREFIX . 'pmethod_memberships',
    'pricing'                           => XC_TBL_PREFIX . 'pricing',
    'product_bookmarks'                 => XC_TBL_PREFIX . 'product_bookmarks',
    'product_links'                     => XC_TBL_PREFIX . 'product_links',
    'product_memberships'               => XC_TBL_PREFIX . 'product_memberships',
    'product_reviews'                   => XC_TBL_PREFIX . 'product_reviews',
    'product_rnd_keys'                  => XC_TBL_PREFIX . 'product_rnd_keys',
    'product_sales_stats'               => XC_TBL_PREFIX . 'product_sales_stats',
    'product_taxes'                     => XC_TBL_PREFIX . 'product_taxes',
    'product_votes'                     => XC_TBL_PREFIX . 'product_votes',
    'products'                          => XC_TBL_PREFIX . 'products',
    'products_categories'               => XC_TBL_PREFIX . 'products_categories',
    'provider_commissions'              => XC_TBL_PREFIX . 'provider_commissions',
    'provider_product_commissions'      => XC_TBL_PREFIX . 'provider_product_commissions',
    'quick_flags'                       => XC_TBL_PREFIX . 'quick_flags',
    'quick_prices'                      => XC_TBL_PREFIX . 'quick_prices',
    'register_field_address_values'     => XC_TBL_PREFIX . 'register_field_address_values',
    'register_field_values'             => XC_TBL_PREFIX . 'register_field_values',
    'register_fields'                   => XC_TBL_PREFIX . 'register_fields',
    'reset_passwords'                   => XC_TBL_PREFIX . 'reset_passwords',
    'secure3d_data'                     => XC_TBL_PREFIX . 'secure3d_data',
    'seller_addresses'                  => XC_TBL_PREFIX . 'seller_addresses',
    'session_history'                   => XC_TBL_PREFIX . 'session_history',
    'session_unknown_sid'               => XC_TBL_PREFIX . 'session_unknown_sid',
    'sessions_data'                     => XC_TBL_PREFIX . 'sessions_data',
    'setup_images'                      => XC_TBL_PREFIX . 'setup_images',
    'shipping'                          => XC_TBL_PREFIX . 'shipping',
    'shipping_cache'                    => XC_TBL_PREFIX . 'shipping_cache',
    'shipping_labels'                   => XC_TBL_PREFIX . 'shipping_labels',
    'shipping_options'                  => XC_TBL_PREFIX . 'shipping_options',
    'shipping_rates'                    => XC_TBL_PREFIX . 'shipping_rates',
    'split_checkout'                    => XC_TBL_PREFIX . 'split_checkout',
    'states'                            => XC_TBL_PREFIX . 'states',
    'states_districts'                  => XC_TBL_PREFIX . 'states_districts',
    'stats_adaptive'                    => XC_TBL_PREFIX . 'stats_adaptive',
    'stats_search'                      => XC_TBL_PREFIX . 'stats_search',
    'tax_rate_memberships'              => XC_TBL_PREFIX . 'tax_rate_memberships',
    'tax_rates'                         => XC_TBL_PREFIX . 'tax_rates',
    'taxes'                             => XC_TBL_PREFIX . 'taxes',
    'temporary_data'                    => XC_TBL_PREFIX . 'temporary_data',
    'titles'                            => XC_TBL_PREFIX . 'titles',
    'users_online'                      => XC_TBL_PREFIX . 'users_online',
    'wishlist'                          => XC_TBL_PREFIX . 'wishlist',
    'zone_element'                      => XC_TBL_PREFIX . 'zone_element',
    'zones'                             => XC_TBL_PREFIX . 'zones',
);

/**
 * Redefine error_reporting option
 */
if (
    defined('X_PHP530_COMPAT')
    && $x_error_reporting != -1
) {

    $x_error_reporting = $x_error_reporting & ~(E_DEPRECATED | E_USER_DEPRECATED);

}

error_reporting($x_error_reporting);

/**
 * Fix broken path for some hostings
 */
$_tmp = @parse_url($current_location);

$xcart_web_dir = empty($_tmp['path']) ? '' : $_tmp['path'];

if ($HTTPS_RELAY) {

    // Fix wrong PHP_SELF for HTTPS relay
    $_tmp = @parse_url($http_location);

    $PHP_SELF = empty($_tmp['path'])
        ? $xcart_web_dir . $PHP_SELF
        : $xcart_web_dir . preg_replace("/^" . preg_quote($_tmp['path'], "/")."/", "", $PHP_SELF);

    $_SERVER['PHP_SELF'] = $PHP_SELF;

    $xcart_web_dir = preg_replace("/\/[\w\d_-]+\.[\w\d]+$/", '', $PHP_SELF);

    $for_replace = false;

    switch(AREA_TYPE) {

        case 'C':
            $for_replace = DIR_CUSTOMER;
            break;

        case 'A':
            $for_replace = DIR_ADMIN;
            break;

        case 'P':
            $for_replace = DIR_PROVIDER;
            break;

        case 'B':
            $for_replace = DIR_PARTNER;
            break;
    }

    if (false !== $for_replace) {

        $xcart_web_dir = preg_replace('/' . preg_quote($for_replace, '/') . "$/", '', $xcart_web_dir);

    }
}

$_tmp = @parse_url($https_location);
$xcart_https_host = $_tmp['host'];
unset($_tmp);

$_tmp = @parse_url($http_location);
$xcart_http_host = $_tmp['host'];
unset($_tmp);

/**
 * Create URL
 */
$request_uri_info = @parse_url($REQUEST_URI);
$php_url = array(
    'url'     => 'http'
        . (
            $HTTPS
            ? 's://'
                . $xcart_https_host
            : '://'
                . $xcart_http_host
        )
        . (
            !zerolen($request_uri_info['path'])
                ? $request_uri_info['path']
                : $PHP_SELF
        ),
    'query_string' => $QUERY_STRING,
);

/**
 * Check internal temporary directories
 */
$var_dirs_rules = array (
    'cache' => array (
        '.htaccess' => "Order Deny,Allow\n<Files \"*\">\n Deny from all\n</Files>\n\n<FilesMatch \"\\.(css|js|html)$\">\n Allow from all\n</FilesMatch>\n"
    )
);

if (
    (TRUE
        && !defined('SKIP_CHECK_REQUIREMENTS.PHP')
        && !defined('QUICK_START')
        && !empty($REQUEST_METHOD)
        && $REQUEST_METHOD == 'GET'
        && (!func_is_ajax_request() || func_constant('AREA_TYPE') != 'C')
    )
    || defined('XCART_INSTALLER')
) {
    foreach ($var_dirs as $k => $v) {
        func_restore_var_dir($k, $v);
    }
}

if (!file_exists($xcart_dir . '/var/.htaccess')) {

    if ($fp = @fopen($xcart_dir . '/var/.htaccess', 'w')) {

        @fwrite($fp, "Order Deny,Allow\nDeny from all\n");

        @fclose($fp);

        func_chmod_file($xcart_dir . '/var/.htaccess', 0644);

    }

}

/**
 * Initialize logging
 */
require_once $xcart_dir . '/include/logging.php';

/**
 * Include functions
 */
include_once($xcart_dir . '/include/bench.php');

/**
 * Connect to database
 */

$mysql_error_count = 0;

db_connection($sql_host, $sql_user, $sql_db, $sql_password);

/**
 * Search products_lng_.. tables
 */
$_lngs = func_query_column("SHOW TABLES LIKE '" . XC_TBL_PREFIX . "products_lng_%'") ;
foreach ($_lngs as $f => $_lng_table) {
    $tbl_alias = str_replace(XC_TBL_PREFIX, '', $_lng_table);
    $sql_tbl[$tbl_alias] = $_lng_table;
}

if (!empty($sql_tbl['products_lng_en'])) {
    func_set_lng_current('products_lng_', 'en');
} else {
    $_code = str_replace('products_lng_', '', $tbl_alias);
    func_set_lng_current('products_lng_', $_code);
}


/**
 * Read config variables from Database
 * These variables are used inside php scripts, not in smarty templates
 */

global $memcache;

$get_config = true;

if ($memcache) {

    $config = func_get_mcache_data('inner_config');

    $get_config = false === $config;

    register_shutdown_function('func_remove_mcache_config');
}

if ($get_config) {

    $c_result = db_query("SELECT name, value, category FROM $sql_tbl[config] WHERE type != 'separator'");

    $config = is_array($config) ? $config : array();
    assert('count($config) <= 1 /*Only "db_charset" key prefilled is allowed.Check if the new data is not garbage*/');

    if ($c_result) {

        while ($row = db_fetch_row($c_result)) {

            if (!empty($row[2])) {

                if ('XCART_INNER_EVENTS' !== $row[2]) {

                    $config[$row[2]][$row[0]] = $row[1];

                }

            } else {

                $config[$row[0]] = $row[1];

            }

        }

    }

    db_free_result($c_result);

    if ($memcache) {

        func_store_mcache_data('inner_config', $config);

    }

}
// xcart_catalogs/xcart_catalogs_insecure/xcart_catalogs_secure is used in func_get_resource_url clean_urls on STEP1 before XC_POINT_DB
$xcart_catalogs = array (
    'admin'    => $current_location . DIR_ADMIN,
    'customer' => $current_location . DIR_CUSTOMER,
    'provider' => $current_location . DIR_PROVIDER,
    'partner'  => $current_location . DIR_PARTNER,
);

$xcart_catalogs_insecure = array (
    'admin'    => $http_location . DIR_ADMIN,
    'customer' => $http_location . DIR_CUSTOMER,
    'provider' => $http_location . DIR_PROVIDER,
    'partner'  => $http_location . DIR_PARTNER,
);

$xcart_catalogs_secure = array (
    'admin'    => $https_location . DIR_ADMIN,
    'customer' => $https_location . DIR_CUSTOMER,
    'provider' => $https_location . DIR_PROVIDER,
    'partner'  => $https_location . DIR_PARTNER,
);
} // if (!defined('XC_LOAD_INIT_STEP2'))

// Simple db functions can be used at this point
/*********************************************************************
*
*                           XC_POINT_DB
*
**********************************************************************/


/**
 * Somebody cannot hack init.php via $_GET['xc_load_init_step1'] as he cannot create an object via GET request
 */
if (
    defined('USE_SIMPLE_DB_INTERFACE')
    || (
        !empty($xc_load_init_step1)
        && $xc_load_init_step1->isActive
    )
) {
    return;
}

/*
 * Check PHP ini since last launch and write changes to log file
*/
if (!defined('SKIP_CHECK_REQUIREMENTS.PHP')) {
    func_check_phpini_changes();
}    

func_force_data_cache_update();

/**
 * Initialize alt_skin feature
 */
if (defined('ALLOW_ALT_SKIN_FROM_GET_OR_COOKIES')) {
    func_redefine_alt_skin_from_get_or_cookies();
}

list($altSkinsInfo, $alt_skin_info, $alt_skin_dir) = func_get_alt_skin();

/**
 * Create Smarty object
 */

if (!include $xcart_dir . '/smarty.php') {
    func_show_error_page("Cannot launch template engine!", '');
}

$smarty->assign('alt_skin_info',  $alt_skin_info);
$smarty->assign('alt_skins_info', $altSkinsInfo);

/**
 * Init miscellaneous vars
 */
$smarty     ->assign('skin_config',      $skin_config_file);
$smarty     ->assign('http_location',    $http_location);
$smarty     ->assign('https_location',   $https_location);
$smarty     ->assign('xcart_web_dir',    $xcart_web_dir);
$smarty     ->assign('current_location', $current_location);
$smarty     ->assign('php_url',          $php_url);

foreach ($var_dirs_web as $k => $v) {
    $var_dirs_web[$k] = $current_location . $v;
}

$smarty->assignByRef('var_dirs_web', $var_dirs_web);
$smarty      ->assign('catalogs',        $xcart_catalogs);
$smarty      ->assign('catalogs_secure', $xcart_catalogs_secure);

/**
 * Files directories
 */
$files_dir_name      = $xcart_dir . $files_dir;
$files_http_location = $http_location . $files_webdir;

$smarty->assign('files_location', $files_dir_name);

$templates_repository = $xcart_dir . $templates_repository_dir;

$md5_check_devlicense = '726e5429de89a8afb5fe2ed1040fb852';

/**
 * Retrive registration information from database
 */
$shop_evaluation = func_is_evaluation();

$smarty->assign('shop_evaluation', $shop_evaluation);

if ($shop_evaluation) {
    if (stripos(basename($php_url['url']), 'cart.php') === FALSE) {
        $show_evaluation_notice = func_is_evaluation_expired();
    } else {
        $show_evaluation_notice = TRUE;
    }

    $smarty->assign('show_evaluation_notice', $show_evaluation_notice);

} else {
    $show_evaluation_notice = FALSE;
}

/**
 * Schema to test .htaccess file if some configuration variables are on.
 */
$schemaTestHtaccess = array(
    array(
        'config' => array(
            'SEO',
            'clean_urls_enabled',
        ),
        'htaccessWord' => 'dispatcher.php [L]',
    ),
);

$htaccessWarning = array();

foreach ($schemaTestHtaccess as $schemaUnit) {
    if (
        'Y' == $config[$schemaUnit['config'][0]][$schemaUnit['config'][1]]
        && !func_test_htaccess($schemaUnit['htaccessWord'])
    ) {
        if (
            defined('AREA_TYPE')
            && 'C' == constant('AREA_TYPE')
        ) {

            $config[$schemaUnit['config'][0]][$schemaUnit['config'][1]] = 'N';

        } else {

            $htaccessWarning[$schemaUnit['config'][0]] = "Y";

        }
    }
}

$smarty->assign('htaccess_warning', $htaccessWarning);

/**
 * Timezone offset (sec) = N hours x 60 minutes x 60 seconds
 */
$config['Appearance']['timezone_offset'] = intval($config['Appearance']['timezone_offset'] * 3600);

/**
 * Define 'End year' for date selectors in the templates
 */
$config['Company']['end_year'] = func_date('Y', XC_TIME + $config['Appearance']['timezone_offset']);

/**
 * Last database backup date
 */
if (!empty($config['db_backup_date']))
    $config['db_backup_date'] += $config['Appearance']['timezone_offset'];

$config['available_images']['T'] = "U";
$config['available_images']['P'] = "U";
$config['available_images']['C'] = "U";
$config['available_images']['G'] = "U";

$config['substitute_images']['P'] = "T";

$httpsmod_active = NULL;

if (!defined('QUICK_START')) {

    if (empty($config['Appearance']['thumbnail_width']))
        $config['Appearance']['thumbnail_width'] = 0;

    if (empty($config['Appearance']['date_format']))
        $config['Appearance']['date_format'] = "%d-%m-%Y";

    $config['Appearance']['datetime_format'] =
        $config['Appearance']['date_format'] . " " . $config['Appearance']['time_format'];

}

$config['Appearance']['thumbnail_width'] = intval($config['Appearance']['thumbnail_width']);

/**
 * Prepare session
 */
include_once $xcart_dir . '/include/sessions.php';

if (defined('USE_SIMPLE_SESSION_INTERFACE')) {
    return;
}

if (defined('ADMIN_UNALLOWED_VAR_FLAG') && $config['Security']['unallowed_request_notify'] == 'Y')
    include_once $xcart_dir . '/include/unallowed_request.php';

// Search engine bots & spiders identificator
if (!defined('DO_NOT_START_SESSION')) {
    require_once $xcart_dir . '/include/bots.php';
}

if (!defined('QUICK_START')) {

    include_once($xcart_dir . '/include/blowfish.php');

    // Start Blowfish class
    $blowfish = new ctBlowfish();
}

/**
 * Prepare number variables
 */
include_once $xcart_dir . '/include/number_conv.php';

if (!defined('QUICK_START')) {

    /**
     * Define default user profile fields
     */
    $default_user_profile_fields = array(
        'title'         => array(
            'avail'     => array('A' => 'N', 'P' => 'N', 'B' => 'N', 'C' => 'N', 'H' => 'N'),
            'required'  => array('A' => 'N', 'P' => 'N', 'B' => 'N', 'C' => 'N', 'H' => 'N')
        ),
        'firstname'     => array(
            'avail'     => array('A' => 'Y', 'P' => 'Y', 'B' => 'Y', 'C' => 'Y', 'H' => 'N'),
            'required'  => array('A' => 'Y', 'P' => 'Y', 'B' => 'Y', 'C' => 'Y', 'H' => 'N')
        ),
        'lastname'      => array(
            'avail'     => array('A' => 'Y', 'P' => 'Y', 'B' => 'Y', 'C' => 'Y', 'H' => 'N'),
            'required'  => array('A' => 'Y', 'P' => 'Y', 'B' => 'Y', 'C' => 'Y', 'H' => 'N')
        ),
        'company'       => array(
            'avail'     => array('A' => 'Y', 'P' => 'Y', 'B' => 'Y', 'C' => 'Y', 'H' => 'N'),
            'required'  => array('A' => 'N', 'P' => 'N', 'B' => 'N', 'C' => 'N', 'H' => 'N')
        ),
        'url'           => array(
            'avail'     => array('A' => 'Y', 'P' => 'Y', 'B' => 'Y', 'C' => 'Y', 'H' => 'N'),
            'required'  => array('A' => 'N', 'P' => 'N', 'B' => 'N', 'C' => 'N', 'H' => 'N')
        ),
        'ssn'           => array (
            'avail'     => array('A' => 'N', 'P' => 'N', 'B' => 'Y', 'C' => 'N' ,'H' => 'N'),
            'required'  => array('A' => 'N', 'P' => 'N', 'B' => 'Y', 'C' => 'N', 'H' => 'N')
        ),
        'tax_number'    => array (
            'avail'     => array('A' => 'N', 'P' => 'N', 'B' => 'Y', 'C' => 'Y' ,'H' => 'N'),
            'required'  => array('A' => 'N', 'P' => 'N', 'B' => 'Y', 'C' => 'N', 'H' => 'N')
        )
    );

    /**
     * Define default address book fields
     */
    $default_address_book_fields = array(
        'title' => array (
            'avail'     => array('A' => 'N', 'P' => 'N', 'B' => 'N', 'C' => 'N', 'H' => 'N'),
            'required'  => array('A' => 'N', 'P' => 'N', 'B' => 'N', 'C' => 'N', 'H' => 'N')
        ),
        'firstname'     => array(
            'avail'     => array('A' => 'N', 'P' => 'N', 'B' => 'N', 'C' => 'Y', 'H' => 'Y'),
            'required'  => array('A' => 'N', 'P' => 'N', 'B' => 'N', 'C' => 'Y', 'H' => 'Y')
        ),
        'lastname'      => array(
            'avail'     => array('A' => 'N', 'P' => 'N', 'B' => 'N', 'C' => 'Y', 'H' => 'Y'),
            'required'  => array('A' => 'N', 'P' => 'N', 'B' => 'N', 'C' => 'Y', 'H' => 'Y')
        ),
        'address'       => array(
            'avail'     => array('A' => 'N', 'P' => 'N', 'B' => 'N', 'C' => 'Y', 'H' => 'Y'),
            'required'  => array('A' => 'N', 'P' => 'N', 'B' => 'N', 'C' => 'Y', 'H' => 'Y')
        ),
        'address_2'     => array(
            'avail'     => array('A' => 'N', 'P' => 'N', 'B' => 'N', 'C' => 'Y', 'H' => 'Y'),
            'required'  => array('A' => 'N', 'P' => 'N', 'B' => 'N', 'C' => 'N', 'H' => 'N')
        ),
        'city'          => array(
            'avail'     => array('A' => 'N', 'P' => 'N', 'B' => 'N', 'C' => 'Y', 'H' => 'Y'),
            'required'  => array('A' => 'N', 'P' => 'N', 'B' => 'N', 'C' => 'Y', 'H' => 'Y')
        ),
        'country'       => array(
            'avail'     => array('A' => 'N', 'P' => 'N', 'B' => 'N', 'C' => 'Y', 'H' => 'Y'),
            'required'  => array('A' => 'N', 'P' => 'N', 'B' => 'N', 'C' => 'Y', 'H' => 'Y')
        ),
        'state'         => array(
            'avail'     => array('A' => 'N', 'P' => 'N', 'B' => 'N', 'C' => 'Y', 'H' => 'Y'),
            'required'  => array('A' => 'N', 'P' => 'N', 'B' => 'N', 'C' => 'Y', 'H' => 'Y')
        ),
        'county'        => array(
            'avail'     => array('A' => 'N', 'P' => 'N', 'B' => 'N', 'C' => 'N', 'H' => 'N'),
            'required'  => array('A' => 'N', 'P' => 'N', 'B' => 'N', 'C' => 'N', 'H' => 'N')
        ),
        'zipcode'       => array(
            'avail'     => array('A' => 'N', 'P' => 'N', 'B' => 'N', 'C' => 'Y', 'H' => 'Y'),
            'required'  => array('A' => 'N', 'P' => 'N', 'B' => 'N', 'C' => 'Y', 'H' => 'Y')
        ),
        'phone'         => array(
            'avail'     => array('A' => 'N', 'P' => 'N', 'B' => 'N', 'C' => 'Y', 'H' => 'Y'),
            'required'  => array('A' => 'N', 'P' => 'N', 'B' => 'N', 'C' => 'N', 'H' => 'N')
        ),
        'fax'           => array(
            'avail'     => array('A' => 'N', 'P' => 'N', 'B' => 'N', 'C' => 'Y', 'H' => 'Y'),
            'required'  => array('A' => 'N', 'P' => 'N', 'B' => 'N', 'C' => 'N', 'H' => 'N')
        )
    );

    /**
     * Define default contact us fields
     */
    $default_contact_us_fields = array(
        'department'    => array(
            'avail'     => 'Y',
            'required'  => 'Y'
        ),
        'username'      => array(
            'avail'     => 'Y',
            'required'  => 'Y'
        ),
        'title'         => array(
            'avail'     => 'N',
            'required'  => 'N'
        ),
        'firstname'     => array(
            'avail'     => 'Y',
            'required'  => 'Y'
        ),
        'lastname'      => array(
            'avail'     => 'Y',
            'required'  => 'Y'
        ),
        'company'       => array(
            'avail'     => 'Y',
            'required'  => 'N'
        ),
        'address'     => array(
            'avail'     => 'Y',
            'required'  => 'Y'
        ),
        'address_2'   => array(
            'avail'     => 'Y',
            'required'  => 'N'
        ),
        'city'        => array(
            'avail'     => 'Y',
            'required'  => 'Y'
        ),
        'country'      => array(
            'avail'     => 'Y',
            'required'  => 'Y'
        ),
        'state'       => array(
            'avail'     => 'Y',
            'required'  => 'Y'
        ),
        'county'     => array(
            'avail'     => 'Y',
            'required'  => 'Y'
        ),
        'zipcode'     => array(
            'avail'     => 'Y',
            'required'  => 'Y'
        ),
        'phone'         => array(
            'avail'     => 'Y',
            'required'  => 'Y'
        ),
        'email'         => array(
            'avail'     => 'Y',
            'required'  => 'Y'
        ),
        'fax'           => array(
            'avail'     => 'Y',
            'required'  => 'N'
        ),
        'url'           => array(
            'avail'     => 'Y',
            'required'  => 'N'
        )
    );

    /**
     * Define shipping estimator fields
     */

    $shipping_estimate_fields = array(
        'city' => array(
            'avail'    => 'Y',
            'required' =>  ''
        ),
        'county' => array(
            'avail'    => 'Y',
            'required' =>  ''
        ),
        'state' => array(
            'avail'    => 'Y',
            'required' => 'Y'
        ),
        'country' => array(
            'avail'    => 'Y',
            'required' => 'Y'
        ),
        'zipcode' => array(
            'avail'    => 'Y',
            'required' => ''
        )
    );

    if ($config['General']['use_counties'] != 'Y') {

        // Disable county usage

        $_N_array = array('A' => 'N', 'P' => 'N', 'B' => 'N', 'C' => 'N', 'H' => 'N');
        $default_address_book_fields['county']['avail']    = $_N_array;
        $default_address_book_fields['county']['required'] = $_N_array;

        $default_contact_us_fields['county']['avail']    = 'N';
        $default_contact_us_fields['county']['required'] = 'N';

        $shipping_estimate_fields['county']['avail']       = 'N';
    }

    $taxes_units = array(
        'ST'  => 'lbl_subtotal',
        'DST' => 'lbl_discounted_subtotal',
        'SH'  => 'lbl_shipping_cost',
    );

    // Unserialize & Assign Right-to-Left languages
    if (isset($config['r2l_languages']))
        $config['r2l_languages'] = unserialize ($config['r2l_languages']);

    if (!defined('XCART_EXT_ENV')) {
        // Include webmaster mode
        include_once $xcart_dir . '/include/webmaster.php';

        if(
            $config['General']['enable_debug_console'] == 'Y'
            || $editor_mode == 'editor'
        ) {
            $smarty->debugging = true;
        }
    }

    // IP addresses
    $smarty->assign('PROXY_IP',         $PROXY_IP);
    $smarty->assign('CLIENT_IP',        $CLIENT_IP);
    $smarty->assign('REMOTE_ADDR',      $REMOTE_ADDR);

    // Disable Clean URLs functionality if a request is performed by the HTML Catalog generator script.
    if (defined('IS_ROBOT') && defined('ROBOT') && constant('ROBOT') == 'X-Cart Catalog Generator') {
        $config['SEO']['clean_urls_enabled'] = 'N';
    }

    // Adaptives section
    if (!defined('XCART_EXT_ENV')) {
        include_once $xcart_dir . '/include/adaptives.php';
    }

}

/**
 * Crontab tasks list
 */
$cron_tasks = array();

$cron_tasks[] = array(
    'x_load'   => 'payment',
    'function' => 'func_check_preauth_expiration'
);

$cron_tasks[] = array(
    'x_load'   => 'payment',
    'function' => 'func_check_preauth_expiration_ttl'
);

/**
 * Read Modules and put in into $active_modules
 */
global $active_modules;
$import_specification = array();
$active_modules = func_get_active_modules();

if (!is_array($active_modules))
    $active_modules = array();

$active_modules["Simple_Mode"] = true;
$shop_type = "GOLD";
$addons = array();
$body_onload = '';
$tbl_demo_data = $tbl_keys = array();
$css_files = array();
$custom_styles = array();
$container_classes = array();
$predefined_setup_images = array();
$image_caches = array();
$smarty->assign('shop_type', ucfirst(strtolower($shop_type)));

// Define checkout module

if (!defined('AREA_TYPE') || AREA_TYPE == 'C') {

    x_session_register('flc_forced', false);

    $flc_forced = isset($force_flc);

    $checkout_module = empty($config['General']['checkout_module']) || $flc_forced
        ? 'Fast_Lane_Checkout'
        : $config['General']['checkout_module'];

    $active_modules[$checkout_module] = true;

    $smarty->assign('checkout_module', $checkout_module);
}

if ($active_modules) {
    if ($config['General']['use_new_module_initialization'] != 'Y') {
        $include_func = $include_init = false;
    } else {
        // Load functions for module (run include "modules/<module_name>/func.php")
        $include_func = true;

        // Init modules (run include "modules/<module_name>/init.php")
        $include_init = true; 
    }

    $_active_modules = $active_modules;
    foreach ($_active_modules as $active_module => $tmp) {

        $_module_dir  = $xcart_dir . XC_DS . 'modules' . XC_DS . $active_module;
        $_config_file = $_module_dir . XC_DS . 'config.php';
        $_func_file   = $_module_dir . XC_DS . 'func.php';

        if (is_readable($_config_file)) {
            include $_config_file;
        }

        if (
            $config['General']['use_new_module_initialization'] != 'Y'
            && is_readable($_func_file)
        ) {
            require_once $_func_file;
        }

    }
    unset($include_func, $include_init, $_active_modules);
}

$smarty->assignByRef('active_modules', $active_modules);

$config['setup_images'] = func_data_cache_get("setup_images");

foreach ($config['available_images'] as $k => $v) {

    if (isset($config['setup_images'][$k]))
        continue;

    if (isset($predefined_setup_images[$k])) {
        $config['setup_images'][$k] = $predefined_setup_images[$k];
        continue;
    }

    $config['setup_images'][$k] = array (
        'itype'         => $k,
        'location'      => 'FS',
        'save_url'      => '',
        'size_limit'    => 0,
        'md5_check'     => '',
        'default_image' => './default_image.gif',
        'image_x'       => 124,
        'image_y'       => 74
    );
}

$config['images_dimensions']['T']['width']  = $config['Appearance']['thumbnail_width'];
$config['images_dimensions']['T']['height'] = $config['Appearance']['thumbnail_height'];
$config['images_dimensions']['P']['width']  = 300;
$config['images_dimensions']['P']['height'] = 225;

$preview_image = 'preview_image.gif';

if (empty($config['User_Profiles']['register_fields']))
    $config['User_Profiles']['register_fields'] = serialize(array());

if (empty($config['User_Profiles']['address_book_fields']))
    $config['User_Profiles']['address_book_fields'] = serialize(array());

$config['Appearance']['ui_date_format'] = func_get_ui_date_format();

if (!func_is_display_states($config['Company']['location_country']))
    $config['Company']['location_state'] = '';

$smarty->assign('single_mode', $single_mode);

func_image_cache_assign('C', 'catthumbn');
func_image_cache_assign('T', 'tinythmbn');

/**
 * If Antibot turned off after it was loaded
 */
if (empty($active_modules['Image_Verification'])) {
    x_session_unregister('antibot_validation_val');
    x_session_unregister('antibot_friend_err');
    x_session_unregister('antibot_contactus_err');
    x_session_unregister('antibot_err');
}

if (!defined('QUICK_START')) {

    // Assign config array to smarty
    $smarty->assignByRef('config', $config);

    // Assign Smarty delimiters
    $smarty ->assign('ldelim', "{");

    $smarty ->assign('rdelim', "}");

    if (
        (
            isset($_GET['delimiter'])
            && $_GET['delimiter'] == 'tab'
        ) || (
            isset($_POST['delimiter'])
            && $_POST['delimiter'] == 'tab'
        )
    ) {

        $delimiter = "\t";

    }

    // Assign email regular expression
    $smarty->assign('email_validation_regexp',         func_email_validation_regexp());
    $smarty->assign('clean_url_validation_regexp',     func_clean_url_validation_regexp());
}

/**
 * Init modules. Used in all module initialization schema
 */
if (
    $config['General']['use_new_module_initialization'] != 'Y'
    && is_array($active_modules)
) {
    $_active_modules = $active_modules;

    foreach ($_active_modules as $__k => $__v) {

        if (is_readable($xcart_dir . '/modules/' . $__k . '/init.php')) {

            include $xcart_dir . '/modules/' . $__k . '/init.php';

        }
    }
    unset($_active_modules);
}


/**
 * Session-based cron
 */
if (!defined('QUICK_START') && defined('NEW_SESSION')) {

    $config['General']['cron_call_per_new_session'] = max(intval($config['General']['cron_call_per_new_session']), 0);
    if ($config['General']['cron_call_per_new_session'] > 0) {

        $config['cron_counter'] = max(intval(@$config['cron_counter']), 0);
        $config['cron_counter']++;

        if ($config['cron_counter'] >= $config['General']['cron_call_per_new_session']) {
            define('X_INTERNAL_CRON', true);
            require($xcart_dir . '/cron.php');
            $config['cron_counter'] = 0;
        }

        func_array2insert(
            'config',
            array(
                'name' => 'cron_counter',
                'value' => $config['cron_counter']
            ),
            true
        );
    }
}

/**
 * Remember visitor for a long time period
 */
$remember_user = true;

/**
 * Time period for which user info should be stored (days)
 */
$remember_user_days = 30;

$smarty->assign('current_area', func_get_current_area());

/**
 * Redirect from alias host to main host
 */
if (TRUE
    && !defined('XCART_EXT_ENV')
    && !defined('X_CRON')
    && !empty($REQUEST_METHOD)
    && $REQUEST_METHOD == 'GET'
    && !empty($_SERVER['HTTP_HOST'])
) {
    $tmp = explode(":", $_SERVER['HTTP_HOST'], 2);
    $server_http_host = strtolower($tmp[0]);
    if ($server_http_host != strtolower($xcart_http_host) && $server_http_host != strtolower($xcart_https_host) && (!$HTTPS || !$HTTPS_RELAY))
        func_header_location(($HTTPS ? "https://".$xcart_https_host : "http://".$xcart_http_host) . $REQUEST_URI, true, 301);
}

// Define name of the auth field depending on login setting: email or username
$login_field_name = func_get_langvar_by_name(
    'lbl_' . ($config['email_as_login'] == 'Y' ? 'email' : 'username'),
    NULL,
    false,
    true
);
$smarty->assign('login_field_name', $login_field_name);

// Detect modal dialog window
if (isset($_GET['open_in_layer'])) {
    $smarty->assign('is_modal_popup', true);
}

if (isset($_GET['is_ajax_request'])) {
    $smarty->assign('is_ajax_request', true);
}

// Check if the cookies are enabled in the browser
require $xcart_dir . '/include/nocookie_warning.php';

if (defined('DEVELOPMENT_MODE')) {
    x_load('dev','debug');

    if (
        !defined('XCART_INSTALLER')
        && !defined('DO_NOT_START_SESSION')
        && defined('XC_DEBUG_CHECK_NON_SAVED_SESSION_VARS')
    ) {
        register_shutdown_function('func_dev_check_non_saved_session_vars');
    }

    register_shutdown_function('func_dev_check_logical_errors');
    register_shutdown_function('func_dev_check_mysql_free_result_calls');
}

if (TRUE
    && !defined('SKIP_CHECK_REQUIREMENTS.PHP') 
    && !defined('QUICK_START') 
    && !defined('X_CRON')
    && !empty($REQUEST_METHOD)
    && $REQUEST_METHOD == 'GET'
    && !func_is_ajax_request()
) {
    func_trigger_postponed_events();
}

/**
 * WARNING !
 * Please ensure that you have no whitespaces / empty lines below this message.
 * Adding a whitespace or an empty line below this line will cause a PHP error.
 */
?>
