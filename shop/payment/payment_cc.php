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
 * @subpackage Payment interface
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v167 (xcart_4_7_0), 2015-02-17 23:56:28, payment_cc.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

require '../include/payment_method.php';

x_load(
    'crypt',
    'order',
    'payment',
    'tests'
);

x_session_unregister('logged_paymentid');

if ($REQUEST_METHOD != 'POST') {

    func_header_location($current_location . DIR_CUSTOMER . "/cart.php?mode=checkout");

}

$paymentid = intval($paymentid);

// Get parameters of the payment module
$module_params = func_get_pm_params($paymentid);

if (!empty($active_modules['Klarna_Payments'])) {
    
    if (!func_klarna_check_input_params($module_params, $userinfo, ((isset($user_ssn)) ? $user_ssn : false), ((isset($selected_pclass)) ? $selected_pclass : false))) {
         func_header_location($current_location . DIR_CUSTOMER . '/cart.php?mode=checkout');
    }

}

if (
    $checkout_module == 'One_Page_Checkout'
    && isset($module_params['processor'])
    && (
        $module_params['processor'] == 'ps_paypal_pro.php'
        || ($module_params['processor'] == 'ps_paypal_bml.php' && $config['paypal_solution'] != 'ipn')
    )
) {

    if ($module_params['processor'] == 'ps_paypal_pro.php') {
        $paypal_express_paymentid = func_cart_get_paypal_express_id();
    } else {
        $paypal_express_paymentid = func_cart_get_paypal_bml_id();
    }

    if (
        $paypal_express_paymentid == $paymentid
        && !func_is_confirmed_paypal_express()
    ) {

        func_paypal_express_disable_1step();

        x_session_register('PPEC_POST_VARS');
        $PPEC_POST_VARS = $_POST;

        func_header_location($current_location . '/payment/' . $module_params['processor'] . '?payment_id=' . $paymentid . '&mode=express&useraction=commit');
    

    }
}

$use_iframe = (!empty($module_params['background']) && $module_params['background'] == 'I');

if ($use_iframe) {
    $smarty->assign('use_iframe', 'Y');
}

if (
    (
        empty($xpc_iframe)
        || $xpc_iframe != 'Y'
    )
    && (
        !$use_iframe
        || !empty($disable_js_iframe)
        || !empty($active_modules['Fast_Lane_Checkout'])
    )
) {
    require_once $xcart_dir . '/include/payment_wait.php';
}

$is_paypal_pro = func_query_first_cell("SELECT COUNT(*) FROM " . $sql_tbl['payment_methods'] . " WHERE paymentid='" . $paymentid . "' AND processor_file='ps_paypal_pro.php'");

if ($is_paypal_pro) {

    $is_emulated_paypal = false;

    if (!empty($active_modules['XPayments_Connector'])) {

        func_xpay_func_load();

        $is_emulated_paypal = xpc_is_emulated_paypal($paymentid);

    }

    if ($is_emulated_paypal) {

        $payment_cc_data = xpc_get_module_params($paymentid);

    } else {

        $payment_cc_data = func_get_pm_params('ps_paypal_pro.php');

    }

} else {

    $payment_cc_data = func_get_pm_params($paymentid);

}

/**
 * Make order details
 */
$_order_details_rval = array();

foreach (func_order_details_fields(true) as $_details_field => $_field_label) {

    if (isset($GLOBALS[$_details_field])) {
        // changing this line can affect func_order_remove_chinfo
        $_order_details_rval[] = $_field_label . ": " . stripslashes($GLOBALS[$_details_field]);

    }

}

$order_details = implode("\n", $_order_details_rval);

$customer_notes = $Customer_Notes;


if ($is_paypal_pro) {

    $module_params = func_get_pm_params('ps_paypal_pro.php');

    $module_params['cmpi'] = in_array($config['paypal_solution'], array('uk', 'pro'))
        ? 'Y'
        : 'N';

}

