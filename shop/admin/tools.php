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
 * Service tools
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Admin interface
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    8d292860d80cf1a776892a05d37ee5b91f587546, v213 (xcart_4_7_1), 2015-03-10 12:34:19, tools.php, aim
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

define('TOOLS', true);
define('BENCH_BLOCK', true);
define('XC_TOOLS_LIMIT_QUERY_ROWS', 5);
define('XC_TOOLS_LIMIT_ERR_COUNT', 1000);

require __DIR__.'/auth.php';
require $xcart_dir.'/include/security.php';

x_load('backoffice', 'category', 'crypt', 'order', 'iterations', 'user');

x_session_register('err_store');

set_time_limit(86400);

$anchors = array();

if ($config['SEO']['clean_urls_enabled'] == 'Y') {
    $anchors['generate_clean_urls'] = 'lbl_generate_clean_urls';
}

$regenerate_dpicons_allowed = !empty($active_modules['Detailed_Product_Images']) && func_check_gd();

$reslice_zimages_allowed = !empty($active_modules['Magnifier']) && (func_get_iteration_length("reslice", "import") > 0);

$generate_thumbnails_allowed = func_get_iteration_length('generate') > 0;

$_anchors = array(
    'clearcc'     => 'txt_credit_card_information_removal',
    'authmodelnk' => 'lbl_change_authmode',
    'optimdb'     => 'lbl_optimize_tables',
    'integrdb'    => 'lbl_check_database_integrity',
    'gencache'    => 'lbl_force_cache_generation',
    'clearstat'   => 'lbl_statistics_clearing',
    'cleartmp'    => 'lbl_clear_templates_cache',
    'cleartmpdir' => 'lbl_clear_tmp_dir',
    'catindex'    => 'lbl_rebuild_category_indexes',
    'check_tpl_syntax' => 'lbl_check_tpl_syntax',
);

if ($regenerate_dpicons_allowed) {
    $_anchors['regendpicons'] = 'lbl_image_cache_regenerate';
}

if ($reslice_zimages_allowed) {
    $_anchors['reslicezimages'] = 'lbl_reslice_zimages';
}

if ($generate_thumbnails_allowed) {
    $_anchors['generatethumbnails'] = 'lbl_generate_thumbnails';
}

if (!empty($active_modules['Magnifier']))
    $_anchors['resliceall'] = 'lbl_reslice_all';

$count_orders2generate_anonymous_customers = func_query_first_cell("SELECT COUNT(*) FROM $sql_tbl[orders] WHERE all_userid=0 AND email!=''");
if ($count_orders2generate_anonymous_customers > 0) {
    // Has an orders without appropriate row in xcart_customers table
    $_anchors['generate_anonymous_customers'] = 'lbl_generate_anonymous_customers';
    $smarty->assign('count_orders2generate_anonymous_customers', $count_orders2generate_anonymous_customers);
}

$_anchors['regenbk'] = 'lbl_regenerating_blowfish_key';
$_anchors['cleardb'] = 'lbl_remove_test_data';

$max_order_id = intval(func_query_first_cell("SELECT MAX(orderid) FROM $sql_tbl[orders]"));
$allow_to_increase_init_orderid = ($max_order_id < 500000);

if ($allow_to_increase_init_orderid) {
    $_anchors['increase_init_orderid'] = 'lbl_increase_init_orderid';
}

$anchors = func_array_merge($anchors, $_anchors);

foreach ($anchors as $anchor => $anchor_label) {

    $dialog_tools_data['left'][] = array(
        'link'  => "#" . $anchor,
        'title' => func_get_langvar_by_name($anchor_label)
    );

}

$dialog_tools_data['right'][] = array(
    'link'  => $xcart_web_dir . DIR_ADMIN . '/general.php',
    'title' => func_get_langvar_by_name('lbl_summary')
);

$dialog_tools_data['right'][] = array(
    'link'  => $xcart_web_dir . DIR_ADMIN . '/snapshots.php',
    'title' => func_get_langvar_by_name('lbl_snapshots')
);

$dialog_tools_data['right'][] = array(
    'link'  => $xcart_web_dir . DIR_ADMIN . '/logs.php',
    'title' => func_get_langvar_by_name('lbl_shop_logs')
);

$dialog_tools_data['right'][] = array(
    'link'  => $xcart_web_dir . DIR_ADMIN . '/user_access_control.php',
    'title' => func_get_langvar_by_name('lbl_user_access_control')
);

function test_templates_cache()
{
    global $smarty;

    return func_get_dir_status($smarty->getCompileDir(), true);
}

function test_tmp_dir()
{
    global $var_dirs;

    return func_get_dir_status($var_dirs['tmp'], true);
}

/**
 * Translation table alias to table name
 */
function func_trans_tbl_name($data, $added = array())
{
    global $sql_replace;

    if (empty($data))
        return $data;

    $replace = $sql_replace;

    if (!empty($added)) {

        foreach ($added as $alias => $name) {

            $replace["/(^|[^\w\d_])" . preg_quote($alias, '/') . "(\.|$)/S"] = "\\1" . $name . "\\2";

        }

        $replace = array_reverse($replace);
    }

    return preg_replace(array_keys($replace), $replace, $data);
}

/**
 * Recrypt all encrypted data
 */
function func_recrypt_data($bf_crypted_tables, $ingnored_encryption_types, $new_blowfish_key) { // {{{
    global $sql_tbl;

    $bf_crypted_tables['reset_passwords'] = array(
        'key' => array('userid'),
        'fields' => array('password_reset_key')
    );

    $bf_crypted_tables['old_passwords'] = array(
        'key' => array('id'),
        'fields' => array('password')
    );

    if (func_is_defined_module_sql_tbl('XAuth', 'xauth_user_ids')) {
        $bf_crypted_tables['xauth_user_ids'] = array(
            'key' => array('auth_id'),
            'fields' => array('identifier')
        );
    }

    if (func_is_defined_module_sql_tbl('PayPal_Login', 'lwpp')) {
        $bf_crypted_tables['lwpp'] = array(
            'key' => array('userid', 'payerId'),
            'fields' => array('openid_identity')
        );
    }

    foreach ($bf_crypted_tables as $tbl => $s) {

        if (
            !isset($sql_tbl[$tbl])
            || empty($s['fields'])
            || empty($s['key'])
        ) {
            continue;
        }

        // Get data
        $opt_where = '';

        if (!empty($s['where'])) {

            $opt_where = (isset($s['use_where']) && ($s['use_where'] == 'Y'))
                ? $s['where']
                : '';

            $s['where'] = " WHERE 1 " . $s['where'];

        } else {

            $s['where'] = '';

        }

        if (!is_array($s['key']))
            $s['key'] = array($s['key']);

        $data = db_query("SELECT " . implode(", ", $s['key']) . ", " . implode(", ", $s['fields']) . " FROM " . $sql_tbl[$tbl] . $s['where']);

        if (!$data)
            continue;

        while ($row = db_fetch_array($data)) {

            $keys = array();
            $key_where = '';
            foreach ($s['key'] as $key) {
                $key_value = array_shift($row);
                $keys[] = $key . " = '" . addslashes($key_value) . "'" ;
            }
            $key_where = implode(" AND ", $keys);

            foreach ($row as $fname => $fvalue) {

                // Check field crypt type
                $type = substr($fvalue, 0, 1);

                if (in_array($type, $ingnored_encryption_types)) {
                    unset($row[$fname]);
                    continue;
                }

                // Decrypt with old (default) key
                $row[$fname] = text_decrypt($row[$fname]);

                // If data is empty (null or false or empty) - pass field
                if (empty($row[$fname])) {

                    unset($row[$fname]);

                    continue;
                }

                // Crypt with new key
                $row[$fname] = addslashes(text_crypt($row[$fname], false, $new_blowfish_key));
            }

            if (empty($row))
                continue;

            // Update row
            func_array2update(
                $tbl,
                $row,
                $key_where . $opt_where
            );

            func_flush(". ");
        }

        db_free_result($data);

    }

    return true;
} // }}}

$location[] = array(func_get_langvar_by_name('lbl_maintenance'), '');

if (@$_GET['mode'] == 'templates') {
    require $xcart_dir . '/include/safe_mode.php';

    if ($config['General']['skip_check_compile'] == 'Y') {
        $result = func_remove_xcart_caches(false, array('cache', 'templates_c'));
    } else {
        $result = func_remove_xcart_caches(false, func_get_cache_dirs());
    }

    $top_message['content'] = func_get_langvar_by_name('msg_adm_summary_templates_del', array('compile_dir' => $smarty->getCompileDir(), 'cache_dir' => $var_dirs['cache']));

    if ($result['is_large']) {

        $top_message['content'] .= func_get_langvar_by_name('msg_adm_files_del_incompleted');

    }

    func_header_location('tools.php');
}

if (@$_GET['mode'] == 'tmpdir') {

    $result = func_rm_dir($var_dirs['tmp'], true);

    $top_message['content'] = func_get_langvar_by_name('msg_adm_summary_tmpdir_del') . " '" . $var_dirs["tmp"] . "'";

    if ($result['is_large']) {

        $top_message['content'] .= func_get_langvar_by_name('msg_adm_files_del_incompleted');

    }

    func_header_location('tools.php');
}

