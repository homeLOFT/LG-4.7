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
 * Perform initial checks before and after checkout
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Cart
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    6baaddca397672f05266e7cfbc61c883e0b3bb5b, v86 (xcart_4_7_2), 2015-04-13 18:04:34, checkout_init.php, aim
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

x_load(
    'order',
    'tests',
    'shipping'
);

x_session_register('intershipper_rates');
x_session_register('intershipper_recalc');
x_session_unregister('secure_oid');
x_session_register('current_carrier','UPS');
x_session_register('order_secureid');
x_session_register('dhl_ext_country_store');
x_session_register('ga_track_commerce');
x_session_register('initial_state_orders', array());
x_session_register('initial_state_show_notif', 'Y');
x_session_register('reg_error', array());

/**
 * Define checkout module
 */

if (
    !empty($active_modules['Amazon_Checkout'])
    && $mode == 'acheckout'
) {
    if ($mode == 'acheckout') {

        $mode = 'checkout';

        $checkout_module = 'Amazon_Checkout';

    }
}

if (isset($dhl_ext_country)) {

    $dhl_ext_country_store = $dhl_ext_country;

} else {

    $dhl_ext_country = $dhl_ext_country_store;

}

/**
 * Stop list module: check transaction
 */
if (
    !empty($active_modules['Stop_List'])
    && !$func_is_cart_empty
    && !func_is_allowed_trans()
) {
    if(
        $mode == 'checkout'
        || $mode == 'auth'
    ) {
        $top_message['content'] = func_get_langvar_by_name('txt_stop_list_customer_note');
        $top_message['type']     = 'E';

        func_header_location('cart.php');
    }

    $smarty->assign('unallowed_transaction', 'Y');
}

/**
 * Check available payment methods
 */
$payment_methods = array();

$paypal_express_enabled = func_cart_get_paypal_express_id();
/*
 * Get paymentid based on cart[paymentid]/$paymentid
 */
$paymentid = func_cart_get_paymentid($cart, $checkout_module);
$cart = func_cart_set_paymentid($cart, $paymentid);

if (!$func_is_cart_empty) {

    $payment_methods = check_payment_methods(@$user_account['membershipid']);

    if (empty($payment_methods))
        $smarty->assign('std_checkout_disabled', 'Y');


    if (
        $checkout_module == 'One_Page_Checkout'
        && $paypal_express_enabled
        && isset($_GET['express_cancel'])
    ) {
        $_new_methodid = ($paypal_express_enabled != $paymentid) ? $paymentid : 0;
        if (!empty($active_modules['Bill_Me_Later']) && $_new_methodid != 0) {
            $_new_methodid = (func_cart_get_paypal_bml_id() != $paymentid) ? $paymentid : 0;
        }
        $cart = func_cart_set_paymentid($cart, $_new_methodid);
        $paymentid = $_new_methodid;
    }
}

if (!empty($active_modules['Klarna_Payments'])) {
    $payment_methods = func_klarna_correct_payments($payment_methods);
}

/**
 * Calculate total number of checkout process steps
 */
if (
    $mode == 'checkout'
    && !$func_is_cart_empty
) {

    $total_checkout_steps = 2;

    $checkout_step_modifier['anonymous'] = 0;
    $checkout_step_modifier['payment_methods'] = 0;

    if ($is_anonymous) {
        $total_checkout_steps ++;
        $checkout_step_modifier['anonymous'] = 1;
    }

    if (
        empty($payment_methods)
        && !in_array($checkout_module, array('Amazon_Checkout'))
    ) {

        if (
            empty($paypal_express_enabled)
            && empty($active_modules['Amazon_Checkout'])
        ) {
            $top_message['content'] = func_get_langvar_by_name('txt_no_payment_methods');
            $top_message['type']    = 'E';
        }

        func_header_location('cart.php');

    } elseif (count($payment_methods) == 1) {

        $total_checkout_steps --;

        $checkout_step_modifier['payment_methods'] = 1;

    }

    $smarty->assign('total_checkout_steps', $total_checkout_steps);
}

/**
 * Notifications about uncompleted orders.
 */
