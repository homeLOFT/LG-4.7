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
 * Display top performers statistics
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Admin interface
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    3968cba5ecdb78320d43cbe05a25fe35597bc800, v35 (xcart_4_7_0), 2015-02-17 13:29:01, partner_top_performers.php, aim
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

require __DIR__.'/auth.php';
require $xcart_dir.'/include/security.php';

if (empty($active_modules['XAffiliate']))
    func_403(24);

$location[] = array(func_get_langvar_by_name('lbl_top_performers'), '');

/**
 * Define data for the navigation within section
 */
$dialog_tools_data['left'][] = array('link' => 'banner_info.php', 'title' => func_get_langvar_by_name('lbl_banners_statistics'));
$dialog_tools_data['left'][] = array('link' => 'referred_sales.php', 'title' => func_get_langvar_by_name('lbl_referred_sales'));
$dialog_tools_data['left'][] = array('link' => 'partner_top_performers.php', 'title' => func_get_langvar_by_name('lbl_top_performers'));
$dialog_tools_data['left'][] = array('link' => 'affiliates.php', 'title' => func_get_langvar_by_name('lbl_affiliates_tree'));
$dialog_tools_data['left'][] = array('link' => 'partner_adv_stats.php', 'title' => func_get_langvar_by_name('lbl_adv_statistics'));

if ($start_date) {
    $search['start_date'] = func_prepare_search_date($start_date);
    $search['end_date']   = func_prepare_search_date($end_date, true);
}

if($search) {
    $where = array();
    if($search['start_date'] && $search['end_date'])
        $where[] = $search['end_date']." > $sql_tbl[partner_clicks].add_date AND $sql_tbl[partner_clicks].add_date > ".$search['start_date'];
    if($where)
        $where_condition = " AND ".implode(" AND ", $where);
    $result = func_query("
                         SELECT $sql_tbl[partner_clicks].*, $sql_tbl[partner_clicks].$search[report] as data,
                                $sql_tbl[customers].login AS name, COUNT($sql_tbl[partner_clicks].$search[report]) as clicks,
                                SUM(($sql_tbl[orders].subtotal - $sql_tbl[orders].discount -
                                $sql_tbl[orders].coupon_discount)) as
                                sales, COUNT($sql_tbl[orders].subtotal) as num_sales
                           FROM $sql_tbl[partner_clicks]
                           INNER JOIN $sql_tbl[customers]
                             ON $sql_tbl[partner_clicks].userid = $sql_tbl[customers].id ".$where_condition."
                           LEFT JOIN $sql_tbl[orders]
                             ON $sql_tbl[partner_clicks].clickid         = $sql_tbl[orders].clickid
                          GROUP BY $sql_tbl[partner_clicks].$search[report]
                          ORDER BY ".$search['sort']." DESC");
    if($result) {
        $smarty->assign('result', $result);
    } elseif (empty($top_message['content'])) {
        $no_results_warning = array('type' => 'W', 'content' => func_get_langvar_by_name("lbl_warning_no_search_results", false, false, true));
        $smarty->assign('top_message', $no_results_warning);
    }
}

$smarty->assign ('main', 'partner_top_performers');

$smarty->assign('search', $search);

$smarty->assign ('month_begin', mktime(0,0,0,date('m'),1,date('Y')));

// Assign the current location line
$smarty->assign('location', $location);

// Assign the section navigation data
$smarty->assign('dialog_tools_data', $dialog_tools_data);

if (is_readable($xcart_dir.'/modules/gold_display.php')) {
    include $xcart_dir.'/modules/gold_display.php';
}
func_display('admin/home.tpl',$smarty);
?>