if (
    $REQUEST_METHOD == 'POST'
    && isset($mode_clear)
) {
// {{{
    require $xcart_dir.'/include/safe_mode.php';

    $updates = array();

    $rsd_limit = 0;

    if (
        $rsd_date == 's'
        && !empty($RSD_Day)
    ) {

        $rsd_limit = mktime(0, 0, 0, date($RSD_Month), date($RSD_Day), date($RSD_Year));

    }

    $rsd_limit = intval($rsd_limit);


    if ($_POST['adaptive_stat'] == 'Y') {

        // Delete ALL adaptive statistics from the database

        if (empty($rsd_limit)) {

            db_query("DELETE FROM $sql_tbl[stats_adaptive]");

        } else {

            db_query("DELETE FROM $sql_tbl[stats_adaptive] WHERE last_date < '$rsd_limit'");

        }

        $updates[] = func_get_langvar_by_name('msg_adm_summary_adaptive_stat_del');
    }

    if ($_POST['search_stat'] == 'Y') {

        // Delete ALL search statistics from the database

        if (empty($rsd_limit)) {

            db_query("DELETE FROM $sql_tbl[stats_search]");

        } else {

            db_query("DELETE FROM $sql_tbl[stats_search] WHERE date < '$rsd_limit'");

        }

        $updates[] = func_get_langvar_by_name('msg_adm_summary_search_stat_del');
    }

    if ($_POST['remove_ccinfo_orders'] == 'Y') {

        func_display_service_header('txt_credit_card_information_removal');

        func_flush(
            "<br />&nbsp;&nbsp;"
            . func_get_langvar_by_name('lbl_remove_from_completed_orders', NULL, false, true)
        );

        // Delete credit card information from completed and processed orders

        $orders = db_query("SELECT orderid, details FROM $sql_tbl[orders] WHERE status IN ('P','C')");

        while ($order = db_fetch_array($orders)) {

            $details = text_decrypt($order['details']);

            if (is_string($details)) {

                $details = func_order_remove_ccinfo($details, ($_POST['save_4_numbers'] == "Y"));
                $details = addslashes(func_crypt_order_details($details));

                func_array2update(
                    'orders',
                    array(
                        'details' => $details,
                    ),
                    "orderid = '$order[orderid]'"
                );

                func_flush(". ");
            }

        }

        echo func_get_langvar_by_name('lbl_done', NULL, false, true) . "<br />\n";

        db_free_result($orders);

        $updates[] = func_get_langvar_by_name('msg_adm_summary_ccinfo_orders_del');

        x_log_flag(
            'log_activity',
            'ACTIVITY',
            "'$login' user has removed CC information from completed and processed orders"
        );

    }

    if ($_POST['remove_ccinfo_orders_all'] == 'Y') {

        func_display_service_header('txt_credit_card_information_removal');

        func_flush("<br />&nbsp;&nbsp;" . func_get_langvar_by_name('lbl_remove_from_all_orders', NULL, false, true));

        // Delete credit card information from all orders

        $orders = db_query("SELECT orderid, details FROM $sql_tbl[orders]");

        while ($order = db_fetch_array($orders)) {

            $details = text_decrypt($order['details']);

            if (is_string($details)) {

                $details = func_order_remove_ccinfo($details, ($_POST['save_4_numbers'] == "Y"));
                $details = addslashes(func_crypt_order_details($details));

                func_array2update(
                    'orders',
                    array(
                        'details' => $details,
                    ),
                    "orderid = '$order[orderid]'"
                );

                echo ". ";

            }

        }

        echo func_get_langvar_by_name('lbl_done', NULL, false, true) . "<br />\n";

        db_free_result($orders);

        $updates[] = func_get_langvar_by_name('msg_adm_summary_ccinfo_orders_del_all');

        x_log_flag(
            'log_activity',
            'ACTIVITY',
            "'$login' user has removed CC information from all orders"
        );

    }

    if (
        $_POST['bench_stat'] == 'Y'
        && empty($rsd_limit)
    ) {

        // Delete ALL benchmark pages from database and records from file system

        db_query("DELETE FROM $sql_tbl[benchmark_pages]");

        $dir = @opendir($path);

        if ($dir) {

            $re_bench_files = '/' . preg_quote(constant('BENCH_FILE_PREFIX'), '/') . "(\d{6})\.php/S";

            while ($file = readdir($dir)) {

                if (
                    $file == '.'
                    || $file == '..'
                    || !preg_match($re_bench_files, $file, $match)
                ) {
                    continue;
                }

                @unlink($path.'/'.$file);
            }

            closedir($dir);
        }

        $updates[] = func_get_langvar_by_name('msg_adm_summary_adaptive_stat_del');
    }

    if (
        !empty($active_modules['XAffiliate'])
        && $_POST['xaff_stat'] == 'Y'
    ) {

        $updates[] = func_clear_stats_xaff($rsd_limit);

    }

    if (
        is_array($updates)
        && !empty($updates)
    ) {
        $top_message['content'] = implode("\n<br /><br />\n", $updates);
        $top_message['type']     = "I";
    }

    func_header_location('tools.php');

// }}}
} elseif (isset($mode_optimize)) {
// {{{

    // Optimize table
    require $xcart_dir.'/include/safe_mode.php';
    func_display_service_header('lbl_optimizing_tables_');

    func_optimize_table(false, 1);

    $top_message['content'] = func_get_langvar_by_name("lbl_table_optimization_successfully");
    $top_message['type']     = "I";

    func_html_location('tools.php');

// }}}
} elseif (isset($mode_clear_db)) {
// {{{

    // Clear DB

    require $xcart_dir.'/include/safe_mode.php';

    $tbls_to_delete = array();

    $unsuccessful_remove = false;

    if (empty($clear_db)) {

        $top_message['content'] = func_get_langvar_by_name("lbl_remove_test_data_alert");
        $top_message['type']     = "E";

        func_html_location('tools.php');

    }

    func_display_service_header('lbl_removing_data_');
    foreach ($clear_db as $key => $value) {

        if (
            !empty($tbl_demo_data)
            && isset($tbl_demo_data[$key])
            && $value == 'Y'
        ) {

            foreach ($tbl_demo_data[$key] as $table_name => $condition) {

                if ($condition == 'images') {

                    func_delete_images(str_replace('images_', '', $table_name));

                } else {

                    $tbls_to_delete[$table_name] = (!empty($condition) ? $condition : '');

                }

            }

        }

    }

    if (
        @$clear_db['products'] == 'Y'
        || @$clear_db['prod_cat'] == 'Y'
    ) {
        x_load('product');

        $update_categories = true;

        if (@$clear_db['prod_cat'] == 'Y') {

            $update_categories = false;

            $tbls_to_delete['categories'] = '';
            $tbls_to_delete['categories_lng'] = '';
            $tbls_to_delete['categories_subcount'] = '';
            $tbls_to_delete['category_memberships'] = '';
            $tbls_to_delete['clean_urls'] = "resource_type = 'C'";

            if (func_is_defined_module_sql_tbl('Special_Offers', 'offer_bonus_params')) {
                $tbls_to_delete['offer_bonus_params'] = "param_type = 'C'";
                $tbls_to_delete['offer_condition_params'] = "param_type = 'C'";
            }

            func_delete_images('C');

        }

        func_delete_product('', $update_categories, true);

    } else {

        if (isset($tbls_to_delete['manufacturers'])) {

            db_query("UPDATE $sql_tbl[products] SET manufacturerid = '0'");

        }

        if (isset($tbls_to_delete['variants'])) {
            x_load_module('Product_Options'); // For class XCVariants*

            if (isset($tbls_to_delete['pricing']))
                $tbls_to_delete['pricing'] .= " OR " . XCVariantsSQL::isVariantsPrice();
            else    
                $tbls_to_delete['pricing'] = XCVariantsSQL::isVariantsPrice();

        }

    }

    if (@$clear_db['orders'] == 'Y') {

        $tbls_to_delete['orders'] = '';
        $tbls_to_delete['order_details'] = '';
        $tbls_to_delete['order_extras'] = '';
        $tbls_to_delete['giftcerts'] = '';
        $tbls_to_delete['shipping_labels'] = '';

        if (func_is_defined_module_sql_tbl('XAffiliate', 'partner_payment')) {
            $tbls_to_delete['partner_payment'] = '';
            $tbls_to_delete['partner_product_commissions'] = '';
            $tbls_to_delete['partner_adv_orders'] = '';
        }

        if (func_is_defined_module_sql_tbl('RMA', 'returns')) {
            $tbls_to_delete['returns'] = '';
        }

        if (func_is_defined_module_sql_tbl('Advanced_Order_Management', 'order_status_history')) {
            $tbls_to_delete['order_status_history'] = '';
        }
    }

    if (@$clear_db['stat_pages'] == 'Y') {

        $pages_data = func_query("SELECT * FROM $sql_tbl[pages]");

        if (!empty($pages_data)) {

            foreach ($pages_data as $page_data) {

                if ($page_data['level'] == 'R') {

                    $page_dir = $xcart_dir.XC_DS;
                    @unlink($page_dir.$page_data['filename']);

                } else {

                    foreach ($all_languages as $k => $v) {

                        $page_dir = $xcart_dir
                            . $smarty_skin_dir
                            . XC_DS
                            . 'pages'
                            . XC_DS
                            . $v['code']
                            . XC_DS;

                        @unlink($page_dir . $page_data['filename']);

                    }

                }

            }

        }

        $tbls_to_delete['pages'] = '';
        $tbls_to_delete['clean_urls_history'] = "resource_type = 'S'";
        $tbls_to_delete['clean_urls'] = "resource_type = 'S'";

    }

    if (@$clear_db['discounts'] == 'Y') {

        $tbls_to_delete['discounts'] = '';
        $tbls_to_delete['discount_memberships'] = '';

    }

    if (@$clear_db['ship_data'] == 'Y') {

        $tbls_to_delete['shipping_rates'] = '';
        $tbls_to_delete['shipping_cache'] = '';
        $tbls_to_delete['packages_cache'] = '';

    }

    if (@$clear_db['clean_urls'] == 'Y') {

        $tbls_to_delete['clean_urls'] = '';
        $tbls_to_delete['clean_urls_history'] = '';

    }

    if (
        isset($tbls_to_delete['giftreg_events'])
        && !isset($tbls_to_delete['wishlist'])
    ) {

        db_query("UPDATE $sql_tbl[wishlist] SET event_id = '0'");

    }

    if (
        !empty($tbls_to_delete)
        && is_array($tbls_to_delete)
    ) {

        foreach ($tbls_to_delete as $tbl => $condition) {

            if (isset($sql_tbl[$tbl])) {

                $prev_mysql_error_count = $mysql_error_count;

                db_query("DELETE FROM " . $sql_tbl[$tbl] . (!empty($condition) ? " WHERE " . $condition : ''));

                func_flush(". ");

                if ($prev_mysql_error_count != $mysql_error_count) {

                    $unsuccessful_remove = true;

                }

            } else {

                $unsuccessful_remove = true;

            }

        }

    }

    func_build_quick_prices();

    $dir = @opendir($var_dirs['cache']);

    if ($dir) {

        while ($file = readdir($dir)) {

            if (
                $file == '.'
                || $file == '..'
                || !preg_match("/\.(php|js)$/Ss", $file)
            ) {
                continue;
            }

            @unlink($var_dirs['cache'] . XC_DS . $file);

            func_flush(". ");

        }

        closedir($dir);

    }

    if ($unsuccessful_remove) {

        $top_message['content'] = func_get_langvar_by_name("lbl_remove_test_data_unsuccessfully");
        $top_message['type']     = "E";

    } else {

        $top_message['content'] = func_get_langvar_by_name("lbl_remove_test_data_successfully");
        $top_message['type']     = "I";

    }

    func_html_location('tools.php');

// }}}
} elseif (
    isset($mode_rebuild)
    && !empty($active_modules['Product_Options'])
    && function_exists('func_rebuild_variants')
) {
// {{{

    // Rebuild product Variants
    require $xcart_dir . '/include/safe_mode.php';

    $pids = db_query("SELECT productid FROM $sql_tbl[products]");

    if ($pids) {

        $i = 0;

        func_display_service_header('lbl_rebuild_variants');

        while ($pid = db_fetch_array($pids)) {

            $i++;

            func_rebuild_variants($pid['productid'], true, 0);

            if ($i % 10 == 0) {

                func_flush(". ");
            }

        }

        db_free_result($pids);

    }

    func_html_location('tools.php');

// }}}
} elseif (isset($mode_clear_cache)) {
// {{{

    // Clear data cache

    require $xcart_dir.'/include/safe_mode.php';

    func_display_service_header('lbl_clear_data_cache');

    func_remove_xcart_caches(TRUE, array('cache'));
    $dir = @opendir($var_dirs['cache']);

    if ($dir) {

        while ($file = readdir($dir)) {

            if (
                $file == '.'
                || $file == '..'
                || !preg_match("/\.(php|js)$/S", $file)
            ) {
                continue;
            }

            @unlink($var_dirs['cache'].XC_DS.$file);

            func_flush(". ");

        }

    }

    func_flush("<br />\n");

    func_build_quick_flags(false, 100);
    func_flush("<br />\n");

    func_build_quick_prices(false, 100);
    func_flush("<br />\n");

    XCProducts_CategoriesChange::repairIntegrity();

    func_recalc_subcat_count(false, 10);

    if (
        !empty($active_modules['Flyout_Menus'])
        && function_exists('func_fc_build_categories')
        && func_fc_use_cache()
    ) {

        func_fc_remove_cache(10);
        func_flush("<br />\n");

        func_fc_build_categories(false, 10);
        func_flush("<br />\n");
    }

    db_query("DELETE FROM $sql_tbl[packages_cache]");
    db_query("DELETE FROM $sql_tbl[shipping_cache]");

    $top_message['content'] = func_get_langvar_by_name("lbl_cache_generation_successfully");
    $top_message['type']     = "I";

    func_html_location('tools.php');

// }}}
} elseif (isset($mode_check_integrity)) {
// {{{

    // Check DB integrity

    require $xcart_dir.'/include/safe_mode.php';

    $tbl_keys['categories.parentid'] = array(
        'keys'         => array(
            'categories.parentid' => 'categories.categoryid',
        ),
        'where'     => "categories.parentid != '0'",
        'fields'     => array(
            'categoryid',
            'category',
        ),
    );

    $tbl_keys['categories_lng.categoryid'] = array(
        'keys'         => array(
            'categories_lng.categoryid' => 'categories.categoryid',
        ),
        'fields'     => array(
            'categoryid',
        ),
    );

    $tbl_keys['categories_lng.code'] = array(
        'keys'         => array(
            'categories_lng.code' => 'language_codes.code',
        ),
        'fields'     => array(
            'categoryid',
        ),
        'type'         => 'W'
    );

    $tbl_keys['categories_subcount.categoryid'] = array(
        'keys'         => array(
            'categories_subcount.categoryid' => 'categories.categoryid',
        ),
        'fields'     => array(
            'categoryid',
        ),
    );

    $tbl_keys['categories_subcount.memberships'] = array(
        'keys'         => array(
            'categories_subcount.membershipid' => 'memberships.membershipid',
        ),
        'on'         => "memberships.area = 'C'",
        'where'     => "categories_subcount.membershipid != '0'",
        'fields'     => array(
            'categoryid',
        )
    );

    $tbl_keys['category_memberships.categoryid'] = array(
        'keys'         => array(
            'category_memberships.categoryid' => 'categories.categoryid',
        ),
        'fields'     => array(
            'categoryid',
        ),
    );

    $tbl_keys['category_memberships.memberships'] = array(
        'keys'         => array(
            'category_memberships.membershipid' => 'memberships.membershipid',
        ),
        'on'         => "memberships.area = 'C'",
        'fields'     => array(
            'categoryid',
        ),
    );

    $tbl_keys['counties.stateid'] = array(
        'keys'         => array(
            'counties.stateid' => 'states.stateid',
        ),
        'fields'     => array(
            'county',
        ),
    );

    $tbl_keys['country_currencies.country_code'] = array(
        'keys'         => array(
            'country_currencies.country_code' => 'countries.code',
        ),
        'fields'     => array(
            'code',
        ),
    );

    $tbl_keys['country_currencies.code'] = array(
        'keys'         => array(
            'country_currencies.code' => 'currencies.code',
        ),
        'fields'     => array(
            'country_code',
        ),
    );

    $tbl_keys['customers.parent'] = array(
        'keys'         => array(
            'customers.parent' => 'customers.id',
        ),
        'where'     => "customers.parent > 0",
        'fields'     => array(
            'id',
        ),
    );

    $tbl_keys['customers.memberships'] = array(
        'keys'         => array(
            'customers.membershipid' => 'memberships.membershipid',
        ),
        'where'     => "customers.membershipid != '0'",
        'fields'     => array(
            'id',
        ),
    );

    $tbl_keys['customers.pending_membershipid'] = array(
        'keys'         => array(
            'customers.pending_membershipid' => 'memberships.membershipid',
        ),
        'where'     => "customers.pending_membershipid != '0'",
        'fields'     => array(
            'id',
        ),
    );

    $tbl_keys['discount_memberships.membershipid'] = array(
        'keys'         => array(
            'discount_memberships.membershipid' => 'memberships.membershipid',
        ),
        'on'         => "memberships.area = 'C'",
        'fields'     => array(
            'discountid',
        ),
    );

    $tbl_keys['discounts.provider'] = array(
        'keys'         => array(
            'discounts.provider' => 'customers.id',
        ),
        'on'         => "customers.usertype IN ('A','P')",
        'fields'     => array(
            'discountid',
        ),
    );

    $tbl_keys['featured_products.productid'] = array(
        'keys'         => array(
            'featured_products.productid' => 'products.productid',
        ),
        'fields'     => array(
            'productid',
            'categoryid',
        ),
    );

    $tbl_keys['featured_products.categoryid'] = array(
        'keys'         => array(
            'featured_products.categoryid' => 'categories.categoryid',
        ),
        'where'     => "featured_products.categoryid != '0'",
        'fields'     => array(
            'productid',
            'categoryid',
        ),
    );

    $tbl_keys['ge_products.productid'] = array(
        'keys'         => array(
            'ge_products.productid' => 'products.productid',
        ),
        'fields'     => array(
            'geid',
        ),
    );

    $tbl_keys['images_C.id'] = array(
        'keys'         => array(
            'images_C.id' => 'categories.categoryid',
        ),
        'fields'     => array(
            'imageid',
        ),
    );

    $tbl_keys['images_P.id'] = array(
        'keys'         => array(
            'images_P.id' => 'products.productid',
        ),
        'fields'     => array(
            'imageid',
        ),
    );

    $tbl_keys['images_G.id'] = array(
        'keys'         => array(
            'images_G.id' => 'language_codes.lngid',
        ),
        'fields'     => array(
            'id',
            'imageid',
        ),
    );

    $tbl_keys['languages.code'] = array(
        'keys'         => array(
            'languages.code' => 'language_codes.code',
        ),
        'fields'     => array(
            'code',
        ),
    );

    $tbl_keys['images_T.id'] = array(
        'keys'         => array(
            'images_T.id' => 'products.productid',
        ),
        'fields'     => array(
            'imageid',
        ),
    );

    $tbl_keys['languages_alt.code'] = array(
        'keys'         => array(
            'languages_alt.code' => 'language_codes.code',
        ),
        'fields'     => array(
            'name',
            'value',
        ),
        'type'         => 'W',
    );

    $tbl_keys['memberships_lng.membershipid'] = array(
        'keys'         => array(
            'memberships_lng.membershipid' => 'memberships.membershipid',
        ),
        'fields'     => array(
            'code',
        ),
    );

    $tbl_keys['memberships_lng.code'] = array(
        'keys'         => array(
            'memberships_lng.code' => 'language_codes.code',
        ),
        'fields'     => array(
            'membershipid',
        ),
        'type'         => 'W',
    );

    $tbl_keys['order_details.orderid'] = array(
        'keys'         => array(
            'order_details.orderid' => 'orders.orderid',
        ),
        'fields'     => array(
            'itemid',
        ),
    );

    $tbl_keys['order_extras.orderid'] = array(
        'keys'         => array(
            'order_extras.orderid' => 'orders.orderid',
        ),
        'fields'     => array(
            'khash',
        ),
    );

    $tbl_keys['pages.language'] = array(
        'keys'         => array(
            'pages.language' => 'language_codes.code',
        ),
        'fields'     => array(
            'pageid',
            'title',
        ),
        'type'         => 'W',
    );

    $tbl_keys['pmethod_memberships.memberships'] = array(
        'keys'         => array(
            'pmethod_memberships.membershipid' => 'memberships.membershipid',
        ),
        'on'         => "memberships.area = 'C'",
        'fields'     => array(
            'paymentid',
        ),
    );

    $tbl_keys['pricing.products'] = array(
        'keys'         => array(
            'pricing.productid' => 'products.productid',
        ),
        'fields'     => array(
            'priceid',
        ),
    );

    $tbl_keys['pricing.memberships'] = array(
        'keys'         => array(
            'pricing.membershipid' => 'memberships.membershipid',
        ),
        'on'         => "memberships.area = 'C'",
        'where'        => "pricing.membershipid != '0'",
        'fields'     => array(
            'priceid',
        ),
    );

    $tbl_keys['product_bookmarks.productid'] = array(
        'keys' => array(
            'product_bookmarks.productid' => 'products.productid',
        ),
    );

    $tbl_keys['product_bookmarks.userid'] = array(
        'keys' => array(
            'product_bookmarks.userid' => 'customers.id',
        ),
    );

    $tbl_keys['product_memberships.productid'] = array(
        'keys'         => array(
            'product_memberships.productid' => 'products.productid',
        ),
        'fields'     => array(
            'membershipid',
        ),
    );

    $tbl_keys['product_memberships.memberships'] = array(
        'keys'         => array(
            'product_memberships.membershipid' => 'memberships.membershipid',
        ),
        'on'         => "memberships.area = 'C'",
        'fields'     => array(
            'productid',
        ),
    );

    $tbl_keys['product_taxes.productid'] = array(
        'keys'         => array(
            'product_taxes.productid' => 'products.productid',
        ),
        'fields'     => array(
            'taxid',
        ),
    );

    $tbl_keys['product_taxes.taxid'] = array(
        'keys'         => array(
            'product_taxes.taxid' => 'taxes.taxid',
        ),
        'fields'     => array(
            'productid',
        ),
    );

    $tbl_keys['products.provider'] = array(
        'keys'         => array(
            'products.provider' => 'customers.id',
        ),
        'on'         => "customers.usertype IN ('P','A')",
        'fields'     => array(
            'productid',
            'productcode',
        ),
    );

    $tbl_keys['products_lng_current.products_categories'] = array(
        'keys'         => array(
            'products_lng_current.productid' => 'products_categories.productid',
        ),
        'fields'     => array(
            'productid',
            'product',
        ),
    );

    $tbl_keys['products_lng_current.quick_flags'] = array(
        'keys'         => array(
            'products_lng_current.productid' => 'quick_flags.productid',
        ),
        'fields'     => array(
            'productid',
            'product',
        ),
    );

    $tbl_keys['products_lng_current.quick_prices'] = array(
        'keys'         => array(
            'products_lng_current.productid' => 'quick_prices.productid',
        ),
        'fields'     => array(
            'productid',
            'product',
        ),
    );

    $tbl_keys['products_lng_current.pricing'] = array(
        'keys'         => array(
            'products_lng_current.productid' => 'pricing.productid',
        ),
        'on'         => "pricing.quantity = '1' AND pricing.membershipid = '0'",
        'fields'     => array(
            'productid',
            'product',
        ),
    );

    $tbl_keys['products_categories.productid'] = array(
        'keys'         => array(
            'products_categories.productid' => 'products.productid',
        ),
        'fields'     => array(
            'categoryid',
            'main',
        ),
    );

    $tbl_keys['products_categories.categoryid'] = array(
        'keys'         => array(
            'products_categories.categoryid' => 'categories.categoryid',
        ),
        'fields'     => array(
            'productid',
            'main',
        ),
    );

    $tbl_keys['products.every product has main category'] = array(
        'keys'         => array(
            'products.productid' => 'products_categories.productid',
        ),
        'on' => 'products_categories.main="Y"',
        'fields'     => array('productid'),
        'url'         => 'product_modify.php?mode=repair_main_category&amp;productid=',
    );

    $tbl_keys['products.every product has 1to1 row in xcart_product_sales_stats'] = array(
        'keys'         => array(
            'products.productid' => 'product_sales_stats.productid',
        ),
        'fields'     => array('productid'),
        'url'         => 'product_modify.php?mode=repair_sales_stats&amp;productid=',
    );

    $tbl_keys['product_sales_stats.every product has 1to1 row in xcart_products'] = array(
        'keys'         => array(
            'product_sales_stats.productid' => 'products.productid',
        ),
        'fields'     => array('productid'),
        'url'         => 'product_modify.php?mode=repair_sales_stats',
    );

    $tbl_keys['quick_flags.productid'] = array(
        'keys'         => array(
            'quick_flags.productid' => 'products.productid',
        ),
    );

    $tbl_keys['quick_prices.productid'] = array(
        'keys'         => array(
            'quick_prices.productid' => 'products.productid',
        ),
        'fields'     => array(
            'membershipid',
        ),
    );

    $tbl_keys['quick_prices.priceid'] = array(
        'keys'         => array(
            'quick_prices.priceid'         => 'pricing.priceid',
            'quick_prices.productid'     => 'pricing.productid',
        ),
        'fields'     => array(
            'membershipid',
        ),
    );

    $tbl_keys['quick_prices.variant_priceid'] = array(
        'keys'         => array(
            'quick_prices.priceid'         => 'pricing.priceid',
            'quick_prices.productid'     => 'pricing.productid',
            'quick_prices.variantid'     => 'pricing.variantid',
        ),
        'where'     => "quick_prices.variantid != '0'",
        'fields'     => array(
            'membershipid',
        ),
    );

    $tbl_keys['quick_prices.memberships'] = array(
        'keys'         => array(
            'quick_prices.membershipid' => 'memberships.membershipid',
        ),
        'on'         => "memberships.area = 'C'",
        'where'     => "quick_prices.membershipid != '0'",
        'fields'     => array(
            'productid',
        ),
    );

    $tbl_keys['register_field_values.fieldid'] = array(
        'keys'         => array(
            'register_field_values.fieldid' => 'register_fields.fieldid',
        ),
        'fields'     => array(
            'userid',
            'value',
        ),
    );

    $tbl_keys['register_field_values.userid'] = array(
        'keys'         => array(
            'register_field_values.userid' => 'customers.id',
        ),
        'fields'     => array(
            'fieldid',
            'value',
        ),
    );

    $tbl_keys['register_field_address_values.fieldid'] = array(
        'keys'         => array(
            'register_field_address_values.fieldid' => 'register_fields.fieldid',
        ),
        'fields'     => array(
            'addressid',
            'value',
        ),
    );

    $tbl_keys['register_field_address_values.addressid'] = array(
        'keys'         => array(
            'register_field_address_values.addressid' => 'address_book.id',
        ),
        'fields'     => array(
            'fieldid',
            'value',
        ),
    );

    $tbl_keys['shipping_rates.shippingid'] = array(
        'keys'         => array(
            'shipping_rates.shippingid' => 'shipping.shippingid',
        ),
        'fields'     => array(
            'rateid',
            'zoneid',
            'provider',
        ),
    );

    $tbl_keys['shipping_rates.provider'] = array(
        'keys'         => array(
            'shipping_rates.provider' => 'customers.id',
        ),
        'on'         => "customers.usertype IN ('P','A')",
        'fields'     => array(
            'rateid',
            'shippingid',
            'zoneid',
        ),
    );

    $tbl_keys['shipping_rates.zoneid'] = array(
        'keys'         => array(
            'shipping_rates.zoneid' => 'zones.zoneid',
        ),
        'where'     => "shipping_rates.zoneid != '0'",
        'fields'     => array(
            'rateid',
            'shippingid',
            'provider',
        ),
    );

    $tbl_keys['shipping_options.carrier'] = array(
        'keys'         => array(
            'shipping_options.carrier' => 'shipping.code',
        ),
        'where'     => "shipping_options.carrier != 'INTERSHIPPER'",
    );

    $tbl_keys['states.country_code'] = array(
        'keys'         => array(
            'states.country_code' => 'countries.code',
        ),
        'fields'     => array(
            'code',
            'state',
        ),
    );

    $tbl_keys['tax_rate_memberships.rateid'] = array(
        'keys'         => array(
            'tax_rate_memberships.rateid' => 'tax_rates.rateid',
        ),
        'fields'     => array(
            'membershipid',
        ),
    );

    $tbl_keys['tax_rate_memberships.memberships'] = array(
        'keys'         => array(
            'tax_rate_memberships.membershipid' => 'memberships.membershipid',
        ),
        'on'         => "memberships.area = 'C'",
        'fields'     => array(
            'rateid',
        ),
    );

    $tbl_keys['tax_rates.taxid'] = array(
        'keys'         => array(
            'tax_rates.taxid' => 'taxes.taxid',
        ),
        'fields'     => array(
            'rateid',
        )
    );

    $tbl_keys['tax_rates.provider'] = array(
        'keys'         => array(
            'tax_rates.provider' => 'customers.id',
        ),
        'on'         => "customers.usertype IN ('P','A')",
        'fields'     => array(
            'rateid',
        ),
    );

    $tbl_keys['tax_rates.zoneid'] = array(
        'keys'         => array(
            'tax_rates.zoneid' => 'zones.zoneid',
        ),
        'where'     => "tax_rates.zoneid != '0'",
        'fields'     => array(
            'rateid',
        ),
    );

    $tbl_keys['zone_element.zoneid'] = array(
        'keys'         => array(
            'zone_element.zoneid' => 'zones.zoneid',
        ),
        'fields'     => array(
            'field',
            'field_type',
        ),
    );

    $tbl_keys['zones.provider'] = array(
        'keys'         => array(
            'zones.provider' => 'customers.id',
        ),
        'on'         => "customers.usertype IN ('P','A')",
        'fields'     => array(
            'zone_name',
        ),
    );

    // Search absent clean URLs
    $tbl_keys['pages.pageid clean_urls test'] = array(
        'keys'         => array(
            'pages.pageid' => 'clean_urls.resource_id',
        ),
        'on'     => "clean_urls.resource_type = 'S'",
        'fields'     => array(
            'filename',
            'title',
        ),
        'where' => "pages.level = 'E'",
    );
    $tbl_keys['products.productid clean_urls test'] = array(
        'keys'         => array(
            'products.productid' => 'clean_urls.resource_id',
        ),
        'on'     => "clean_urls.resource_type = 'P'",
        'fields'     => array(
            'productid',
            'productcode',
        ),
    );
    $tbl_keys['categories.categoryid clean_urls test'] = array(
        'keys'         => array(
            'categories.categoryid' => 'clean_urls.resource_id',
        ),
        'on'     => "clean_urls.resource_type = 'C'",
        'fields'     => array(
            'categoryid',
            'category',
        ),
    );
    $tbl_keys['manufacturers.manufacturerid clean_urls test'] = array(
        'keys'         => array(
            'manufacturers.manufacturerid' => 'clean_urls.resource_id',
        ),
        'on'     => "clean_urls.resource_type = 'M'",
        'fields'     => array(
            'manufacturerid',
            'manufacturer',
        ),
    );

    x_load_module('Product_Options'); // For class XCVariants*
    if (XCVariantsSQL::isVariantsExist()) {
        $tbl_keys['variants.check every product must have at least one variant row'] = array(
            'keys' => array('variants.productid' => 'products.productid'),
            'fields' => array('productcode', 'is_product_row')
        );

        $tbl_keys['products.check every variant must have at least one product row'] = array(
            'keys' => array('products.productid' => 'variants.productid'),
            'url'         => "product_modify.php?mode=repair_product_row_in_variants&amp;productid=",
        );
    }

    $_languages = array_keys($all_languages);

    foreach ($sql_tbl as $tbl_alias => $table) {
        if (strpos($table, XC_TBL_PREFIX . 'products_lng_') === false)
            continue;
        if (strpos($tbl_alias, 'products_lng_current') !== false)
            continue;

        $_code = str_replace('products_lng_', '', $tbl_alias);

        if (!in_array($_code, $_languages))
            continue;

        $tbl_keys[$tbl_alias . '.products'] = array(
            'keys'         => array(
                $tbl_alias . '.productid' => 'products.productid',
            ),
            'fields'     => array(
                'productid',
                'product',
            ),
        );

        $tbl_keys['products.' . $tbl_alias] = array(
            'keys'         => array(
                'products.productid' => $tbl_alias . '.productid',
            ),
            'fields'     => array(
                'productid',
            ),
            'url'         => "product_modify.php?mode=repair_products_lng&amp;section=lng&amp;productid=",
            'where_or'     => "$tbl_alias.product = 'restored_product'"
        );
    }

    $sql_replace = array();

    foreach ($sql_tbl as $alias => $name) {

        $sql_replace["/(^|[^\w\d_])" . preg_quote($alias, '/') . "(\.|$)/S"] = "\\1" . $name . "\\2";

    }

    $err_num = 0;

    $err_store = array();

    $lbl_error = func_get_langvar_by_name('lbl_error', array(), false, true);
    $lbl_warning = func_get_langvar_by_name('lbl_warning', array(), false, true);
    func_display_service_header('lbl_checking_dbintegrity_');

    $tables_list = func_query_column("SHOW TABLES");

    $tmp = array_values($sql_tbl);

    if (defined('X_MYSQL_LOWER_CASE_TABLE_NAMES') && X_MYSQL_LOWER_CASE_TABLE_NAMES > 0) {

        $tables_list = array_map('strtolower', $tables_list);

        $tmp = array_map('strtolower', $tmp);

    }

    $tmp = array_diff($tmp, $tables_list);

    foreach ($tmp as $v) {

        func_flush("<br />\n" . $lbl_error . ": " . func_get_langvar_by_name('lbl_table_x_not_found', array('table' => $v), false, true) . "<br />\n");

        $err_store[$v] = 'no table';

    }

    $err_num += count($tmp);

    // Scan exist key links
    foreach ($tbl_keys as $kname => $d) {

        $join = $from = $added = array();

        $as = $where = $join_on = $tbl2 = '';

        if (empty($d['fields']) || !is_array($d['fields'])) {
            $d['fields'] = array();
        }

        // Get parent table name
        $kname = trim($kname);
        $tbl1 = substr($kname, 0, strpos($kname, '.'));
        // Translate key pairs
        foreach ($d['keys'] as $kk => $key) {

            $from[] = $d['fields'][] = substr($kk, strpos($kk, ".")+1);

            // Get child table name
            if (empty($tbl2)) {

                $tbl2 = substr($key, 0, strpos($key, '.'));

                if ($tbl1 == $tbl2) {

                    $as = '_' . $tbl2;
                    $added[$tbl2] = $as;
                }

                $tbl2 = func_trans_tbl_name($tbl2);
            }

            $key = func_trans_tbl_name($key, $added);

            $kk = func_trans_tbl_name($kk);

            $join[] = $kk . " = " . $key;

            if (empty($where)) {
                $where = $key;
            }

        }

        // Translate JOIN ON (if exist)
        if (!empty($d['on'])) {

            $join_on = " AND " . func_trans_tbl_name($d['on']);

        }

        $tbl1 = func_trans_tbl_name($tbl1);

        foreach ($d['fields'] as $k => $v) {

            $d['fields'][$k] = $tbl1 . "." . $v;

        }

        if (!empty($as)) {

            $as = " as " . $as;

        }

        $tbl1_lower = (defined('X_MYSQL_LOWER_CASE_TABLE_NAMES') && X_MYSQL_LOWER_CASE_TABLE_NAMES > 0) ? strtolower($tbl1): $tbl1;

        $tbl2_lower = (defined('X_MYSQL_LOWER_CASE_TABLE_NAMES') && X_MYSQL_LOWER_CASE_TABLE_NAMES > 0) ? strtolower($tbl2): $tbl2;

        if (
            !in_array($tbl1_lower, $tables_list)
            || !in_array($tbl2_lower, $tables_list)
        ) {

            $res = false;

        } else {

            $query = "SELECT "
                . implode(", ", $d['fields'])
                . " FROM $tbl1 LEFT JOIN $tbl2 $as ON "
                . implode(" AND ", $join)
                . $join_on
                . " WHERE "
                . $where
                . " IS NULL";

            // Translate where (if exist)
            if (!empty($d['where'])) {

                $query .= " AND " . func_trans_tbl_name($d['where']);

            } elseif(!empty($d['where_or'])) {

                $query .= " OR " . func_trans_tbl_name($d['where_or']);

            }

            $res = db_query("$query LIMIT " . XC_TOOLS_LIMIT_QUERY_ROWS);
        }

        if ($res) {
            if (db_num_rows($res) > 0) {
                func_flush("<br />\n");
                if (@$d['type'] == 'W') {
                    echo $lbl_warning;
                } else {
                    echo $lbl_error;
                }

                echo ": "
                    . func_get_langvar_by_name(
                        'lbl_unrelated_data',
                        array(
                            'table_parent'     => $tbl1,
                            'table_child'     => $tbl2
                        ),
                        false,
                        true
                    )
                    . "<br />\n";

                $err_num++;

                while ($row = db_fetch_array($res)) {
                    if (@$d['type'] != 'W') {
                        $keys = array();
                        foreach ($from as $v) {
                            $keys[$v] = $row[$v];
                        }

                        $err_store[$tbl1][$tbl2][] = array(
                            'row'   => $row,
                            'keys'  => $keys,
                            'url'  => @$d['url'],
                        );
                    }

                    echo "&nbsp;&nbsp;&nbsp;";

                    $is_first = true;

                    foreach ($row as $k => $v) {

                        if (!in_array($k, $from))
                            continue;

                        if (!$is_first)
                            echo "; ";

                        $is_first = false;

                        echo $k . ": " . $v;

                    }

                    func_flush("<br />\n");
                    if ($err_num >= XC_TOOLS_LIMIT_ERR_COUNT)
                        break;
                }

            }

            db_free_result($res);

        } elseif (!in_array($tbl1_lower, $tables_list)) {

            if (isset($err_store[$tbl1]) && $err_store[$tbl1] == 'no table') {

                $err_num++;
                func_flush(
                    "<br />\n"
                    . $lbl_error
                    . ": "
                    . func_get_langvar_by_name(
                        'lbl_table_x_not_found',
                        array(
                            'table' => $tbl1,
                        ),
                        false,
                        true
                    )
                    . "<br />\n"
                );

                $err_store[$tbl1] = 'no table';

            }

        } elseif (!in_array($tbl2_lower, $tables_list)) {

            if (
                isset($err_store[$tbl2])
                && $err_store[$tbl2] == 'no table'
            ) {

                $err_num++;

                func_flush(
                    "<br />\n"
                    . $lbl_error
                    . ": "
                    . func_get_langvar_by_name(
                        'lbl_table_x_not_found',
                        array(
                            'table' => $tbl2
                        ),
                        false,
                        true
                    )
                    . "<br />\n"
                );

                $err_store[$tbl2] = 'no table';

            }

        }

        func_flush(". ");

        if ($err_num >= XC_TOOLS_LIMIT_ERR_COUNT)
            break;
    }

    if ($err_num == 0) {

        $top_message['content'] = func_get_langvar_by_name("lbl_integrity_check_successfully");
        $top_message['type']     = "I";

        $delay = 3;

    } else {

        echo "<br />\n<br />\n"
            . func_get_langvar_by_name(
                'lbl_total',
                array(),
                false,
                true
            )
            . ": "
            . $err_num;

        $top_message['content'] = func_get_langvar_by_name("lbl_integrity_check_successfully_err", array("err_num" => $err_num));
        $top_message['type']     = "W";
        $top_message['anchor']     = "integrdb";

        $delay = 30;

    }

    if (!empty($dbic_log_file))
        fclose($dbic_log_file);

    func_html_location('tools.php', $delay);

// }}}
} elseif (!empty($regenerate_blowfish)) {
// {{{

    require $xcart_dir.'/include/safe_mode.php';

    func_display_service_header('lbl_regenerating_blowfish_key');

    $fp = @fopen($xcart_dir . '/config.php', "r+");

    if (!$fp) {

        $top_message = array(
            'content'     => func_get_langvar_by_name('lbl_cannot_read_config'),
            'type'         => 'E'
        );

        func_header_location('tools.php');

    }

    mt_srand(XC_TIME);

    $new_blowfish_key = func_get_secure_random_key(32);

    $security_key_length = 512;
    $rand_string = func_get_secure_random_key($security_key_length*3);

    $new_xc_security_key_session    = substr($rand_string,     0, $security_key_length);
    $new_xc_security_key_config     = substr($rand_string, $security_key_length*1, $security_key_length);
    $new_xc_security_key_general    = substr($rand_string, $security_key_length*2);
    $_keys = array(
        'xc_security_key_session' => $new_xc_security_key_session,
        'xc_security_key_config' => $new_xc_security_key_config,
        'xc_security_key_general' => $new_xc_security_key_general,
    );

    // Collect validated users for sign
    $users2update_signature = array();
    $users2check = func_query("SELECT * FROM $sql_tbl[customers] WHERE " . XCUserSignature::getApplicableSqlCondition());
    if (!empty($users2check)) {
        foreach ($users2check as $k=>$_user) {
            $obj_user = new XCUserSignature($_user);
            if ($obj_user->checkSignature()) {
                $users2update_signature[] = $_user['id'];
            }
        }
    }

    // Collect validated xauth-users for sign
    if (func_is_defined_module_sql_tbl('XAuth', 'xauth_user_ids')) {
        $xauth_users2update_signature = array();
        $xauth_users2check = func_query("SELECT " . XCUserXauthIdsSignature::getSignedFields() . " FROM $sql_tbl[customers] INNER JOIN $sql_tbl[xauth_user_ids] ON $sql_tbl[customers].id=$sql_tbl[xauth_user_ids].id AND " . XCUserXauthIdsSignature::getApplicableSqlCondition());
        if (!empty($xauth_users2check)) {
            foreach ($xauth_users2check as $k=>$_xauth_user) {
                $obj_xauth_user = new XCUserXauthIdsSignature($_xauth_user);
                if ($obj_xauth_user->checkSignature()) {
                    $xauth_users2update_signature[] = $_xauth_user['auth_id'];
                }
            }
        }
    }

    // Collect validated reset password keys for sign
    $reset_keys2update_signature = array();
    $reset_keys2check = func_query("SELECT * FROM $sql_tbl[reset_passwords] WHERE " . XCResetPasswordSignature::getApplicableSqlCondition());
    if (!empty($reset_keys2check)) {
        foreach ($reset_keys2check as $k=>$_reset_key) {
            $obj_reset_key = new XCResetPasswordSignature($_reset_key);
            if ($obj_reset_key->checkSignature()) {
                $reset_keys2update_signature[] = $_reset_key['userid'];
            }
        }
    }

    // Collect validated configs for sign
    $configs2update_signature = array();
    $configs2check = func_query("SELECT " . XCConfigSignature::getSignedFields() . " FROM $sql_tbl[config] WHERE " . XCConfigSignature::getApplicableSqlCondition());
    if (!empty($configs2check)) {
        foreach ($configs2check as $k=>$_config_data) {
            $obj_config_data = new XCConfigSignature($_config_data);
            if ($obj_config_data->checkSignature()) {
                $configs2update_signature[] = $_config_data['name'];
            }
        }
    }

    $allfile = '';
    $is_added = false;

    while (!feof($fp)) {

        $buffer = fgets($fp, 4096);

        if (preg_match('/^\$blowfish_key\s*=/S', $buffer)) {
            $buffer = preg_replace('/=.*;/', "= '" . $new_blowfish_key . "'; //old_key:" . $blowfish_key . ';', $buffer);
            $is_added = true;
        }

        if (preg_match('/^\$xc_security_key_session\s*=/S', $buffer)) {
            $buffer = preg_replace('/=.*;/', "= '" . $new_xc_security_key_session . "'; //old_key:" . $xc_security_key_session . ';', $buffer);
        }

        if (preg_match('/^\$xc_security_key_config\s*=/S', $buffer)) {
            $buffer = preg_replace('/=.*;/', "= '" . $new_xc_security_key_config . "'; //old_key:" . $xc_security_key_config . ';', $buffer);
        }

        if (preg_match('/^\$xc_security_key_general\s*=/S', $buffer)) {
            $buffer = preg_replace('/=.*;/', "= '" . $new_xc_security_key_general . "'; //old_key:" . $xc_security_key_general . ';', $buffer);
        }

        $allfile .= $buffer;

    }

    ftruncate($fp, 0);

    rewind($fp);

    fwrite($fp, $allfile);

    fclose($fp);

    if (!$is_added) {

        $top_message = array(
            'content'     => func_get_langvar_by_name('txt_regen_blowfish_key_failed'),
            'type'         => 'E'
        );

        func_header_location('tools.php');

    }

    func_change_shop_secure_keys($_keys);

    func_recrypt_data($bf_crypted_tables, $ingnored_encryption_types, $new_blowfish_key);

    func_refresh_check_blowfish_data($new_blowfish_key);

    // Update signature for validated users
    if (!empty($users2update_signature)) {
        $users2update_signature = func_query("SELECT * FROM $sql_tbl[customers] WHERE id IN ('" . implode("','", $users2update_signature) . "')");
        foreach ($users2update_signature as $_user) {
            $obj_user = new XCUserSignature($_user);
            $obj_user->updateSignature();
        }
    }

    // Update signature for validated xauth-users
    if (!empty($xauth_users2update_signature)) {
        $xauth_users2update_signature = func_query("SELECT " . XCUserXauthIdsSignature::getSignedFields() . " FROM $sql_tbl[customers] INNER JOIN $sql_tbl[xauth_user_ids] ON $sql_tbl[customers].id=$sql_tbl[xauth_user_ids].id WHERE auth_id IN ('" . implode("','", $xauth_users2update_signature) . "')");
        foreach ($xauth_users2update_signature as $_xauth_user) {
            $obj_xauth_user = new XCUserXauthIdsSignature($_xauth_user);
            $obj_xauth_user->updateSignature();
        }
    }

    // Update signature for validated reset passwords
    if (!empty($reset_keys2update_signature)) {
        $reset_keys2update_signature = func_query("SELECT * FROM $sql_tbl[reset_passwords] WHERE userid IN ('" . implode("','", $reset_keys2update_signature) . "')");
        foreach ($reset_keys2update_signature as $_reset_key) {
            $obj_reset_key = new XCResetPasswordSignature($_reset_key);
            $obj_reset_key->updateSignature();
        }
    }

    // Update signature for validated configs
    if (!empty($configs2update_signature)) {
        $configs2update_signature = func_query("SELECT " . XCConfigSignature::getSignedFields() . " FROM $sql_tbl[config] WHERE name IN ('" . implode("','", $configs2update_signature) . "')");
        foreach ($configs2update_signature as $_config) {
            $obj_config = new XCConfigSignature($_config);
            $obj_config->updateSignature();
        }
    }

    $top_message = array(
        'content'     => func_get_langvar_by_name('txt_regen_blowfish_key_success'),
        'type'         => 'I',
    );

    func_update_bf_generation_date();

    func_html_location('tools.php');

// }}}
} elseif (
    isset($check_sku)
    && !empty($active_modules['Product_Options'])
) {
// {{{

    require $xcart_dir . '/include/safe_mode.php';

    // Check SKU list
    x_load('product');

    func_display_service_header('txt_sku_checking_');

    $cnt = 0;

    $res = db_query("SELECT p1.productid, p1.productcode, p1.provider FROM $sql_tbl[products] as p1 INNER JOIN $sql_tbl[products] as p2 ON p1.provider = p2.provider ".XCVariantsSQL::getJoinQueryVariants(array('Variants' => 'v2', 'Products' => 'p2'))." WHERE p1.productcode = v2.productcode");

    if ($res) {

        x_load_module('Product_Options'); // For class XCVariants*
        while ($row = db_fetch_array($res)) {

            func_array2update('products',
                array(
                    'productcode' => addslashes(func_generate_sku($row['provider'], substr($row['productcode'], 0, 26)))
                ),
                "productid = '$row[productid]'"
            );

            $cnt++;

            if ($cnt % 10 == 0)
                func_flush(" .");

            XCVariantsChange::repairIntegrity($row['productid']);
        }

        db_free_result($res);

       }

    $top_message = array(
        'content'     => func_get_langvar_by_name('txt_sku_checking_is_success', array('cnt' => $cnt)),
        'type'         => 'I'
    );

    func_html_location('tools.php');

// }}}
} elseif (
    $config['SEO']['clean_urls_enabled'] == 'Y'
    && isset($generate_clean_urls)
) {
// {{{
    require $xcart_dir . '/include/safe_mode.php';

    // Regenerate Clean URLs

    if (
        !isset($generate_clean_urls_for)
        || empty($generate_clean_urls_for)
        || !is_array($generate_clean_urls_for)
    ) {

        $top_message = array(
            'content'     => func_get_langvar_by_name('err_generate_clean_urls_no_types'),
            'type'         => 'E'
        );

        func_header_location('tools.php');
    }

    $resources_data = func_clean_url_get_resources_data();

    $log_filename = "x-errors_clean_url-" . date('ymd') . ".php";

    $log_path = $var_dirs['log'] . '/' .$log_filename;

    $log_url = "get_log.php?file=" . $log_filename;

    $logf = @fopen($log_path, 'w');

    if (!$logf) {

        $top_message = array(
            'type'         => 'E',
            'content'     => func_get_langvar_by_name('msg_err_log_writing')
        );

        func_header_location('tools.php');
    }

    $current_date = date(
        "d-M-Y H:i:s",
        XC_TIME + $config['Appearance']['timezone_offset']
    );

    $message =<<<OUT
Date: $current_date
Launched by: $login

OUT;

    fwrite($logf, X_LOG_SIGNATURE . $message);

    $generated = 0;
    $failed = 0;
    $last_error_title = false;

    foreach ($generate_clean_urls_for as $resource_type) {

        if (!in_array($resource_type, array_keys($resources_data))) {
            continue;
        }

        $resource = $resources_data[$resource_type];

        echo "<br />\n"
            . func_get_langvar_by_name(
                'txt_generating_clean_urls_for',
                array(
                    'resource_name' => $resource['resource_name'],
                ),
                false,
                true
            )
            . "<br />\n";

        $cnt = 0;

        $fetch_columns         = $resource['params'];
        $fetch_columns[]     = $resource['resource_table_alias'] . '.' . $resource['resource_id_column'];

        $res = db_query("SELECT ".join(",", $fetch_columns)." FROM $resource[resource_table] LEFT JOIN $sql_tbl[clean_urls] ON $sql_tbl[clean_urls].resource_type = '".$resource_type."' AND $sql_tbl[clean_urls].resource_id = {$resource['resource_table_alias']}.{$resource['resource_id_column']} WHERE $sql_tbl[clean_urls].resource_id IS NULL".($resource_type == 'S' ? " AND level='E'" : ""));

        if (!$res) {
            continue;
        }

        // Remove sql aliases from params
        $_pcount = count($resource['params']);
        $_search_regex = array_fill(0, $_pcount, "/.*\./Ss");
        $_replace_to = array_fill(0, $_pcount, "");
        $resource['params'] = array_map('preg_replace', $_search_regex, $_replace_to, $resource['params']);

        while ($row = db_fetch_array($res)) {

            $params = array();

            foreach ($row as $column => $value) {

                if (in_array($column, $resource['params'])) {

                    $params[$column] = $value;

                }

            }

            $autogenerated_url = func_clean_url_autogenerate($resource_type, $row[$resource['resource_id_column']], $params);

            if ($autogenerated_url == -1) // Clean URL must not be generated for static page (M:0074029)
                continue;

            if (empty($autogenerated_url)) {

                $failed++;
                $cnt++;

                func_clean_url_add_generate_log($logf, $resource_type, $row[$resource['resource_id_column']], $params);

                continue;
            }

            if (!func_clean_url_add($autogenerated_url, $resource_type, $row[$resource['resource_id_column']])) {

                $failed++;
                $cnt++;

                func_clean_url_add_generate_log($logf, $resource_type, $row[$resource['resource_id_column']], $params);

                continue;
            }

            $generated++;
            $cnt++;

            if ($cnt % 10 == 0) {

                func_flush('.');

                if ($cnt % 500 == 0) {

                    func_flush("<br />\n");

                }

            }

        } // /while

        db_free_result($res);

    } // /foreach

    fclose($logf);

    if ($failed != 0) {

        $top_message = array(
            'content' => func_get_langvar_by_name(
                'txt_generated_clean_urls_with_issues',
                array(
                    'generated' => $generated,
                    'failed'     => $failed,
                    'log_url'     => $log_url,
                )
            ),
            'type' => 'W',
        );

    } else {

        $top_message = array(
            'content'     => func_get_langvar_by_name('txt_generated_clean_urls_successfully'),
            'type'         => 'I'
        );

    }

    func_header_location('tools.php');

// }}}
} elseif (
    isset($reslice_zimages)
    || isset($reslice_all)
) {
// {{{

    require $xcart_dir . '/include/safe_mode.php';

    x_session_register('zimages_counter', 0);

    list(
        $iteration_name,
        $url_param,
        $iteration_limit
    ) = array(
        'reslice',
        "reslice_zimages=Y",
        5,
    );

    if (
        !isset($_GET['position'])
        || !is_numeric($_GET['position'])
        || $_GET['position'] < 0
    ) {

        $zimages_counter = 0;
        $position = 0;

        if (isset($reslice_all)) {

            $to_reslice = db_query("SELECT imageid, id FROM $sql_tbl[images_Z]");

            if ($to_reslice) {

                while ($row = db_fetch_array($to_reslice)) {

                    $_id = $row['id'] . ':' . $row['imageid'];

                    $data = (func_get_iteration_data($iteration_name, $_id) == 'import')
                        ? 'import'
                        : 'all';

                    func_add_iteration_row($iteration_name, $_id, $data);
                }

                db_free_result($to_reslice);
            }

        }

    }

    $iteration_count = func_get_iteration_length($iteration_name);

    if ($iteration_count <= 0)
        func_header_location('tools.php');

    func_display_service_header('lbl_magnifier_reslice_start');

    func_seek_iteration($iteration_name, $position);

    func_flush(' (' . (round($position / $iteration_count, 2) * 100) . '%) ');

    func_local_iteration_limit($iteration_name, $iteration_limit);

    $reason = true;

    while ($row = func_each_iteration($iteration_name, $reason)) {

        func_tick_iteration($iteration_name);

        list($productid, $imageid) = explode(":", $row['id']);

        if ($row['data'] == 'import') {

            $data = $row['id'];
            $reason = func_magnifier_reslice($imageid);

        } else {

            $reason = func_magnifier_reslice_image($imageid, $productid);
            $reason = ($reason[0] == 'I');
        }

        if ($reason === false) {

            $reason = 'magnifier_error';

            break;

        }

        $zimages_counter++;

    }

    $lbl_substitute = array(
        'count' => $zimages_counter,
        'total' => func_get_iteration_length($iteration_name),
    );

    switch ($reason) {
        case 'eof':

            if ($lbl_substitute['count'] == $lbl_substitute['total']) {

                $top_message = array(
                    'type'         => 'I',
                    'content'     => func_get_langvar_by_name('lbl_magnifier_reslice_successfull', $lbl_substitute)
                );

            } else {

                $top_message = array(
                    'type'         => 'W',
                    'content'     => func_get_langvar_by_name('lbl_magnifier_reslice_unsuccessfull', $lbl_substitute)
                );

            }

            func_init_iteration($iteration_name);

            break;

        case 'res':
        case 'limit':

            if (func_seek_iteration($iteration_name, 0, SEEK_CUR) == $position) {

                $top_message = array(
                    'type'         => 'E',
                    'content'     => func_get_langvar_by_name('lbl_magnifier_reslice_ttl_err', $lbl_substitute)
                );

            } else {

                func_header_location("tools.php?$url_param&position=" . func_seek_iteration($iteration_name, 0, SEEK_CUR));

            }

            break;

        case 'magnifier_error':

            $top_message = array(
                'type'         => 'E',
                'content'     => func_get_langvar_by_name('lbl_magnifier_reslice_error', $lbl_substitute)
            );

    }

    func_header_location('tools.php');

// }}}
} elseif (
    $generate_thumbnails_allowed
    && @$generate_thumbnails == 'Y'
) {
// {{{
    require $xcart_dir . '/include/safe_mode.php';

    x_session_register('timages_counter', 0);

    list(
        $url_param,
        $iteration_limit
    ) = array(
        "generate_thumbnails=Y",
        5,
    );

    if (
        !isset($_GET['position'])
        || !is_numeric($_GET['position'])
        || $_GET['position'] < 0
    ) {

        $timages_counter = 0;
        $position = 0;

    }

    $iteration_count = func_get_iteration_length('generate');

    if ($iteration_count <= 0) {

        func_header_location('tools.php');

    }

    func_display_service_header('lbl_generate_thumbnails_start');

    $a = func_seek_iteration('generate', $position);

    func_flush(' (' . (round($position / $iteration_count, 2) * 100) . '%) ');

    func_local_iteration_limit('generate', $iteration_limit);

    $reason = true;

    while ($row = func_each_iteration('generate', $reason)) {

        func_tick_iteration('generate');

        $productid = $row['id'];

        $result = func_generate_image($productid);

        if (false === $result) {

            $reason = 'generate_images_error';

            break;

        }

        if (!func_check_sysres()) {

            $reason = 'ttl';

            break;

        }

        $timages_counter ++;

        $reason = true;

    }

    $top_message = null;

    $lbl_substitute = array(
        'count' => $timages_counter,
        'total' => func_get_iteration_length('generate'),
    );

    switch ($reason) {

        case 'eof':

            if ($lbl_substitute['count'] == $lbl_substitute['total']) {

                $top_message = array(
                    'type'      => 'I',
                    'content'   => func_get_langvar_by_name('lbl_generate_images_successfull', $lbl_substitute)
                );

            } else {

                $top_message = array(
                    'type'      => 'W',
                    'content'   => func_get_langvar_by_name('lbl_generate_images_unsuccessfull', $lbl_substitute)
                );

            }

            func_init_iteration('generate');

            break;

        case 'res':
        case 'limit':

            $currentSeek = func_seek_iteration('generate', 0, SEEK_CUR);

            if ($currentSeek == $position) {

                $top_message = array(
                    'type'      => 'E',
                    'content'   => func_get_langvar_by_name("lbl_generate_images_ttl_error", $lbl_substitute)
                );

            } else {

                func_header_location("tools.php?generate_thumbnails=Y&position=" . $currentSeek);

            }

            break;

        case 'ttl':

            $top_message = array(
                'type'      => 'E',
                'content'   => func_get_langvar_by_name('lbl_generate_images_ttl_error', $lbl_substitute)
            );

            break;

        case 'generate_images_error':

            $content = func_get_langvar_by_name('lbl_generate_thumbnails_error', $lbl_substitute)
                . (
                    !empty($auto_thumb_error)
                        ? ' (' . func_get_langvar_by_name($auto_thumb_error) . ')'
                        : ''
                );

            $top_message = array(
                'type'      => 'E',
                'content'   => $content,
            );

    }

    func_header_location('tools.php');

// }}}
} elseif (isset($regenerate_dpicons)) {
// {{{

    require $xcart_dir . '/include/safe_mode.php';

    // Regenerate image cache

    func_display_service_header('lbl_image_cache_generate_start');

    x_session_register('image_cache_counter', 0);
    x_session_register('image_cache_return_url', false);

    if (
        !isset($_GET['position'])
        || !is_numeric($_GET['position'])
        || $_GET['position'] < 0
    ) {
        $position = 0;
        $image_cache_counter = 0;

        func_init_iteration('icache');

        x_session_register('image_cache_tasks');

        if (empty($image_cache_tasks)) {

            func_image_cache_remove();

            foreach ($image_caches as $t => $n) {

                $n = func_image_cache_get_datas($t);

                foreach ($n as $k => $fn) {

                    if (!$fn['is_valid']())
                        unset($n[$k]);

                }

                $res = db_query('SELECT imageid FROM ' . $sql_tbl['images_' . $t] . ' ORDER BY imageid');

                if ($res) {

                    while ($row = db_fetch_array($res)) {

                        foreach ($n as $name => $tmp) {

                            func_add_iteration_row('icache', $t . ':' . $name . ':' . $row['imageid']);

                        }

                    }

                    db_free_result($res);

                }
            }

        } else {

            foreach ($image_cache_tasks as $t) {

                $where = '';

                if ($t[1]) {

                    $where = " WHERE imageid IN ('" . implode("','", $t[1]). "')";

                } else {

                    func_image_cache_remove($t[0], $t[2]);

                }

                $data = func_image_cache_get_data($t[0], $t[2]);

                if (!$data || !$data['is_valid']())
                    continue;

                $res = db_query('SELECT imageid FROM ' . $sql_tbl['images_' . $t[0]] . $where . ' ORDER BY imageid');

                if ($res) {

                    while ($row = db_fetch_array($res)) {

                        func_add_iteration_row('icache', $t[0] . ':' . $t[2] . ':' . $row['imageid']);

                        if ($t[1]) {

                            func_image_cache_remove($t[0], $t[2], $row['imageid']);

                        }

                    }

                    db_free_result($res);

                }

            }

            x_session_unregister('image_cache_tasks');

        }

        $image_cache_return_url = (!empty($return_url)) ? $return_url : false;

    } else {

        func_seek_iteration('icache', $position);

    }

    $iteration_count = func_get_iteration_length('icache');

    if ($iteration_count <= 0) {
        func_header_location((!empty($image_cache_return_url)) ? $image_cache_return_url : 'tools.php');
    }

    func_flush(' (' . (round($position / $iteration_count, 2) * 100) . '%) ');

    func_local_iteration_limit('icache', 50);

    $reason = true;

    while ($row = func_each_iteration('icache', $reason)) {

        $data = explode(":", $row['id'], 3);

        $res = func_image_cache_build($data[0], $data[2], $data[1], X_IMAGE_CACHE_EXTERNAL_RES_CHECK);

        func_tick_iteration('icache');

        if ($res[2] === 'nogd' || $res[2] === 'noperms') {

            $reason = $res[2];
            break;

        } elseif ($res[2] === true) {

            $image_cache_counter += $res[0];

        }

    }

    $lbl_substitute = array(
        'count' => $image_cache_counter,
        'total' => func_get_iteration_length('icache'),
    );

    switch ($reason) {

        case 'eof':

            if ($lbl_substitute['count'] == $lbl_substitute['total']) {

                $top_message = array(
                    'type'         => 'I',
                    'content'     => func_get_langvar_by_name('lbl_image_cache_build_successfull', $lbl_substitute)
                );

            } else {

                $top_message = array(
                    'type'         => 'W',
                    'content'     => func_get_langvar_by_name('lbl_image_cache_build_unsuccessfull', $lbl_substitute)
                );

            }

            func_init_iteration('icache');

            break;

        case 'res':
        case 'limit':

            if (func_seek_iteration('icache', 0, SEEK_CUR) == $position) {

                $top_message = array(
                    'type'         => 'E',
                    'content'     => func_get_langvar_by_name("lbl_image_cache_build_ttl_err", $lbl_substitute)
                );

            } else {

                func_header_location("tools.php?regenerate_dpicons=Y&position=" . func_seek_iteration('icache', 0, SEEK_CUR));

            }

            break;

        case 'nogd':

            $top_message = array(
                'type'         => 'E',
                'content'     => func_get_langvar_by_name("lbl_image_cache_build_gd_err", $lbl_substitute)
            );

            func_init_iteration('icache');

            break;

        case 'noperms':

            $lbl_substitute['dir'] = func_get_images_root();

            $top_message = array(
                'type'         => 'E',
                'content'     => func_get_langvar_by_name("msg_err_dir_X_permission_denied", $lbl_substitute)
            );

            func_init_iteration('icache');

    }

    func_header_location((!empty($image_cache_return_url)) ? $image_cache_return_url : 'tools.php');

// }}}
} elseif (isset($mode_change_authmode)) {
// {{{

    require $xcart_dir . '/include/safe_mode.php';

    func_display_service_header('lbl_change_authmode_check');

    $_ok = func_get_langvar_by_name('lbl_ok', NULL, false, true);

    $new_authmode = $config['email_as_login'] != 'Y'
        ? 'email'
        : 'uname';

    // Changes confirmed, trying perform adjustments
    $errors = array();

    if ($new_authmode == 'email') {

        // Check for non-unique emails
        $accounts = func_find_similar_emails(array(), true, 10);

        if (!empty($accounts)) {

            func_flush(func_get_langvar_by_name('lbl_failed', NULL, false, true) . "<br /><br />\n");

            $top_message = array(
                'type'         => 'E',
                'content'     => func_get_langvar_by_name('txt_change_authmode_warn')
            );

            func_header_location('tools.php');

        }

    }

    func_flush(
        $_ok
        . "<br />\n"
        . func_get_langvar_by_name(
            'lbl_updating_profiles',
            NULL,
            false,
            true
        )
    );

    $changed_admin_users = func_query("SELECT * FROM $sql_tbl[customers] WHERE " . XCUserSignature::getApplicableSqlCondition());

    db_query("LOCK TABLES $sql_tbl[customers] WRITE");

    if ($new_authmode == 'email') {

        // Copy old logins into username field, replace login field with email
        db_query("UPDATE $sql_tbl[customers] SET username = login, login = email WHERE email <> ''");

    } else {
        
        // Copy old logins from username fields
        db_query("UPDATE $sql_tbl[customers] SET login = username WHERE username <> ''");

    }

    db_query("UNLOCK TABLES");

    if (!empty($changed_admin_users)) {
        foreach ($changed_admin_users as $old_admin_user) {
            $obj_user = new XCUserSignature($old_admin_user);
            if ($obj_user->checkSignature()) {

                $obj_user = new XCUserSignature(func_query_first("SELECT * FROM $sql_tbl[customers] WHERE id='$old_admin_user[id]'"));
                $obj_user->updateSignature();
            }
        }
    }

    db_query("UPDATE $sql_tbl[config] SET value='" . ( $new_authmode == 'email' ? 'Y' : 'N' ) . "' WHERE name='email_as_login'");

    func_flush($_ok . "<br />\n");

    $top_message['content'] = func_get_langvar_by_name('txt_change_authmode_success');

    func_header_location('tools.php');

// }}}
} elseif (
    isset($increase_init_orderid)
    && $allow_to_increase_init_orderid
) {
// {{{

    // Increase initial order ID
    require $xcart_dir.'/include/safe_mode.php';

    $top_message['anchor'] = "increase_init_orderid";
    $new_init_orderid = intval($new_init_orderid);
    if ($new_init_orderid <= $max_order_id) {
        $top_message['content'] = func_get_langvar_by_name("lbl_increase_init_orderid_err");
        $top_message['type'] = "E";
    } else {
        db_query("ALTER TABLE $sql_tbl[orders] AUTO_INCREMENT = $new_init_orderid");
        $top_message['content'] = func_get_langvar_by_name("lbl_increase_init_orderid_successfully");
        $top_message['type'] = "I";
    }

    func_header_location('tools.php');
// }}}
} elseif (isset($mode_rebuild_catindex)) {
// {{{

    require $xcart_dir . '/include/safe_mode.php';

    x_load('category');
    func_cat_tree_rebuild();

    func_header_location('tools.php');
// }}}    
} elseif (isset($start_generate_anonymous)) {
// {{{
    require $xcart_dir . '/include/safe_mode.php';
    $res = func_generate_anonymous_from_orders(array('rows_per_step' => 2000, 'total_count' => $count_orders2generate_anonymous_customers));
    if ($res == 'all_generated') {
        $top_message['content'] = func_get_langvar_by_name('msg_adm_operation_successfully');
        func_header_location('tools.php');
    } else {
        func_header_location('tools.php?start_generate_anonymous=Y');
    }
// }}}
} elseif (isset($start_tpl_syntax_checking)) {
// {{{
    require $xcart_dir . '/include/safe_mode.php';
    require $xcart_dir . DIR_ADMIN . '/check_tpl_syntax.php';

    $is_ok = XCTplSyntaxChecker::run_n_redirect($ignore_tpl_files, 100);
    if ($is_ok) {
        $top_message['content'] = func_get_langvar_by_name('msg_adm_operation_successfully');
    }

    func_header_location('tools.php');
// }}}
}


