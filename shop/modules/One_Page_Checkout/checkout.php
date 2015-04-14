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
 * This script implements checkout facility for One Page Checkout module
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage One Page Checkout
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v43 (xcart_4_7_0), 2015-02-17 23:56:28, checkout.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if ( !defined('XCART_SESSION_START') ) { header('Location: ../../'); die('Access denied'); }

x_session_register('login_antibot_on');

$smarty->assign('allow_popup_login', empty($login_antibot_on));

$paymentid = func_cart_get_paymentid($cart, $checkout_module);
if (!func_is_valid_payment_method($paymentid)) {
    $cart = func_cart_set_paymentid($cart, 0);
    $top_message['content'] = func_get_langvar_by_name('err_payment_cc_not_available');
    $top_message['type']    = 'E';
    func_header_location('cart.php');
} else {
    $cart = func_cart_set_paymentid($cart, $paymentid);
}

if (
    !empty($shipping)
    && (
        !isset($cart['shippingid'])
        || empty($cart['shippingid'])
    )
) {
    $cart['shippingid'] = $shipping[0]['shippingid'];
}

if ($cart['total_cost'] == 0) {
    func_paypal_express_enable_1step();
}

$shippingid = (isset($shippingid) && !empty($shippingid))
    ? $shippingid
    : $cart['shippingid'];

/**
 * Prepare checkout details
 */

if ($checkout_step_modifier['payment_methods'] == 1) {
    $smarty->assign('ignore_payment_method_selection', 1);
}

if (!empty($payment_methods)) {
    x_load('paypal');

    foreach ($payment_methods as $k => $payment_data) {

        $payment_data['payment_script_url'] = $current_location . '/payment/' . $payment_data['payment_script'];

        if ($payment_data['paymentid'] == $paymentid) {
            $smarty->assign('payment_script_url', $payment_data['payment_script_url']);
            $smarty->assign('payment_method',     $payment_data['payment_method']);
        }

        if ($payment_data['processor_file'] == 'ps_paypal_pro.php') {
            // Adjust cc_data and payment template for paypal
            list($payment_data) = func_paypal_adjust_payment_data($payment_data, 'One_Page_Checkout');
        }

        $payment_methods[$k] = $payment_data;
    }
}

$smarty->assign('cart', $cart);

if (func_is_confirmed_paypal_express()) {
    $paypal_expressid = func_cart_get_paypal_express_id();

    $smarty->assign('paypal_express_selected', true);
    $smarty->assign('paypal_expressid', $paypal_expressid);
}

?>