if (!empty($module_params['processor'])) {

    x_session_register('logged_paymentid');

    $logged_paymentid = $paymentid;

    // Get active processor's data and module parameters

    $duplicate = true;

    x_session_register('secure_oid');
    x_session_register('secure_oid_cost');
    x_session_register('initial_state_orders', array());
    x_session_register('initial_state_show_notif', 'Y');

    $current_cart_hash = func_calculate_cart_hash($cart);

    if (
        !empty($cart['split_query'])
        && $cart['total_cost'] <= 0
    ) {

        $top_message = array(
            'content'   => func_get_langvar_by_name('lbl_total_cost_less_than_paid_amount'),
            'type'      => 'I',
        );

        // if customer already paid amount more than total cost then (s)he MUST remove some transactions 
        func_header_location('cart.php?mode=checkout&paymentid=' . $paymentid);

    }

    if (
        !empty($cart['split_query'])
        && $cart['split_query']['cart_hash'] === $current_cart_hash
    ) {

        $orderids    = $cart['split_query']['orderid'];
        $paid_amount = $cart['split_query']['paid_amount'];

    } elseif (
        empty($secure_oid)
        || $secure_oid_cost != $cart['total_cost']
    ) {

        // Put order in table

        $extra = array();

        $in_testmode = get_cc_in_testmode($module_params);

        if ($in_testmode) {
            $extra['in_testmode'] = $in_testmode;
        }

        if (strpos($module_params['processor'], 'ps_paypal') !== false) {
            $payment_method_text = stripslashes($payment_method) . (($in_testmode) ? ' (in test mode)' : '');
        } else {
            $payment_method_text = stripslashes($payment_method) . ' (' . $module_params['module_name'] . (($in_testmode) ? ', in test mode' : '') . ')';
        }

        $initial_order_status = 'I';

        if (defined('XPC_OVERRIDE_INITIAL_ORDER_STATUS')) {
            $initial_order_status = XPC_OVERRIDE_INITIAL_ORDER_STATUS;
        }

        $orderids = func_place_order(
            $payment_method_text,
            $initial_order_status,
            $order_details,
            $customer_notes,
            $extra
        );

        if (
            empty($orderids)
            || in_array($orderids, XCPlaceOrderErrors::getAllCodes())
        ) {

            $top_message = array(
                'content'   => func_get_langvar_by_name('txt_err_place_order_' . $orderids),
                'type'      => 'E',
                'xpc_type'  => func_constant('XPC_REDIRECT_ALERT_ERROR'),
            );

            func_header_location($xcart_catalogs['customer'] . "/cart.php?mode=checkout&paymentid=" . $paymentid);
        }

        $secure_oid      = $orderids;
        $secure_oid_cost = $cart['total_cost'];
        $duplicate       = false;

        $initial_state_orders     = func_array_merge($initial_state_orders, $orderids);
        $initial_state_show_notif = 'Y';

    } else {

        $orderids = $secure_oid;

    }

    func_split_checkout_check_decline_order($cart, $orderids);

    if (
        func_is_preauth_force_enabled($orderids)
        && $module_params['has_preauth'] != 'Y'
    ) {

        define('STATUS_CHANGE_REF', 6);

        func_change_order_status(
            $orderids,
            'Q',
            func_get_langvar_by_name('txt_antifraud_order_note', array(), $config['default_admin_language'], true)
        );

        x_session_register('cart');

        $cart = '';

        func_header_location($xcart_catalogs['customer'] . "/cart.php?mode=order_message&orderids=" . func_get_urlencoded_orderids($orderids));

        exit;

    }

    // Do not remove as some payment calls take a lot of time
    x_session_save();

    // Set CVV2 info line...
    $a = isset($userinfo['card_cvv2']) ? strlen($userinfo['card_cvv2']) : 0;

    $bill_output = array(
        'cvvmes' => ($a ? ($a . ' digit(s)') : 'not set') . ' / ',
    );

    func_pm_load(basename($module_params['processor']));

    if (
        $module_params['cmpi'] == 'Y'
        && file_exists($xcart_dir . '/payment/cmpi.php')
        && $config['CMPI']['cmpi_enabled'] == 'Y'
        && in_array($card_type, array('VISA', 'MC', 'JCB', 'SW'))
    ) {

        require $xcart_dir . '/payment/cmpi.php';

    } elseif ($module_params['cmpi'] == 'B') {

        require $xcart_dir . '/payment/3dsecure.php';

    }

    if ($module_params['background'] == 'I' && (!empty($active_modules['Fast_Lane_Checkout']) || !empty($disable_js_iframe))) {

        if ($module_params['processor'] != 'ps_paypal_advanced.php' && $module_params['processor'] != 'ps_paypal_payflowlink.php' ) {
           $smarty->assign('payment_method', $module_params['module_name']);
        } else {
            $smarty->assign('payment_method', 'PayPal');
        }

        func_flush(func_display('payments/iframe_init.tpl', $smarty, false));

    }

    if (
        isset($cart['split_query']['transaction_query'][$paymentid])
        && !empty($cart['split_query']['transaction_query'][$paymentid])
    ) {
        define('POSSIBLE_TRANSACTION_QUERY', true);
    }

    require $xcart_dir . '/payment/' . basename($module_params['processor']);

    require $xcart_dir . '/payment/payment_ccend.php';

} else {

    // Manual processing

    $orderids = func_place_order(
        stripslashes($payment_method) . " (manual processing)",
        'Q',
        $order_details,
        $customer_notes
    );

    if (
        empty($orderids)
        || in_array($orderids, XCPlaceOrderErrors::getAllCodes())
    ) {

        $top_message = array(
            'content' => func_get_langvar_by_name('txt_err_place_order_' . $orderids),
            'type'    => 'E',
        );

        func_header_location($xcart_catalogs['customer']."/cart.php?mode=checkout&paymentid=".$paymentid);

    }

    $_orderids = func_get_urlencoded_orderids ($orderids);

    $cart = '';

    func_header_location($xcart_catalogs['customer'] . "/cart.php?mode=order_message&orderids=$_orderids");

}

exit;

?>
