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
 * General statistics page interface
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Admin interface
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    3968cba5ecdb78320d43cbe05a25fe35597bc800, v77 (xcart_4_7_0), 2015-02-17 13:29:01, statistics.php, aim
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

require __DIR__.'/auth.php';
require $xcart_dir.'/include/security.php';

x_session_register('date_range');

if (!in_array(@$mode, array('general','logins','adaptives','search','users_online')))
    $mode = 'general';

$location[] = array(func_get_langvar_by_name('lbl_statistics'), 'statistics.php');

/**
 * Define data for the navigation within section
 */
$dialog_tools_data['left'][] = array('link' => 'statistics.php', 'title' => func_get_langvar_by_name('lbl_general_statistics'));

if ($user_account['flag'] != 'FS')
    $dialog_tools_data['left'][] = array('link' => "statistics.php?mode=logins", 'title' => func_get_langvar_by_name('lbl_log_in_history'));
$dialog_tools_data['left'][] = array('link' => "statistics.php?mode=adaptives", 'title' => func_get_langvar_by_name('lbl_browser_settings'));
if (!empty($active_modules['Users_online']))
    $dialog_tools_data['left'][] = array('link' => "statistics.php?mode=users_online", 'title' => func_get_langvar_by_name('lbl_users_online'));
$dialog_tools_data['left'][] = array('link' => "statistics.php?mode=search", 'title' => func_get_langvar_by_name('lbl_search_statistics'));

if ($user_account['flag'] != 'FS')
    $dialog_tools_data['right'][] = array('link' => 'general.php', 'title' => func_get_langvar_by_name('lbl_summary'));

$ctime = XC_TIME + $config['Appearance']['timezone_offset'];

$do_delete_login_history = ($mode == 'logins' && (@$action == 'delete' || @$action == 'delete_all'));

if ($REQUEST_METHOD == 'POST' && !$do_delete_login_history && empty($submode)) {

    // Save the date range

    if ($start_date) {
        $start_date = func_prepare_search_date($start_date);
        $end_date   = func_prepare_search_date($end_date, true);
    } else {
        $start_date = func_prepare_search_date($ctime);
        $end_date = $ctime;
    }

    $date_range['start_date'] = $start_date;
    $date_range['end_date'] = $end_date;
    $date_range['refresh_end_date'] = '';

    if ($QUERY_STRING)
        $qry_string = "?$QUERY_STRING";

    func_header_location('statistics.php'.$qry_string);
}

if (empty($date_range) || $date_range['refresh_end_date'] == 'Y') {
    $date_range['start_date'] = mktime(0,0,0,date('m',$ctime),1,date('Y',$ctime));
    $date_range['end_date'] = XC_TIME + $config['Appearance']['timezone_offset'];
    $date_range['refresh_end_date'] = 'Y';
}

$start_date = $date_range['start_date'];
$end_date = $date_range['end_date'];

$smarty->assign('start_date', $start_date);
$smarty->assign('end_date', $end_date);

/**
 * Process GET-request
 */

if ($mode == 'general') {

    // Collect statistics information

    $location[] = array(func_get_langvar_by_name('lbl_general_statistics'));

    $clients = func_query_hash("SELECT IF(is_anonymous_customer, 'anonymous', 'registered') AS is_anonymous_customer, count(id) FROM $sql_tbl[customers] WHERE usertype='C' GROUP BY is_anonymous_customer ORDER BY NULL", "is_anonymous_customer",  false, true);
    $statistics['clients'] = intval($clients['registered']);
    $statistics['clients_anonymous'] = empty($clients['anonymous']) ? 0 : $clients['anonymous'];
    $statistics['providers'] = func_query_first_cell("SELECT count(id) FROM $sql_tbl[customers] WHERE usertype='P'");
    $statistics['products'] = func_query_first_cell("SELECT count(productid) FROM $sql_tbl[products]");
    $statistics['categories'] = func_query_first_cell("SELECT count(categoryid) FROM $sql_tbl[categories] WHERE parentid='0'");
    $statistics['subcategories'] = func_query_first_cell("SELECT count(categoryid) FROM $sql_tbl[categories] WHERE parentid!='0'");
    $statistics['orders'] = func_query_first_cell("SELECT count(orderid) FROM $sql_tbl[orders]");

    $start_date_off = $start_date - $config["Appearance"]["timezone_offset"];
    $end_date_off = $end_date - $config["Appearance"]["timezone_offset"];

    $time_login_cond = " first_login>='$start_date_off' AND first_login<='$end_date_off' ";
    $add_date_cond = " add_date>='$start_date_off' AND add_date<='$end_date_off' ";
    $date_cond = " date>='$start_date_off' AND date<='$end_date_off' ";

    $clients = func_query_hash("SELECT IF(is_anonymous_customer, 'anonymous', 'registered') AS is_anonymous_customer, count(id) FROM $sql_tbl[customers] WHERE usertype='C' AND ($time_login_cond) GROUP BY is_anonymous_customer ORDER BY NULL", "is_anonymous_customer",  false, true);
    $statistics['clients_last_month'] = intval($clients['registered']);
    $statistics['clients_last_month_anonymous'] = empty($clients['anonymous']) ? 0 : $clients['anonymous'];
    $statistics['providers_last_month'] = func_query_first_cell("SELECT count(id) FROM $sql_tbl[customers] WHERE usertype='P' AND ($time_login_cond)");
    $statistics['products_last_month'] = func_query_first_cell("SELECT count(productid) FROM $sql_tbl[products] WHERE ($add_date_cond)");
    $statistics['orders_last_month'] = func_query_first_cell("SELECT count(orderid) FROM $sql_tbl[orders] WHERE ($date_cond)");
} else {
    $start_date_off = $start_date - $config["Appearance"]["timezone_offset"];
    $end_date_off = $end_date - $config["Appearance"]["timezone_offset"];
    $date_condition = "(ss.date>='$start_date_off' AND ss.date<='$end_date_off')";

    if (in_array($mode, array('logins'))) {
        include $xcart_dir.DIR_ADMIN.'/atracking.php';
    }
    elseif($mode == 'adaptives') {
        $location[] = array(func_get_langvar_by_name('lbl_browser_settings'));
        $statistics = func_query("SELECT * FROM $sql_tbl[stats_adaptive]");
    }
    elseif($mode == 'users_online' && !empty($active_modules['Users_online'])) {
        $location[] = array(func_get_langvar_by_name('lbl_users_online'));
        include $xcart_dir.'/modules/Users_online/stats.php';
    }
    elseif($mode == 'search') {
        $location[] = array(func_get_langvar_by_name('lbl_search_statistics'));
        include $xcart_dir.DIR_ADMIN.'/stats_search.php';
    }
}

/**
 * Assign Smarty variables and show template
 */
$smarty->assign('statistics', $statistics);
$smarty->assign('mode', $mode);
$smarty->assign('main', 'statistics');

// Assign the current location line
$smarty->assign('location', $location);

// Assign the section navigation data
$smarty->assign('dialog_tools_data', $dialog_tools_data);

if (is_readable($xcart_dir.'/modules/gold_display.php')) {
    include $xcart_dir.'/modules/gold_display.php';
}
func_display('admin/home.tpl',$smarty);
?>
