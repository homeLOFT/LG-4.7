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
 * Delete all orders
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Lib
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v41 (xcart_4_7_0), 2015-02-17 23:56:28, orders_deleteall.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if ( !defined('XCART_SESSION_START') ) { header("Location: ../"); die("Access denied"); }

x_log_flag('log_orders_delete', 'ORDERS', "Login: $login\nIP: $REMOTE_ADDR\nOperation: delete all orders", true);

/**
 * Delete ALL orders and move them to the orders_deleted table
 */

$xaff = func_is_defined_module_sql_tbl('XAffiliate', 'partner_payment');
$xrma = func_is_defined_module_sql_tbl('RMA', 'returns');
$xaom = func_is_defined_module_sql_tbl('Advanced_Order_Management', 'order_status_history'); 

$lock_tables = array(
    'orders',
    'order_details',
    'giftcerts',
    'order_extras'
    );

if ($xaff) {
    $lock_tables[] = 'partner_payment';
    $lock_tables[] = 'partner_product_commissions';
    $lock_tables[] = 'partner_adv_orders';
}

if ($xrma) {
    $lock_tables[] = 'returns';
}

if ($xaom) {
    $lock_tables[] = 'order_status_history';
}

foreach ($lock_tables as $k => $v) {
    if (isset($sql_tbl[$v]))
        $lock_tables[$k] = $sql_tbl[$v]." WRITE";
}

db_query("LOCK TABLES ".implode(', ', $lock_tables));

db_query("DELETE FROM $sql_tbl[orders]");
db_query("DELETE FROM $sql_tbl[order_details]");
db_query("DELETE FROM $sql_tbl[order_extras]");
db_query("DELETE FROM $sql_tbl[giftcerts]");

if ($xaff) {
    db_query("DELETE FROM $sql_tbl[partner_payment]");
    db_query("DELETE FROM $sql_tbl[partner_product_commissions]");
    db_query("DELETE FROM $sql_tbl[partner_adv_orders]");
}

if ($xrma) {
    db_query("DELETE FROM $sql_tbl[returns]");
}

if ($xaom) {
    db_query("DELETE FROM $sql_tbl[order_status_history]");
}

db_query("UNLOCK TABLES");

$smarty->assign('deleteall','true');

?>
