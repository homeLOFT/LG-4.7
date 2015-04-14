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
 * This script implements checkout facility
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Cart
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v37 (xcart_4_7_0), 2015-02-17 23:56:28, checkout.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if ( !defined('XCART_SESSION_START') ) { header('Location: ../'); die('Access denied'); }

// Common checkout code goes here

if (
    $cart['display_subtotal'] < $config['General']['minimal_order_amount']
    && $config['General']['minimal_order_amount'] > 0
) {
    $_min_allowed_subtotal = $smarty->formatCurrency(array('value' => $config['General']['minimal_order_amount'], 'plain_text_message' => TRUE));

    // ERROR: Cart total must exceeds the minimum order total amount (defined in General settings)
    $top_message = array (
        'content' => func_get_langvar_by_name('err_checkout_not_allowed_msg', array('value' => $_min_allowed_subtotal)),
        'type' => 'E',
    );

    func_header_location('cart.php');
}

if (
    $config['General']['maximum_order_amount'] > 0
    && $cart['display_subtotal'] > $config['General']['maximum_order_amount']
) {

    // ERROR: Cart total must not exceed the maximum order total amount
    // (defined in General settings)

    $_max_allowed_subtotal = $smarty->formatCurrency(array('value' => $config['General']['maximum_order_amount'], 'plain_text_message' => TRUE));

    $top_message = array (
        'content' => func_get_langvar_by_name('err_checkout_max_order_msg', array('value' => $_max_allowed_subtotal)),
        'type' => 'E',
    );

    func_header_location('cart.php');
}

if (
    $config['General']['maximum_order_items'] > 0
    && func_cart_count_items($cart) > $config['General']['maximum_order_items']
) {

    // ERROR: Cart total must not exceed the maximum total quantity
    // of products in an order (defined in General settings)
    $top_message = array (
        'content' => func_get_langvar_by_name('err_checkout_max_items_msg', array('quantity' => $config['General']['maximum_order_items'])),
        'type' => 'E',
    );

    func_header_location('cart.php');
}

if (!empty($partner)) {
    $smarty->assign('partner', $partner);
}    

if (
    empty($login)
    && $config['General']['enable_anonymous_checkout'] == 'Y'
) {
    // Anonymous checkout
    $smarty->assign('anonymous', 'Y');
}

// Count available shipping carriers
$carriers_count = 0;

if (
    isset($_carriers)
    && is_array($_carriers)
    && isset($_carriers['UPS'])
    && isset($_carriers['other'])
) {
    $carriers_count = $_carriers['UPS'] + $_carriers['other'];
}

// Generate uniq orderid which will identify order session
$order_secureid = md5(uniqid(mt_rand()));

if (
    !empty($active_modules['Google_Analytics'])
    && $config['Google_Analytics']['ganalytics_e_commerce_analysis'] == 'Y'
) {
    $ga_track_commerce = 'Y';
}

x_session_register('login_antibot_on');

if ($login_antibot_on) {
    // Show antibot image after 3 unsucceful attempts to login
    $smarty->assign('login_antibot_on', $login_antibot_on);
}

// Do not show the 'on_registration antibot image' for customers passed verification procedure
x_load('user');
$_anonymous_userinfo = func_get_anonymous_userinfo();
$display_antibot = empty($login) && empty($_anonymous_userinfo);
$smarty->assign('display_antibot', $display_antibot);

define('CHECKOUT_STARTED', 1);

include $xcart_dir . '/modules/' . $checkout_module . '/checkout.php';

$smarty->assign('paymentid',        $paymentid);
if (!empty($payment_cc_data)) {
    $smarty->assign('payment_cc_data', $payment_cc_data);
}
if (!empty($payment_data)) {
    $smarty->assign('payment_data', $payment_data);
}
$smarty->assign('userinfo',         $userinfo);
$smarty->assign('main',             'checkout');
$smarty->assign('payment_methods',  $payment_methods);

?>
