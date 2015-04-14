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
 * Cloud Search module functions
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Cloud Search
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v18 (xcart_4_7_0), 2015-02-17 23:56:28, func.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if (!defined('XCART_START')) { header('Location: ../../'); die('Access denied');}

define('CLOUD_SEARCH_MAX_ENTITIES_AT_ONCE', 300);
define('CLOUD_SEARCH_DOMAIN', 'cloudsearch.x-cart.com');
define('CLOUD_SEARCH_REGISTER_URL', '/api/v1/register');
define('CLOUD_SEARCH_REQUEST_SECRET_KEY_URL', '/api/v1/getkey');
define('CLOUD_SEARCH_REMOTE_IFRAME_URL', '/api/v1/iframe?key=');
define('CLOUD_SEARCH_REMINDER_URL', '/api/v1/reminder');
define('CLOUD_SEARCH_SEARCH_URL', '/api/v1/search');
define('CLOUD_SEARCH_SESSION_CACHE_LIFETIME', 10 * 60);

function func_cloud_search_entities_at_once()
{
	return CLOUD_SEARCH_MAX_ENTITIES_AT_ONCE;
}

function func_cloud_search_get_products($start, $limit)
{
	global $sql_tbl, $config, $active_modules;

    x_load('category');

    $extraSelect = '';
    $extraJoin = '';

    if (!empty($active_modules['Manufacturers'])) {
        $extraSelect .= ', m.manufacturer';
        $extraJoin .= "LEFT JOIN $sql_tbl[manufacturers] m ON p.manufacturerid = m.manufacturerid AND m.avail = 'Y'";
    }

    $condOutOfStock = func_cloud_search_get_out_of_stock_condition();

    if (
        !empty($active_modules['Product_Options'])
        && strpos($condOutOfStock, 'v.') !== false
    ) {
        $extraJoin .= XCVariantsSQL::getJoinQueryAllRows(array('Variants' => 'v', 'Products' => 'p'));
    }

    if (!empty($config['default_customer_language']) && isset($sql_tbl['products_lng_' . $config['default_customer_language']])) {
        $tbl_products_lng = $sql_tbl['products_lng_' . $config['default_customer_language']];
    } else {
        $tbl_products_lng = $sql_tbl['products_lng_en'];
    }

    $products = func_query("
        SELECT lng.product AS name,
            lng.descr AS description,
            lng.fulldescr AS fullDescription,
            p.productid AS id,
            p.productcode AS sku,
            pricing.price AS price,
            pc.categoryid AS categoryid
            $extraSelect
        FROM $sql_tbl[products] p

        LEFT JOIN $tbl_products_lng lng ON p.productid = lng.productid
        LEFT JOIN $sql_tbl[quick_prices] qp ON qp.productid = p.productid AND qp.membershipid = 0
        LEFT JOIN $sql_tbl[pricing] pricing ON qp.priceid = pricing.priceid
        LEFT JOIN $sql_tbl[products_categories] pc ON p.productid = pc.productid AND pc.main = 'Y'
        $extraJoin

        WHERE p.forsale = 'Y' $condOutOfStock
        GROUP BY p.productid
        ORDER BY p.productid
        LIMIT $start, $limit
    ");

	if (!$products) {
		$products = array();
	}

	foreach ($products as $k => $p) {
		$products[$k]['description'] = $p['fullDescription'] ? $p['fullDescription'] : $p['description'];
		unset($products[$k]['fullDescription']);

		$products[$k]['url'] = func_get_resource_url('P', $p['id']);

		$images = func_get_image_url_by_types(
			array(
				'T' => $p['id'],
				'P' => $p['id'],
			)
		);

		if (is_array($images['images'])) {
			$image = $images['images']['T'] ? $images['images']['T'] : $images['images']['P'];

			$products[$k]['image_src'] = $image['url'];
			$products[$k]['image_width'] = $image['x'];
			$products[$k]['image_height'] = $image['y'];
		}

        $products[$k]['category'] = func_get_category_path($p['categoryid'], 'category');

        $modifiers = array();
        if (!empty($active_modules['Product_Options'])) {
            $classes = func_get_product_classes($p['id']);

            if (is_array($classes)) {

                foreach ($classes as $c) {
                    if (!empty($c) && $c['avail'] == 'Y') {
                        $modifier = $c['classtext'];
                        $values = array();

                        if (is_array($c['options'])) {
                            foreach ($c['options'] as $option) {
                                if ($option['avail'] == 'Y') {
                                    $values[] = $option['option_name'];
                                }
                            }
                        }

                        $modifiers[] = array(
                            'name'  => $c['classtext'],
                            'values' => $values,
                        );
                    }
                }
            }
        }
        $products[$k]['modifiers'] = $modifiers;

        $products[$k]['manufacturer'] = !empty($p['manufacturer']) ? $p['manufacturer'] : '';

        $extraFields = array();
        if (!empty($active_modules['Extra_Fields'])) {
            $extraFields = func_query("
                SELECT ef.field, efv.value
                FROM $sql_tbl[extra_field_values] efv
                JOIN $sql_tbl[extra_fields] ef ON efv.fieldid = ef.fieldid AND ef.active = 'Y'
                WHERE efv.productid = '$p[id]'
            ");
        }
        $products[$k]['extraFields'] = !empty($extraFields) ? $extraFields : array();

	}

	return $products;
}

function func_cloud_search_get_categories($start, $limit)
{
    global $sql_tbl;

    $categories = func_query("
        SELECT categoryid AS id,
            category AS name,
            description,
            parentid AS parent
        FROM $sql_tbl[categories]
        WHERE avail = 'Y'
        ORDER BY categoryid
        LIMIT $start, $limit
    ");

    if (!$categories)
        $categories = array();

    foreach ($categories as $k => $c) {
		$images = func_get_image_url_by_types(
			array(
				'C' => $c['id'],
			)
		);

		if (is_array($images['images'])) {
			$image = $images['images']['C'];

            if (empty($image['is_default'])) {
                $categories[$k]['image_src'] = $image['url'];
                $categories[$k]['image_width'] = $image['x'];
                $categories[$k]['image_height'] = $image['y'];
            }
		}

		$categories[$k]['url'] = func_get_resource_url('C', $c['id']);
    }

    return $categories;
}

function func_cloud_search_get_pages($start, $limit)
{
    global $sql_tbl, $xcart_dir, $smarty_skin_dir, $smarty, $config;

    $pages = func_query("
        SELECT pageid AS id, filename, title, language
        FROM $sql_tbl[pages]
        WHERE active = 'Y'
        ORDER BY pageid
    ");

	if (!$pages)
		$pages = array();

    foreach ($pages as $k => $page) {
        $filename = $xcart_dir . $smarty_skin_dir . '/pages/' . $page['language'] . "/" . $page['filename'];

        if ($config['General']['parse_smarty_tags'] == 'Y') {
            $page_content = func_display($filename, $smarty, false);
        } else {
            $page_content = func_file_get($filename, true);
        }

        if ($page_content !== false) {
            $pages[$k]['content'] = $page_content;

            $pages[$k]['url'] = func_get_resource_url('S', $page['id']);

            unset($pages[$k]['filename']);
            unset($pages[$k]['language']);
        } else {
            unset($pages[$k]);
        }
    }

    return $pages;
}

function func_cloud_search_get_manufacturers($start, $limit)
{
    global $sql_tbl, $active_modules;

    if (empty($active_modules['Manufacturers'])) {
        return array();
    }

    $manufacturers = func_query("
        SELECT manufacturerid AS id,
            manufacturer AS name,
            descr AS description
        FROM $sql_tbl[manufacturers]
        WHERE avail = 'Y'
        ORDER BY manufacturerid
        LIMIT $start, $limit
    ");

    if (!$manufacturers)
        $manufacturers = array();

    foreach ($manufacturers as $k => $m) {
		$images = func_get_image_url_by_types(
			array(
				'M' => $m['id'],
			)
		);

		if (is_array($images['images'])) {
			$image = $images['images']['M'];

            if (empty($image['is_default'])) {
                $manufacturers[$k]['image_src'] = $image['url'];
                $manufacturers[$k]['image_width'] = $image['x'];
                $manufacturers[$k]['image_height'] = $image['y'];
            }
		}

		$manufacturers[$k]['url'] = func_get_resource_url('M', $m['id']);
    }

    return $manufacturers;
}

function func_cloud_search_get_info()
{
    global $sql_tbl, $active_modules;

    $condOutOfStock = func_cloud_search_get_out_of_stock_condition();

    $variantsJoin = !empty($active_modules['Product_Options']) && strpos($condOutOfStock, 'v.') !== false
        ? XCVariantsSQL::getJoinQueryAllRows(array('Variants' => 'v', 'Products' => 'p'))
        : '';

    $numProducts = func_query_first_cell("
        SELECT COUNT(DISTINCT p.productid)
        FROM $sql_tbl[products] p
        $variantsJoin
        WHERE p.forsale = 'Y' $condOutOfStock
    ");

    $numCategories = func_query_first_cell("
        SELECT COUNT(*)
        FROM $sql_tbl[categories]
        WHERE avail = 'Y'
    ");

    $numPages = func_query_first_cell("
        SELECT COUNT(*)
        FROM $sql_tbl[pages]
        WHERE active = 'Y'
    ");

    if (!empty($active_modules['Manufacturers'])) {
        $numManufacturers = func_query_first_cell("
            SELECT COUNT(*)
            FROM $sql_tbl[manufacturers]
            WHERE avail = 'Y'
        ");
    } else {
        $numManufacturers = 0;
    }

	return array(
        'numProducts'		=> intval($numProducts),
        'numCategories'		=> intval($numCategories),
        'numPages'          => intval($numPages),
        'numManufacturers'  => intval($numManufacturers),
        'productsAtOnce'	=> func_cloud_search_entities_at_once(),
    );
}

function func_cloud_search_recursive_chmod($path, $filePerm = 0664, $dirPerm = 0775)
{
    if(file_exists($path)) {
        if(is_file($path)) {
            @chmod($path, $filePerm);

        } elseif(is_dir($path)) {
            @chmod($path, $dirPerm);

            $dh = opendir($path);
            if ($dh) {
                while (false !== ($entry = readdir($dh))) {
                    if ($entry != '.' && $entry != '..') {
                        func_cloud_search_recursive_chmod($path . DIRECTORY_SEPARATOR . $entry, $filePerm, $dirPerm);
                    }
                }
            }
        }
    }
}

function func_cloud_search_chmod_installation()
{
    global $xcart_dir, $smarty_skin_dir;

    func_chmod_file($xcart_dir . '/cloud_search_api.php', 0644);

    func_cloud_search_recursive_chmod($xcart_dir . '/modules/Cloud_Search');
    func_cloud_search_recursive_chmod($xcart_dir . $smarty_skin_dir . '/modules/Cloud_Search');
}

function func_cloud_search_install()
{
    global $xcart_http_host, $xcart_web_dir, $sql_tbl;

    func_cloud_search_chmod_installation();

    x_load('http');

    list($a, $result) = func_http_post_request(
        CLOUD_SEARCH_DOMAIN,
        CLOUD_SEARCH_REGISTER_URL,
        'shopUrl=' . urlencode('http://' . $xcart_http_host . $xcart_web_dir)
        . '&format=php'
    );

    if ($result) {
        $data = unserialize($result);

        if ($data && !empty($data['apiKey'])) {
            db_query("
                UPDATE $sql_tbl[config]
                SET value = '" . addslashes($data['apiKey']) . "'
                WHERE name = 'cloud_search_api_key'
            ");
        }
        
        if ($data && !empty($data['remindDates'])) {
            $dates = $data['remindDates'];

            db_query("
                REPLACE INTO $sql_tbl[config]
                (name, value, category, defvalue, variants) VALUES
                ('cloud_search_remind_dates', '" . addslashes(serialize($dates)) . "', '', '', '')
            ");
        }
    }

    return $data;
}

function func_cloud_search_api_output($data)
{
	header('Content-type: application/php');

    echo serialize($data);
}

function func_cloud_search_init()
{
	global $smarty, $config, $PHP_SELF, $option, $store_currency, $active_modules;

    if (defined('ADMIN_MODULES_CONTROLLER')) {
        func_add_event_listener('module.ajax.toggle', 'func_cloud_search_on_module_toggle');
    }

    if (defined('AREA_TYPE') && constant('AREA_TYPE') == 'A') {
        if (basename($PHP_SELF) == 'configuration.php' && $option == 'Cloud_Search') {
            func_cloud_search_configuration();
        }
    }

    if (!empty($active_modules['XMultiCurrency'])) {
        $currency = func_mc_get_currency($store_currency);

        if ($currency && !$currency['is_default'] && $currency['rate'] > 0) {
            $smarty->assign('cloud_search_currency_rate', $currency['rate']);
        }   
    }

    func_cloud_search_check_tasks();

    $smarty->assign('cloud_search_dynamic_prices', func_cloud_search_dynamic_prices());
}

function func_cloud_search_dynamic_prices()
{
    global $sql_tbl;

    $hasTaxes = func_query_first_cell("
        SELECT COUNT(*)
        FROM $sql_tbl[taxes]
        WHERE active = 'Y'
    ") > 0;

    return $hasTaxes;
}

function func_cloud_search_adjust_search_params()
{
    global $where, $inner_joins, $search_data, $sql_tbl, $cloud_search_temp_table_sort;

    if (func_cloud_search_is_customer_search_controller()) {
        if (!empty($search_data['products']) && !empty($search_data['products']['substring'])) {
            $q = $search_data['products']['substring'];

            $pids = func_cloud_search_get_all_ids_cached($q);

            if ($pids !== null) {
                $table_created = db_query("
                    CREATE TEMPORARY TABLE $sql_tbl[cs_search_pids](
                        id INT(11),
                        rank INT(11) AUTO_INCREMENT,
                        KEY (id), KEY (rank)
                    )
                ");

                if ($table_created) {
                    db_query("
                        INSERT INTO $sql_tbl[cs_search_pids]
                        (id) VALUES (" . implode('),(', $pids) . ")
                    ");

                    $pidsHashComment = ' /* ' . md5(implode(',', $pids)) . ' */';

                    $inner_joins['cs_search_pids'] = array(
                        'on' => "$sql_tbl[products].productid = $sql_tbl[cs_search_pids].id" . $pidsHashComment
                    );

                } else {
                    $where[] = "$sql_tbl[products].productid IN (" . implode(',', $pids) . ')';
                }

                $cloud_search_temp_table_sort = $table_created;

                $search_data['products']['substring'] = '';
                $search_data['products']['cloud_search_substring'] = $q;
            }
        }
    }
}

function func_cloud_search_restore_search_params()
{
    global $search_data, $orderbys, $sql_tbl, $cloud_search_temp_table_sort, $data;

    if (func_cloud_search_is_customer_search_controller()) {
        if (
            !empty($search_data['products'])
            && !empty($search_data['products']['cloud_search_substring'])
        ) {
            $q = $search_data['products']['substring'] = $search_data['products']['cloud_search_substring'];
            unset($search_data['products']['cloud_search_substring']);

            $pids = func_cloud_search_get_all_ids_cached($q);

            if (
                empty($data['sort_field'])
                || $data['sort_field'] == 'orderby'
                || $data['sort_field'] == 'title'
            ) {
                if ($pids !== null) {
                    if ($cloud_search_temp_table_sort) {
                        $orderbys = array("$sql_tbl[cs_search_pids].rank");
                    } else {
                        $orderbys = array("FIELD($sql_tbl[products].productid, " . implode(',', $pids) . ')');
                    }
                }
            }
        }
    }
}

function func_cloud_search_is_customer_search_controller()
{
    global $PHP_SELF;

    $name = basename($PHP_SELF);
    
    $isSearchPhp = $name == 'search.php';
    
    $isInfScroll = $name == 'get_block.php'
        && $_GET['block'] == 'infs_widget'
        && $_GET['infs_widget_object_type'] == 'search';
    
    return defined('AREA_TYPE') && constant('AREA_TYPE') == 'C' && ($isSearchPhp || $isInfScroll);
}

function func_cloud_search_cache_set($key, $value)
{
    global $cloud_search_cache;

    x_session_register('cloud_search_cache', array());

    $cloud_search_cache[$key] = array(
        'timestamp' => XC_TIME,
        'value' => $value,
    );
}

function func_cloud_search_cache_get($key)
{
    global $cloud_search_cache;

    x_session_register('cloud_search_cache', array());

    $result = null;

    if (
        isset($cloud_search_cache[$key])
        && $cloud_search_cache[$key]['timestamp'] + constant('CLOUD_SEARCH_SESSION_CACHE_LIFETIME') > XC_TIME
    ) {
        $result = $cloud_search_cache[$key]['value'];
    }

    return $result;
}

function func_cloud_search_get_all_ids_cached($q)
{
    $ids = null;

    if (($cached = func_cloud_search_cache_get('last_request')) !== null) {
        if ($cached['key'] == $q) {
            $ids = $cached['val'];
        }
    }

    if ($ids === null) {
        $ids = func_cloud_search_get_all_ids($q);
    }

    func_cloud_search_cache_set('last_request', array('key' => $q, 'val' => $ids));

    return $ids;
}

function func_cloud_search_get_all_ids($q)
{
    global $config;

    x_load('http');

    list(, $json) = func_http_get_request(
        CLOUD_SEARCH_DOMAIN,
        CLOUD_SEARCH_SEARCH_URL,
        'apiKey=' . urlencode($config['cloud_search_api_key']) . '&q=' . urlencode($q) . '&all=1'
    );

    $result = null;

    $decoded = json_decode($json, true);

    if (is_array($decoded) && !empty($decoded['products'])) {
        $result = array();
        
        foreach ($decoded['products'] as $p) {
            $result[] = (int)$p['id'];
        }
    }

    return $result;
}

function func_cloud_search_configuration()
{
    global $remote_iframe;

    if (!empty($remote_iframe))
        func_cloud_search_remote_iframe();
}

function func_cloud_search_remote_iframe()
{
    global $sql_tbl, $config;

    x_load('http');

    list($a, $result) = func_http_post_request(
        CLOUD_SEARCH_DOMAIN,
        CLOUD_SEARCH_REQUEST_SECRET_KEY_URL,
        'apiKey=' . urlencode($config['cloud_search_api_key'])
    );

    $secretKey = func_query_first_cell("
        SELECT value
        FROM $sql_tbl[config]
        WHERE name = 'cloud_search_secret_key'
    ");

    func_header_location('https://' . CLOUD_SEARCH_DOMAIN . CLOUD_SEARCH_REMOTE_IFRAME_URL . $secretKey);
}

function func_cloud_search_set_secret_key()
{
    global $REQUEST_METHOD;

    if ($REQUEST_METHOD == 'POST' && !empty($_POST['key']) && !empty($_POST['signature'])) {

        $secretKey = $_POST['key'];
        $signature = base64_decode($_POST['signature']);

        if (func_cloud_search_check_service_ip()
            || func_cloud_search_check_key_signature($secretKey, $signature)) {

            func_array2insert(
                'config',
                array(
                    'name'  => 'cloud_search_secret_key',
                    'value' => $secretKey,
                ),
                true
            );
        }
    }

    return array();
}

function func_cloud_search_check_key_signature($secretKey, $signature)
{
    if (function_exists('openssl_get_publickey')) {
        $pubkeyid = openssl_get_publickey(func_cloud_search_get_public_api_key());

        return openssl_verify($secretKey, $signature, $pubkeyid) == 1;
    } else {
        return false;
    }
}

function func_cloud_search_get_public_api_key()
{
    return '-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQC+sJv3R+kKUl0okgi7HoN6sGcM
4Lyp4LMkMYqwD0hK618lJwydI5PRMj3+vmCxVZcnoiAM/8XwGmH24y2s7D2/8/co
K55PFPn6T0V5++5oyyObofPe08kDoW6Ft2+yNcshmg1Vd711Vd37LLXWsaWpfcjr
82cfYTelfejE4IO5NQIDAQAB
-----END PUBLIC KEY-----';
}

function func_cloud_search_check_service_ip()
{
    return in_array($_SERVER['REMOTE_ADDR'], array('78.46.67.123'));
}

function func_cloud_search_on_module_toggle($module_name, $active)
{
    global $smarty;

    if ($module_name == 'Cloud_Search' && $active) {
        func_remove_xcart_caches(TRUE, func_get_cache_dirs());

        func_cloud_search_install();

        return 'configuration.php?option=Cloud_Search';
    }
}

function func_cloud_search_check_tasks()
{
    global $config, $smarty, $sql_tbl, $PHP_SELF, $REQUEST_METHOD, $mode;

    x_load('http');

    if (!empty($config['cloud_search_schedule_reg']) && $config['cloud_search_schedule_reg'] == 'Y') {
        func_array2update('config', array('value' => 'N'), "name = 'cloud_search_schedule_reg'");

        func_cloud_search_install();
    }

    if (defined('AREA_TYPE') && constant('AREA_TYPE') == 'A' && basename($PHP_SELF) == 'home.php') {

        if (!empty($config['cloud_search_remind_dates'])) {
            $remindDatesRange = isset($config['cloud_search_remind_dates_range']) ?
                $config['cloud_search_remind_dates_range'] : -1;

            if ($REQUEST_METHOD == 'GET') {
                $remindDates = unserialize($config['cloud_search_remind_dates']);

                foreach ($remindDates as $k => $date) {
                    if ($k > $remindDatesRange && $date['from'] < XC_TIME && $date['to'] > XC_TIME) {

                        list($a, $result) = func_http_get_request(
                            CLOUD_SEARCH_DOMAIN,
                            CLOUD_SEARCH_REMINDER_URL,
                            'apiKey=' . urlencode($config['cloud_search_api_key'])
                            . '&period=' . $k
                        );

                        if ($result) {
                            $smarty->assign('cloud_search_reminder', $result);
                        }
                    }
                }

            } else {

                if ($mode == 'cloud_search_reminder_dismiss') {

                    $remindDatesRange++;

                    db_query("
                        REPLACE INTO $sql_tbl[config]
                        (name, value, category, defvalue, variants) VALUES
                        ('cloud_search_remind_dates_range', '$remindDatesRange', '', '', '')
                    ");

                    func_header_location('home.php');
                }
            }
        }
    }
}

function func_cloud_search_get_out_of_stock_condition()
{
    global $config, $active_modules;

    $showOutOfStock = isset($config['General']['show_outofstock_products']) && $config['General']['show_outofstock_products'] == 'Y';

    if ($showOutOfStock)
        $cond = '';
    elseif (!empty($active_modules['Product_Options']))
        $cond = 'AND ' . XCVariantsSQL::getVariantField('avail', array('Variants' => 'v', 'Products' => 'p')) . ' > 0 ';
    else
        $cond = ' AND p.avail > 0';

    return $cond;
}

function func_cloud_search_get_prices($ids)
{
    global $user_account;

    $ids = array_map(intval, explode(',', $ids));

    $result = array();

    foreach ($ids as $id) {
        $product = func_select_product(
            $id,
            isset($user_account['membershipid']) ? $user_account['membershipid'] : 0,
            false
        );

        $result[] = $product ? $product['taxed_price'] : null;
    }

    return $result;
}

function func_cloud_search_output_json($data)
{
	header('Content-type: application/json');

    echo json_encode($data);
}