if ($mode == 'disable_init_state_notif') {

    $initial_state_show_notif = '';

    func_header_location(func_is_internal_url($HTTP_REFERER) ? $HTTP_REFERER : 'home.php');

} elseif (
    is_array($initial_state_orders)
    && !empty($initial_state_orders)
    && (
        empty($active_modules['XPayments_Connector'])
        || $config['XPayments_Connector']['xpc_use_iframe'] != 'Y'
    )
) {
    $oids = array();

    foreach ($initial_state_orders as $k => $v) {

        if (func_query_first_cell("SELECT status FROM $sql_tbl[orders] WHERE orderid = '" . (int)$v . "'") == 'I') {

            $oids[] = $v;

        } else {

            unset($initial_state_orders[$k]);

        }

    }

    if (
        !empty($oids)
        && empty($top_message)
        && !$smarty->getTemplateVars('top_message')
        && $initial_state_show_notif == 'Y'
    ) {
        $lng_var = count($oids) > 1
            ? 'txt_warn_unfinished_orders'
            : 'txt_warn_unfinished_order';

        $message = array(
            'content' => func_get_langvar_by_name(
                $lng_var,
                array(
                    'orders'            => join(', ', $oids),
                    'customer_area_url' => $xcart_catalogs['customer']
                ),
                false,
                true
            ),
            'type' => 'W'
        );

        $smarty->assign('top_message', $message);

    }

}

/**
 * User cannot operate with cart while processing order on Amazon Checkout
 */
$_cart_locked = func_cart_is_locked(); 
if (
    !empty($_cart_locked)
    && !(
        $mode == 'add2wl'
        || $mode == 'wishlist'
    )
) {
    $_ref = func_query_first_cell("SELECT ref FROM $sql_tbl[cc_pp3_data] WHERE sessid='$XCARTSESSID'");

    $msg = "Customer returned to the store before Amazon checkout completed processing the payment transaction. ReferenceID: '$_ref'; sessid: '$XCARTSESSID'. Transaction declined by the store.";

    x_log_flag('log_payment_processing_errors', 'PAYMENTS', $msg, true);

    if (
        !empty($active_modules['Amazon_Checkout'])
        && $_cart_locked == 'by_Amazon_Checkout'
    ) {
        db_query("DELETE FROM $sql_tbl[amazon_data] WHERE sessid='$XCARTSESSID'");
        func_acheckout_debug('\t+ [Error] ' . $msg);
    }

    db_query("DELETE FROM $sql_tbl[cc_pp3_data] WHERE sessid='$XCARTSESSID'");
    func_cart_unlock();
}

/**
 * Get userinfo
 */

$userinfo = func_userinfo($logged_userid, !empty($login) ? $user_account['usertype'] : '', false, false, 'H');


if (!empty($active_modules['TaxCloud'])) {
    include $xcart_dir . '/modules/TaxCloud/checkout_init.php';
}

/**
 * Check required fields
 */
if (
    $REQUEST_METHOD == 'GET'
    && func_is_completed_userinfo($userinfo)
    && $mode == 'checkout'
    && (
        $is_anonymous
        || $userinfo['status'] != 'A'
    )
    && !isset($edit_profile)
    && !in_array($checkout_module, array('Amazon_Checkout'))
) {

    if (
        !func_check_required_fields($userinfo)
        || !func_check_required_fields($userinfo['address']['S'], 'H', 'address_book')
    ) {

        if (!empty($active_modules['Fast_Lane_Checkout'])) {

            $top_message = array(
                'type'    => 'E',
                'content' => func_get_langvar_by_name('txt_registration_error')
            );

        }

        $reg_error = 1;

        func_header_location('cart.php?mode=checkout&edit_profile&paymentid=' . $paymentid);

    }

}

/**
 * Register customer if not registerred yet
 * (not a newbie - do not show help messages)
 */
if (
    $mode == 'checkout'
    && !$func_is_cart_empty
) {
    $usertype   = 'C';
    $old_action = $action;
    $action     = 'cart';
    $newbie     = 'Y';
    $main       = 'checkout';

    $smarty->assign('action', $action);

    // Adjust mode and include registration script
    $mode = 'update';

    include $xcart_dir . '/include/register.php';

    $mode = 'checkout';

    if (!empty($auto_login)) {

        func_header_location('cart.php?mode=checkout&registered=');

    }

    $action = $old_action;

    $smarty->assign('newbie', $newbie);

    // Check if billing/shipping address section needed
    if (
        empty($userinfo['address'])
        || @$is_areas['B']
        && empty($userinfo['address']['B'])
        || @$is_areas['S']
        && empty($userinfo['address']['S'])
        || isset($edit_profile)
    ) {
        $smarty->assign('need_address_info',    true);
        $smarty->assign('force_change_address', true);
        $smarty->assign('address_fields',       func_get_default_fields('H', 'address_book'));
    }
}

/**
 * Check for the min order amount
 */
