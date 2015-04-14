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
 * Core module
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Modules
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v30 (xcart_4_7_0), 2015-02-17 23:56:28, generator.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if ( !defined('XCART_SESSION_START') ) { header("Location: ../../"); die("Access denied"); }

x_load('order');

$location[] = array(func_get_langvar_by_name('lbl_shipping_labels'));
$dialog_tools_data['left'][] = array("link" => "orders.php", "title" => func_get_langvar_by_name("lbl_orders"));
$dialog_tools_data['left'][] = array('link' => "configuration.php?option=Shipping_Label_Generator", 'title' => func_get_langvar_by_name('option_title_Shipping_Label_Generator'));

x_session_register('slg_orderids');
x_session_register('slg_ups_orders');
x_session_register('slg_img_orders');

// Restore order IDs from session
if (empty($orderids) && !empty($slg_orderids)) {
    $orderids = $slg_orderids;
}

if (!isset($orderids_update) || !is_array($orderids_update)) {
    $orderids_update = array();
}

// Get order/orders labels data
$orders = array();
if (isset($orderids) && !empty($orderids)) {
    // Type cast, if we got called with a single order ID as input.
    if (!is_array($orderids)) {
        if (is_numeric($orderids)) {
            $orderids = array($orderids => true);
        } else {
            func_403(75);
        }
    }

    $orders = func_slg_get_orders_labels_data($orderids, $orderids_update, $mode);
}

// Check if we got information about orders with labels.
if (empty($orders) || !is_array($orders)) {
    func_403(42);
}

// Store collected information in a session
$slg_orderids = array_flip(array_keys($orders));

if ($REQUEST_METHOD == 'POST') {
    func_header_location('generator.php' . (!empty($QUERY_STRING) ? '?'.$QUERY_STRING : ''));
}

$smarty->assign('orders', $orders);
$smarty->assign('have_ups_orders', !empty($slg_ups_orders) ? 'Y' : '');
$smarty->assign('have_img_orders', !empty($slg_img_orders) ? 'Y' : '');

// Assign the section navigation data
$smarty->assign('dialog_tools_data', $dialog_tools_data);
?>
