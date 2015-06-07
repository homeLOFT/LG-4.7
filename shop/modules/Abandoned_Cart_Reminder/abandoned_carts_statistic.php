<?php
/* vim: set ts=4 sw=4 sts=4 et: */
/*****************************************************************************\
+-----------------------------------------------------------------------------+
| X-Cart Software license agreement                                           |
| Copyright (c) 2001-2013 Qualiteam software Ltd <info@x-cart.com>            |
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
 *
 * @category   X-Cart
 * @package    X-Cart
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2013 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    $Id$
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if (!defined('XCART_START')) {
    header('Location: ../../');
    die('Access denied');
}

$location[] = array(func_get_langvar_by_name('lbl_abcr_order_statistic'), '');

$time_variables = array (
    'abcr_start_Month'  => date('n'),
    'abcr_start_Year'   => date('Y') - 1,
    'abcr_end_Month'  => date('n'),
    'abcr_end_Year'   => date('Y'),
);

foreach ($time_variables as $name => $default_value) {

    if (!isset($$name)) {
        $$name = $default_value;
    }

}

$date_param = array (
    'start' => mktime(0, 0, 0, $abcr_start_Month, 1, $abcr_start_Year),
    'end'   => mktime(23, 59, 59, $abcr_end_Month + 1, 0, $abcr_end_Year),
);

list($recovering_statistic, $totals) = func_abcr_get_order_statistic($date_param, (($current_area == 'P' && empty($single_mode)) ? $logged_userid : 0));

$smarty->assign('location', $location);
$smarty->assign('main', 'abandoned_carts_statistic');

$abcr_start_year = func_query_first_cell('SELECT date FROM ' . $sql_tbl['orders'] . ' WHERE orderid = (SELECT MIN(orderid) FROM ' . $sql_tbl['abcr_notifications'] . ')');
$abcr_start_year = date('Y', $abcr_start_year) - 1;

$smarty->assign('recovering_statistic', $recovering_statistic);
$smarty->assign('totals', $totals);
$smarty->assign('abcr_start_year', $abcr_start_year); // Yes, it is hardcoded to the year when module was created
$smarty->assign('abcr_dates', $date_param);

if (
    file_exists($xcart_dir . '/modules/gold_display.php')
    && is_readable($xcart_dir . '/modules/gold_display.php')
) 
{
    include $xcart_dir . '/modules/gold_display.php';
}

$display_area = ($current_area == 'A' || $single_mode) ? 'admin' : 'provider';

func_display($display_area . '/home.tpl',$smarty);

?>
