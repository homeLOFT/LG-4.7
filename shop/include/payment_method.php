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
 * CC processing payment module
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Lib
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v125 (xcart_4_7_0), 2015-02-17 23:56:28, payment_method.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if (is_readable('../top.inc.php')) {
    include_once '../top.inc.php';
}

if (!defined('XCART_START')) die("ERROR: Can not initiate application! Please check configuration.");

include_once $xcart_dir . '/payment/auth.php';

x_load(
    'cart',
    'user'
);

x_session_register('cart');
x_session_register('order_secureid');
x_session_register('intershipper_rates');
x_session_register('dhl_ext_country_store');

if (
    func_is_ajax_request()
    && $action == 'apply_gc'
) {
    return;
}

if (empty($logged_userid)) {
    $logged_userid = 0;
}

// This is call from XPC iframe on One Page Checkout
if (
    !empty($active_modules['XPayments_Connector'])
    && $config['XPayments_Connector']['xpc_use_iframe'] == 'Y'
) {

    func_xpay_func_load(); 

    if (xpc_get_module_params($paymentid)) {
        define('IS_XPC_IFRAME', 1);
        if (defined('XPC_API_1_3_COMPATIBLE')) {
            define('XPC_OVERRIDE_INITIAL_ORDER_STATUS', 'X');
        }
    }
}

$userinfo = func_userinfo($logged_userid, $login_type, false, false, array('C','H'));

if (
    !empty($active_modules['Adv_Mailchimp_Subscription'])
) {
     func_mailchimp_batch_subscribe($userinfo);
  } elseif (
    !empty($active_modules['Mailchimp_Subscription'])
    && !empty($mailchimp_subscription)
    && !empty($userinfo['email'])
) {
    $mailchimp_response = func_mailchimp_subscribe($userinfo);
}

// Check if customer accepted terms and conditions
if (
    !isset($_POST['accept_terms'])
    || empty($_POST['accept_terms'])
) {

    $top_message = array(
        'type'      => 'E',
        'content'   => func_get_langvar_by_name('txt_accept_terms_err'),
        'xpc_type'  => func_constant('XPC_REDIRECT_DO_NOTHING'),
    );

    func_header_location($xcart_catalogs['customer'] . '/cart.php?mode=checkout&paymentid=' . $paymentid);

}

/**
 * Get userinfo and cart products and output an error if empty
 */
if (
    empty($userinfo)
    || func_is_cart_empty($cart)
    || (
        empty($_POST['paymentid'])
        && $cart['total_cost'] > 0
    )
) {

    $top_message = array(
        'type'      => 'E',
        'content'   => func_get_langvar_by_name('err_payment_processor_msg'),
        'xpc_type'  => func_constant('XPC_REDIRECT_DO_NOTHING'),
    );

    func_header_location($xcart_catalogs['customer'] . "/cart.php?mode=checkout&paymentid=" . $paymentid);

}

// Check required fields
if (
    !empty($userinfo)
    && (
        $is_anonymous
        || $userinfo['status'] != 'A'
    )
    && !func_check_required_fields($userinfo)
) {
    $top_message = array(
        'type' => 'E',
        'content' => func_get_langvar_by_name('txt_registration_error'),
        'xpc_type'=> func_constant('XPC_REDIRECT_DISPLAY_ERROR'),
    );
}

/**
 * Check if paymentid can be used
 */
if (!func_is_valid_payment_method($paymentid)) {
    $cart = func_cart_set_paymentid($cart, 0);
    $top_message = array(
        'type'    => 'E',    
        'content' => func_get_langvar_by_name('err_payment_cc_not_available'),
        'xpc_type'=> func_constant('XPC_REDIRECT_DO_NOTHING'),
    );

    func_header_location($xcart_catalogs['customer'] . '/cart.php');

} else {
    $cart = func_cart_set_paymentid($cart, $paymentid);
}

$payment_info = func_query_first("SELECT * FROM $sql_tbl[payment_methods] WHERE paymentid = '$paymentid'");
if (
    $payment_info['payment_script'] != basename($php_url['url'])
    && $cart['total_cost'] > 0
) {
    func_header_location($xcart_catalogs['customer'] . "/error_message.php?error_ccprocessor_baddata");
}

$userinfo = func_array_merge($userinfo, $_POST);

if (!empty($paymentid)) {

    if (func_query_first_cell("SELECT COUNT(*) FROM $sql_tbl[payment_methods] WHERE paymentid = '$paymentid' AND af_check = 'Y'")) {

        define('IS_AF_CHECK', true);

    }

}

// Only for compatibility with old code in payment modules.
// Please, use $cart['products'] in new code instead of $products.
if (
    !empty($cart['products'])
    && is_array($cart['products'])
) {

    $products = $cart['products'];

} else {

    $products = array();

}

$bill_firstname = empty($userinfo['b_firstname'])
    ? $userinfo['firstname']
    : $userinfo['b_firstname'];

$bill_lastname     = empty($userinfo['b_lastname'])
    ? $userinfo['lastname']
    : $userinfo['b_lastname'];

$bill_name         = $bill_firstname;

if (!empty($bill_lastname)) {
    $bill_name .= (
        empty($bill_firstname)
            ? ''
            : " "
    )
    . $bill_lastname;
}

$ship_firstname = empty($userinfo['s_firstname'])
    ? $userinfo['firstname']
    : $userinfo['s_firstname'];

$ship_lastname     = empty($userinfo['s_lastname'])
    ? $userinfo['lastname']
    : $userinfo['s_lastname'];

$ship_name         = $ship_firstname;

if (!empty($ship_lastname)) {
    $ship_name .= (
        empty($ship_firstname)
            ? ''
            : " "
    )
    . $ship_lastname;
}

// Check non existing/updated Gift certificate in the applied_giftcerts array
if (
    !empty($active_modules['Gift_Certificates'])
    && !empty($cart['applied_giftcerts'])
) {

    if (
        !empty($active_modules['XPayments_Connector'])
        && $config['XPayments_Connector']['xpc_use_iframe'] == 'Y'
    ) {
        func_xpc_reset_giftcerts($userinfo);
    }

    $_invalid_gcs = func_check_applied_giftcerts();

    if (
        !empty($_invalid_gcs)
        && is_array($_invalid_gcs)
    ) {
        $top_message = array(
            'type' => 'E',
            'content' => func_get_langvar_by_name('err_gc_invalid_gcs', array('invalid_gcs' => implode(', ', $_invalid_gcs))),
            'xpc_type'=> func_constant('XPC_REDIRECT_DISPLAY_ERROR'),
        );

        func_header_location($xcart_catalogs['customer'] . "/cart.php?mode=checkout&err=gc_notfound&paymentid=" . $paymentid);
    }
}

if (
    isset($cart['split_query'])
    && isset($cart['split_query']['paid_amount'])
) {

    // Paid amount must be substracted from Total cost
    $cart['total_cost'] -= $cart['split_query']['paid_amount'];

}

?>