if (!empty($tbl_demo_data)) {

    $modules_to_delete = array();

    foreach ($tbl_demo_data as $mod => $value) {

        $modules_to_delete[$mod] = str_replace('_', " ", $mod);

    }

    $smarty->assign('modules_to_delete', $modules_to_delete);
}

$rsd_serach_date = array(
    'stats_adaptive'         => 'last_date',
    'stats_search'             => 'date',
);

$rsd_start_year = date('Y');

foreach ($rsd_serach_date as $tbl => $fld) {

    $tmp = func_query_first_cell("SELECT MIN($fld) FROM " . $sql_tbl[$tbl]);

    if (empty($tmp))
        continue;

    $tmp = date('Y', $tmp);

    if ($rsd_start_year > $tmp) {
        $rsd_start_year = $tmp;
    }

}

$smarty->assign('rsd_start_year', $rsd_start_year);

if (!is_writable($xcart_dir.'/config.php')) {

    $smarty->assign('config_non_writable', true);

}

// Check for admin staff users having same email
if ($config['email_as_login'] != 'Y') {

    $accounts = func_find_similar_emails(array(), true, 100);

    if (!empty($accounts)) {

        $smarty->assignByRef('nonuniq_accounts', $accounts);

    }

    $smarty->assign('usertypes', $usertypes);
}

$smarty->assign('regenerate_dpicons_allowed', $regenerate_dpicons_allowed);

$smarty->assign('reslice_zimages_allowed', $reslice_zimages_allowed);

$smarty->assign('generate_thumbnails_allowed', $generate_thumbnails_allowed);

$smarty->assign('allow_to_increase_init_orderid', $allow_to_increase_init_orderid);
$smarty->assign('possible_new_init_orderid', (intval($max_order_id/1000)+1)*1000);

if (!empty($err_store)) {

    $smarty->assign('err_store', $err_store);

}

if (!empty($estimate_dir_size)) {
    if ($smarty->compile_id_based_on_filters) {
        $smarty->assign('templates_cache', func_get_dir_status($var_dirs['cache'], true));
    } else {
        $smarty->assign('templates_cache', test_templates_cache());
    }

    $smarty->assign('tmp_dir', test_tmp_dir());
}

$smarty->assign('main','tools');

// Assign the current location line
$smarty->assign('location', $location);

// Assign the section navigation data
$smarty->assign('dialog_tools_data', $dialog_tools_data);

if (is_readable($xcart_dir . '/modules/gold_display.php')) {
    include $xcart_dir . '/modules/gold_display.php';
}

func_display('admin/home.tpl', $smarty);
?>
