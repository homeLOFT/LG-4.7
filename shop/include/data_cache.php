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
 * Repository of data cache functions
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Lib
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v146 (xcart_4_7_0), 2015-02-17 23:56:28, data_cache.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if ( !defined('XCART_START') ) { header("Location: ../"); die("Access denied"); }

// if has_private_data is TRUE then add php die(); to header of the cache file, PEAR::Cache* is not applicable
$data_caches = array(
    'modules' => array(
        'func' => 'func_dc_modules',
        'include_constants' => array('SKIP_ALL_MODULES')
    ),
    'setup_images' => array(
        'func' => 'func_dc_setup_images'
    ),
    'charsets' => array(
        'func' => 'func_dc_charsets'
    ),
    'languages' => array(
        'func' => 'func_dc_languages'
    ),
    'sql' => array(
        'func' => 'func_dc_sql',
        'ttl' => SQL_DATA_CACHE_TTL,
        'exclude_keys' => array('query'),
        'has_private_data' => TRUE
    ),
    'get_address_by_ip' => array(
        'func' => 'func_get_address_by_ip',
        'ttl' => SECONDS_PER_DAY, // 1 day to invalidate cache
        'use_func_cache_logic' => TRUE
    ),
    'get_categories_tree' => array(
        'func' => 'func_dc_get_categories_tree',
        'include_global_keys' => array('current_area'),
        'func_is_valid' => 'func_dc_get_categories_tree_validate'
    ),
    'get_language_vars' => array(
        'func' => 'func_dc_get_language_vars',
        'ttl' => SECONDS_PER_DAY*30, // 1 month to invalidate cache
        'func_is_valid' => 'func_dc_get_language_vars_validate'
    ),
    'get_offers_categoryid' => array(
        'func' => 'func_dc_get_offers_categoryid',
    ),
    'get_schemes' => array(
        'func' => 'func_dc_get_schemes',
        'func_is_valid' => 'func_dc_get_schemes_validate',
        'ttl' => 86400*30, // 1 month to invalidate cache
        'fileNameProtection' => true,
    ),
    'getDirtyUpsellingProducts' => array(
        'class' => 'XCUpsellingProducts',
        'func' => 'getDirtyUpsellingProducts',
        'ttl' => SECONDS_PER_MIN*90, // 90 min to invalidate cache
        'use_func_cache_logic' => TRUE,
        'dir' => 'product_cache',
        'hashedDirectoryLevel' => 2,
    ),
    'get_package_limits_CPC' => array(
        'func' => 'func_get_package_limits_CPC',
        'ttl' => SECONDS_PER_DAY*30, // 1 month to invalidate cache
        'use_func_cache_logic' => TRUE
    ),
    'sql_vars' => array(
        'func' => 'func_dc_sql_vars',
        'ttl' => 1200, // 20 minutes to invalidate cache bt:#0092173
    ),
    'get_default_fields' => array(
        'func' => 'func_get_default_fields',
        'use_func_cache_logic' => TRUE
    ),
    'test_active_bouncer' => array(
        'func' => 'test_active_bouncer',
        'use_func_cache_logic' => TRUE,
        'ttl' => 600, // 10 min to invalidate cache
    ),
    'tpl_get_xcart_news' => array(
        'func' => 'func_tpl_get_xcart_news',
        'ttl' => 1200, // 20 minutes to invalidate cache bt:#111638
        'use_func_cache_logic' => TRUE
    ),
    'search_products_query_count' => array(
        'func' => 'func_search_products_query_count',
        'ttl' => 1200, // 20 minutes to invalidate cache
        'use_func_cache_logic' => TRUE,
        'dir' => 'search_cache',
        'hashedDirectoryLevel' => 1,
    ),
    'getRangeProductIds' => array(
        'func' => 'getRangeProductIds',
        'class' => 'XCRangeProductIds',
        'ttl' => 3600, // 60 minutes to invalidate cache bt:#0132187
        'use_func_cache_logic' => TRUE,
        'dir' => 'search_cache',
        'hashedDirectoryLevel' => 1,
    ),
    'get_xcart_paid_modules' => array(
        'func' => 'func_get_xcart_paid_modules',
        'ttl' => 2400, // 40 minutes to invalidate cache bt:#115042
        'use_func_cache_logic' => TRUE
    ),
    'get_xp_processors' => array(
        'func' => 'func_get_xp_processors',
        'ttl' => SECONDS_PER_DAY, // a day to invalidate cache
        'use_func_cache_logic' => TRUE
    ),
    'getSqlOptimizationLock' => array(
        'func' => 'getSqlOptimizationLock',
        'class' => 'XCOptimizeSQLTables',
        'ttl' => SECONDS_PER_DAY/2, // 12 hours to invalidate cache
        'use_func_cache_logic' => TRUE,
    ),
    'sql_tables_fields' => array(
        'func' => 'func_dc_sql_tables_fields',
        'func_is_valid' => 'func_dc_sql_tables_fields_validate'
    ),
);
$data_caches['getRangeProductIdsQuery'] = $data_caches['getRangeProductIds'];
$data_caches['getRangeProductIdsQuery']['ttl'] *= 10;