if (
    $action != 'update'
    && !$func_is_cart_empty
    && $mode == 'checkout'
) {

    $productindexes = array();

    if (!empty($cart['products'])) {

        foreach ($cart['products'] as $p) {

            $productindexes[$p['cartid']] = $p['amount'];

        }

    }

    if (!empty($productindexes)) {
        // Update the quantity of products in cart
        list($min_amount_warns, $changes) = func_update_quantity_in_cart($cart, $productindexes);

        $top_message = func_generate_min_amount_warning($min_amount_warns, $productindexes, $cart['products']);

        if (!empty($top_message)) {

            $return_url = 'cart.php';

        }

    }

}

/**
 * Display the invoice page (order confirmation page)
 */
if ($mode == 'order_message') {

    $orders = array ();

    if (!empty($orderids)) {

        x_session_register('session_orders', array());

        if (empty($login) && empty($session_orders))
            func_403(32);

        $_orderids = explode(',', $orderids);
        settype($access_key, 'string');

        if (!empty($active_modules['POS_System'])) {
            $smarty->assign('pos_receipts', func_pos_get_receipts($_orderids));
        }

        foreach ($_orderids as $orderid) {

            $order_data = func_order_data($orderid);

            // Security check if current customer is not order's owner
            if (
                empty($order_data)
                || !func_order_check_owner($order_data, $access_key)
            ) {

                unset($order_data);

                continue;

            } else {

                $order_data['products'] = func_translate_products($order_data['products'], $shop_language);

            }

            $orders[] = $order_data;

        }

    }

    if (empty($orders))
        func_403(59);

    if (
        !empty($active_modules['Google_Analytics'])
        && $config['Google_Analytics']['ganalytics_e_commerce_analysis'] == 'Y'
    ) {
        foreach ($orders as $key => $order) {

            foreach ($order['products'] as $p_key => $product) {

                $orders[$key]['products'][$p_key]['category'] = func_query_first_cell("SELECT $sql_tbl[categories].category FROM $sql_tbl[categories] INNER JOIN $sql_tbl[products_categories] WHERE $sql_tbl[categories].categoryid = $sql_tbl[products_categories].categoryid AND $sql_tbl[products_categories].productid='" . $product['productid'] . "' AND $sql_tbl[products_categories].main='Y'");

            }

        }

    }

    $smarty->assign('orders', $orders);

    $smarty->assign('ga_track_commerce', $ga_track_commerce);

    $ga_track_commerce = 'N';

    if ($action == 'print') {

        $smarty->assign('template', 'customer/main/order_message.tpl');

        func_display('customer/preview.tpl', $smarty);

        exit;

    } else {

        if (
            !empty($active_modules['XPayments_Connector'])
            && $config['XPayments_Connector']['xpc_use_iframe'] == 'Y'
        ) {
            func_xpay_func_load();
            func_xpc_delete_initial_started_orders();
        }
    
    }

    $smarty->assign('orderids', $orderids);

    $main = 'order_message';

    $location[] = array(func_get_langvar_by_name('lbl_order_processed'), '');
}

/**
 * Display the invoice page (order confirmation page) for amazon order details widget
 */
if (
    $mode == 'order_message_widget'
    && !empty($amazon_orderid)
) {
    x_session_register('session_orders_cba', array());

    if (empty($active_modules['Amazon_Checkout']))
        func_403(90);

    if (empty($session_orders_cba))
        func_403(91);

    // Security check if current customer is not order's owner
    if (!in_array($amazon_orderid, $session_orders_cba))
        func_403(92);

    $main = 'order_message_widget';
    $smarty->assign('amazon_orderid', $amazon_orderid);

    $location[] = array(func_get_langvar_by_name('lbl_order_processed'), '');
}

$intershipper_recalc = 'Y';

$allow_cod = @func_query_first_cell("SELECT COUNT(*) FROM $sql_tbl[payment_methods] WHERE active = 'Y' AND is_cod = 'Y'") > 0;
$smarty->assign('allow_cod', $allow_cod);

/**
 * Detect PayPal Pro status
 */

if (
    !empty($paypal_express_enabled)
    && func_get_paypal_express_active() == $paypal_express_enabled
) {
    if (func_is_valid_payment_method($paypal_express_enabled)) {
        $smarty->assign('paypal_express_active', $paypal_express_enabled);
        if (!empty($active_modules['Bill_Me_Later'])) {
            $smarty->assign('paypal_bml_id', func_cart_get_paypal_bml_id());
        }
    }

    func_paypal_express_enable_1step();
}

$smarty->assign('dhl_ext_country', $dhl_ext_country);

if (isset($dhl_ext_countries)) {

    $smarty->assign('dhl_ext_countries', $dhl_ext_countries);

}
?>