/**
 * A work-around for the signal race issue
 */
function func_force_data_cache_update() { // {{{
    global $config, $PHP_SELF, $sql_tbl, $REQUEST_METHOD;
    $all_data_cache_ttl = SECONDS_PER_DAY * 30;

    assert('!empty($config) /* '.__FUNCTION__.' */');
    if (TRUE
        && (empty($config['data_cache_expiration']) || (XC_TIME-$config['data_cache_expiration']) > $all_data_cache_ttl)
        && !defined('QUICK_START')
        && !defined('SKIP_CHECK_REQUIREMENTS.PHP')
        && !empty($PHP_SELF) 
        && preg_match('/(?:home|product|dispatcher)\.php/s', $PHP_SELF)
        && !empty($REQUEST_METHOD) 
        && $REQUEST_METHOD == 'GET'

        && !func_is_ajax_request()
    ) {
        sleep(mt_rand(0,2));
        if ((XC_TIME-func_query_first_cell("SELECT value FROM $sql_tbl[config] WHERE name='data_cache_expiration'")) > $all_data_cache_ttl) {
            $config['data_cache_expiration'] = XC_TIME+10;
            func_array2insert(
                    'config',
                    array(
                        'value'    => $config['data_cache_expiration'],
                        'name'     => 'data_cache_expiration',
                        'defvalue' => '',
                        'variants' => ''
                        ),
                    TRUE
                    );

            x_load('backoffice');
            func_remove_xcart_caches(TRUE, array('cache'));
        }
    }
} // }}}

/**
 * Sort active_modules according order to initialization
 */
function func_sort_active_modules($a, $b) { // {{{
    static $sort_order = array(
        'Demo_Mode' => -10001, # Demo module enables/disables other modules
        'Dev_Mode' => 0, # Dev module
        'Add_to_cart_popup' => 1, #independent module
        'Discount_Coupons' => 1, #independent module
        'EU_Cookie_Law' => 1, #independent module
        'Egoods' => 1, #independent module
        'Gift_Certificates' => 1, #independent module
        'Google_Analytics' => 1, #independent module
        'HTML_Editor' => 1, #independent module
        'Interneka' => 1, #independent module
        'PayPal_Login' => 1, #independent module
        'QuickBooks' => 1, #independent module
        'Recently_Viewed' => 1, #independent module
        'Socialize' => 1, #independent module
        'TaxCloud' => 1, #independent module
        'Upselling_Products' => 1, #independent module
        'XMultiCurrency' => 1, # Cloud_Search must be loaded after XMultiCurrency
        'Cloud_Search' => 2, # Cloud_Search must be loaded after XMultiCurrency
        'Amazon_Checkout' => 5, #can be unsetted in config/init.php
        'Flyout_Menus' => 5, #can be unsetted in config/init.php
        'Magnifier' => 5, #can be unsetted in config/init.php
        'Mailchimp_Subscription' => 5, #can be unsetted in config/init.php
        'Wishlist' => 11, #Gift_Registry depends on Wishlist
        'Gift_Registry' => 12, #can be unsetted in config/init.php
        'News_Management' => 31,
        'Survey' => 32,
        'Image_Verification' => 33,# Image_Verification depends on Survey/News_Management
        'Manufacturers' => 23,
        'XAffiliate' => 24, #XAffiliate depends on Manufacturers
        'Advanced_Customer_Reviews' => 2000, #Advanced_Customer_Reviews depends on Special_Offers/Product_Options/Manufacturers
        'POS_System' => 2000, #POS_System depends on Product_Options (Product_Options must be defined first)
        'XPayments_Subscriptions' => 3000, #can be unsetted in config/config.php; depends on XPayments_Connector
    );
    $key_a = isset($sort_order[$a]) ? -$sort_order[$a] : -1000;
    $key_b = isset($sort_order[$b]) ? -$sort_order[$b] : -1001;
    return $key_b - $key_a;
} // }}}

function func_dc_modules() { // {{{
    global $sql_tbl;

    $all_active_modules = func_query_column("SELECT module_name FROM " . $sql_tbl['modules'] . " USE INDEX (active) WHERE active='Y'");

    $active_modules = array();

    if ($all_active_modules) {
        usort($all_active_modules, 'func_sort_active_modules');
        foreach($all_active_modules as $active_module) {
            if (
                defined('SKIP_ALL_MODULES')
                && !in_array($active_module, array('Demo', 'Simple_Mode'))
            )
                continue; 

            $active_modules[$active_module] = TRUE;
        }
    }

    return $active_modules;
} // }}}

function func_dc_setup_images() { // {{{
    global $sql_tbl, $xcart_dir;

    $setup_images = func_query_hash("SELECT * FROM " . $sql_tbl['setup_images'], "itype", FALSE);

    if(!empty($setup_images)) {

        $default_images = array();

        foreach($setup_images as $k => $v) {

            if (!empty($v['default_image'])) {

                $tmp = isset($default_images[md5($v['default_image'])])
                    ? $default_images[md5($v['default_image'])]
                    : func_get_image_size($xcart_dir.XC_DS.$v['default_image']);

            }

            if (is_array($tmp)) {
                $setup_images[$k]['image_x'] = $tmp[1];
                $setup_images[$k]['image_y'] = $tmp[2];
            }

        }

    }

    return $setup_images;
} // }}}

function func_dc_charsets() { // {{{
    global $sql_tbl;

    return func_query_hash(
        "SELECT " . $sql_tbl['languages'] . ".code, " . $sql_tbl['language_codes'] . ".charset FROM " . $sql_tbl['languages'] . ", " . $sql_tbl['language_codes'] . " WHERE " . $sql_tbl['languages'] . ".code = " . $sql_tbl['language_codes'] . ".code AND " . $sql_tbl['language_codes'] . ".disabled != 'Y' GROUP BY " . $sql_tbl['languages'] . ".code ORDER BY NULL",
        'code',
        FALSE,
        TRUE
    );
} // }}}

function func_dc_languages($code) { // {{{
    global $sql_tbl, $current_location;

    $_codes = func_query_column("SELECT DISTINCT code FROM $sql_tbl[languages]");

    $languages_codes = implode("', '", $_codes);

    $languages = func_query_hash("
            SELECT tmp_lng.*, IFNULL(lng_l.value, tmp_lng.language) AS language,
                   $sql_tbl[images_G].image_path, $sql_tbl[images_G].image_x, $sql_tbl[images_G].image_y
              FROM (
                  SELECT DISTINCT $sql_tbl[language_codes].*, CONCAT('language_', code) AS _language_code
                    FROM $sql_tbl[language_codes]
                   WHERE code IN ('$languages_codes')
             ) AS tmp_lng
              LEFT JOIN $sql_tbl[languages] AS lng_l
                ON lng_l.code               = '$code'
               AND lng_l.name               = tmp_lng._language_code
              LEFT JOIN $sql_tbl[images_G]
                ON $sql_tbl[images_G].id    = tmp_lng.lngid
             ORDER BY language", 'code', FALSE, FALSE);


    if (!empty($languages)) {

        foreach ($languages as $k => $v) {

            $languages[$k]['code'] = $k;
            unset($languages[$k]['_language_code']);

            if (!is_null($v['image_path'])) {
                if ($languages[$k]['is_url'] = is_url($v['image_path'])) {
                    $languages[$k]['tmbn_url'] = $v['image_path'];
                } else {
                    $languages[$k]['tmbn_url'] = func_get_image_url($v['lngid'], 'G', $v['image_path']);
                    $languages[$k]['tmbn_url'] = str_replace($current_location, '', $languages[$k]['tmbn_url']);
                }
            }    
        }

    }

    return $languages;
} // }}}

function func_dc_sql($md5, $query, $type) { // {{{
    switch ($type) {
        case 'first':
            return func_query_first($query);

        case 'first_cell':
            return func_query_first_cell($query);

        case 'column':
            return func_query_column($query);

        default:
            return func_query($query);
    }

    return null;
} // }}}

/*
* Cache categories tree
*/
function func_dc_get_categories_tree($root, $simple, $language, $membershipid) { // {{{
    global $sql_tbl;

    x_load('category');
    return func_get_categories_tree($root, $simple, $language, $membershipid);
} // }}}

function func_dc_get_categories_tree_validate($data) { // {{{
    return !empty($data);
} // }}}

/*
* Cache all languages
*/
function func_dc_get_language_vars($lng_code) { // {{{
    assert('/*'.__FUNCTION__.' @param*/ 
    is_string($lng_code) && !empty($lng_code)');

    global $sql_tbl, $config, $all_languages;

    $lng = array();    

    if (empty($lng_code))
        return $lng;

    $default_language = empty($config['default_customer_language']) ? $config['default_admin_language'] : $config['default_customer_language'];

    if (
        count($all_languages) == 1 
        || $lng_code == $default_language
    ) {
        $labels = db_query("SELECT name, value FROM $sql_tbl[languages] WHERE code = '$lng_code'");
    } elseif(version_compare(X_MYSQL_VERSION, '5.0.1') > 0) {
        // Obtain all languages with $lng_code, add $default_language for empty names. Thanks2Abr.
        db_query("CREATE OR REPLACE VIEW base_lang AS SELECT name,value FROM $sql_tbl[languages] WHERE code ='$lng_code'");
        $labels = db_query("
                SELECT * FROM base_lang
                UNION 
                SELECT name, value
                    FROM $sql_tbl[languages]
                    WHERE code = '$default_language'
                    AND name NOT IN ( SELECT DISTINCT name FROM base_lang)"
        );
    }

    if ($labels) {
        while ($v = db_fetch_array($labels)) {
            $lng[$v['name']] = $v['value'];
        }
        db_free_result($labels);
    }

    assert('/*'.__FUNCTION__.' @return*/
    !empty($lng) && is_array($lng)');
    return $lng;
} // }}}

function func_dc_get_language_vars_validate($data) { // {{{
    return !empty($data);
} // }}}

/*
 Cache offers for category
*/
function func_dc_get_offers_categoryid($categoryid) { // {{{
    global $active_modules;

    if (empty($active_modules['Special_Offers']))
        return NULL;

    return func_get_offers_categoryid($categoryid);
} // }}}

/*
 Cache schemes
*/
function func_dc_get_schemes($xcart_web_dir) { // {{{
    return func_get_schemes($xcart_web_dir);
} // }}}

function func_dc_get_schemes_validate($data) { // {{{
    return !empty($data);
} // }}}

/*
 Cache mySQL server configuration variables
*/
function func_dc_sql_vars() { // {{{
    $variables = array(
        'max_allowed_packet',
        'lower_case_table_names',
        'max_join_size',
        'character_set_client',
        'join_buffer_size',
        'sql_big_selects',
        'wait_timeout',
    );

    if (defined('DEVELOPMENT_MODE')) {
        $variables[] = 'query_cache_type';
        $variables[] = 'slow_query_log';
    }

    // bt#0134112 check if has_private_data=TRUE should be used for new variables
    assert('count($variables) == 9 /* '.__FUNCTION__.' Check if has_private_data=TRUE should be used for new setting*/');

    $variables_list = implode("', '", $variables);
    if (version_compare(X_MYSQL_VERSION, '5.0.3') >= 0)
        return func_query_hash("SHOW VARIABLES WHERE Variable_name in ('$variables_list')", 'Variable_name', FALSE, TRUE);
    else        
        return func_query_hash("SHOW VARIABLES", 'Variable_name', FALSE, TRUE);
} // }}}

/*
 Cache the database structure
*/
function func_dc_sql_tables_fields() { // {{{
    global $sql_tbl;
    $all_tables = func_query_column("SHOW TABLES");

    if (empty($all_tables))
        return FALSE;

    $storage = array();
    foreach ($all_tables as $k => $v) { 
        $storage[strtolower($v)] = func_query_column("SHOW FIELDS FROM `$v`");
    }    

    if (
        empty($storage['xcart_customers'])
        && empty($storage['xcart_orders'])
    ) {
        assert('FALSE /* '.__FUNCTION__.' Mysql storage error*/');
        return FALSE;
    }

    return $storage;
} // }}}

function func_dc_sql_tables_fields_validate($data) { // {{{
    return !empty($data);
} // }}}

/*
 General data cache functions
*/
function func_data_cache_status_check($name) { // {{{
    global $data_caches;

    $func_exists = isset($data_caches[$name]['class']) 
        ? method_exists($data_caches[$name]['class'], $name)
        : function_exists($data_caches[$name]['func']);

    if (
        !isset($data_caches[$name])
        || empty($data_caches[$name]['func'])
        || !$func_exists
        || empty($data_caches[$name]['use_func_cache_logic'])
        || !empty($data_caches[$name]['has_private_data'])
    ) { 
        return FALSE;
    } 

    $no_save = defined('BLOCK_DATA_CACHE_' . strtoupper($name));
    if (
        !defined('USE_DATA_CACHE')
        || !constant('USE_DATA_CACHE')
        || $no_save
    ) {
        return FALSE;
    }

    return TRUE;
} // }}}

function func_save_cache_func($data, $cache_key, $name) { // {{{
    if (!func_data_cache_status_check($name)) {
        return FALSE;
    }

    global $xcart_dir, $data_caches;
    require_once "$xcart_dir/include/classes/class.xc_cache_lite.php";
    $cache_lite = XC_Cache_Lite::get_instance(); 

    $cache_lite->setLifeTime( empty($data_caches[$name]['ttl']) ? 0  : $data_caches[$name]['ttl'] );
    $cache_lite->setCacheDir( empty($data_caches[$name]['dir']) ? '' : $data_caches[$name]['dir'] );
    $cache_lite->setFileNameProtection(!empty($data_caches[$name]['fileNameProtection']));
    $cache_lite->setHashedDirectoryLevel(
        empty($data_caches[$name]['hashedDirectoryLevel']) ? 0 : $data_caches[$name]['hashedDirectoryLevel']
    );


    return $cache_lite->save($data, $cache_key, $name);
} // }}}

function func_get_cache_func($cache_key, $name) { // {{{
    if (!func_data_cache_status_check($name)) {
        return FALSE;
    }

    global $xcart_dir, $data_caches;
    require_once "$xcart_dir/include/classes/class.xc_cache_lite.php";
    $cache_lite = XC_Cache_Lite::get_instance();

    $cache_lite->setLifeTime( empty($data_caches[$name]['ttl']) ? 0  : $data_caches[$name]['ttl'] );
    $cache_lite->setCacheDir( empty($data_caches[$name]['dir']) ? '' : $data_caches[$name]['dir'] );
    $cache_lite->setFileNameProtection(!empty($data_caches[$name]['fileNameProtection']));
    $cache_lite->setHashedDirectoryLevel(
        empty($data_caches[$name]['hashedDirectoryLevel']) ? 0 : $data_caches[$name]['hashedDirectoryLevel']
    );

    return $cache_lite->get($cache_key, $name);
} // }}}

function func_validate_cache_func($name, &$data, $func_is_valid = NULL) { // {{{
    if (empty($func_is_valid)) {
        global $data_caches;

        $func_is_valid = $data_caches[$name]['func']."_validate";
        assert('empty($data_caches[$name]["func_is_valid"]) && !function_exists($func_is_valid) || $data_caches[$name]["func_is_valid"] == $func_is_valid /* '.__FUNCTION__.' */');
    }

    if (function_exists($func_is_valid)) {
        return call_user_func_array($func_is_valid, array($data));
    } else {
        return isset($data);
    }
} // }}}

/**
 * Register function or class method as a cache function in the global cache registry
 *
 * @global type $data_caches - Global cache registry
 *
 * @staticvar array $cache_names - Internal calls cache
 *
 * @param string $cache_name - function name
 * @param array $params - caching params
 *
 * @return void
 */
function func_register_cache_function($cache_name, $params = array()) { // {{{
    global $data_caches;

    static $cache_names = array();

    // Default registration params
    $default = array(
        'class' => '', // Class name
        'func'  => $cache_name, // Function or class method
        'ttl'   => SECONDS_PER_DAY * 30, // 1 month to invalidate cache by default
        'use_func_cache_logic' => true, // Cache logic mode
        'dir'   => '', // Cache directory, should be pre-registered in config.php
        'hashedDirectoryLevel' => 0, // Hashed directory level
    );

    // Make sure cache is assigned only once
    if (!empty($cache_names[$cache_name])) {
        return;
    }

    // Make sure the cache key does not exist
    if (!empty($data_caches[$cache_name])) {
        $method_name = !empty($params['class']) ? $params['class'] . '::' . $cache_name : $cache_name;
        assert('FALSE /* '.__METHOD__.': An attempt to overwrite existing cache function ('.$method_name.') has occured */');
        return;
    }

    if (!empty($params['dir'])) {
        // Inject global vars
        global $var_dirs, $xcart_fs_permissions_map;
        // Setup directory permissions (inherit from var)
        $xcart_fs_permissions_map['var'.XC_DS.'cache'.XC_DS.$params['dir']] = $xcart_fs_permissions_map['var'.XC_DS.'cache'];
        // Setup cache directory path
        $var_dirs[$params['dir']] = $var_dirs['cache'] . "/$params[dir]";
        // Restore cache directory
        func_restore_var_dir($params['dir'], $var_dirs[$params['dir']]);
    }

    // Registration params
    $registration_params = array_merge($default, $params);

    // Set cache
    $data_caches[$cache_name] = $registration_params;

    // Update static calls cache
    $cache_names[$cache_name] = true;
} // }}}

?>
