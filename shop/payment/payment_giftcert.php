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
 * Gift certificate processing payment module
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Payment interface
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v80 (xcart_4_7_0), 2015-02-17 23:56:28, payment_giftcert.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

require '../include/payment_method.php';

x_load(
    'cart',
    'order',
    'payment'
);

/**
 * Perform some checks of the applied Gift Certificate
 */
$err = false;
$gcid = trim($gcid);

$gc_error_code = func_giftcert_check($gcid);

if ($gc_error_code == 1) {

    // Empty Gift certificate code
    $err     = 'fields';
    $errdesc = 'err_filling_form';

} elseif ($gc_error_code == 2) {

    // Gift certificate has already been applied

    $err     = 'gc_used';
    $errdesc = 'err_gc_used';

}

if (!$err) {

    $gc = func_giftcert_data($gcid, true);

    if (false === $gc) {

        // Non-existing Gift certificate

        $err     = 'gc_notfound';
        $errdesc = 'err_gc_error';

    } elseif (false === func_giftcert_apply($gc)) {

        // Not enough money - continue checkout

        $err     = 'gc_not_enough_money';
        $errdesc = 'txt_gc_not_enough_money';

    }
}

// Re-calculate cart totals

if (!$err) {

    $cart['applied_giftcerts'][count($cart['applied_giftcerts']) - 1]['giftcert_cost'] = $cart['total_cost'];

    $cart['giftcert_discount'] += $cart['total_cost'];

    $cart['total_cost'] = 0;

    if ($cart['orders']) {

        foreach($cart['orders'] as $k => $v) {

            $cart['orders'][$k]['total_cost'] = 0;

        }

    }

    $products = func_products_in_cart($cart, (!empty($userinfo['membershipid']) ? $userinfo['membershipid'] : 0));

    $cart = func_array_merge($cart, func_calculate($cart, $products, $logged_userid, $login_type));

}

if (
    $checkout_module == 'One_Page_Checkout'
    && func_is_ajax_request()
) {

    // Output errors / apply GC

    $_gc_total = $cart['giftcert_discount'];
    if ($err == 'gc_not_enough_money') {
        $err = false;
        // $cart['giftcert_discount'] is not calculated for 'gc_not_enough_money' case
        $_gc_total += $gc['debit'];
    }

    func_register_ajax_message(
        'opcUpdateCall',
        array(
            'action'    => 'updateGC',
            'gc_total'  => $_gc_total,
            'covered'   => $cart['total_cost'] > 0 ? 0 : 1,
            'status'    => $err ? 0 : 1,
            'message'   => $errdesc
                ? func_get_langvar_by_name($errdesc, false, false, true)
                : null,
        )
    );

    func_define('XC_DISABLE_SESSION_SAVE', true);
    x_session_save();

    func_ajax_finalize();

    exit;

} elseif ($err && $cart['total_cost'] > 0) {

    $top_message = array(
        'content' => func_get_langvar_by_name($errdesc),
        'type'    => 'E'
    );

    // Return to payment methods list
    if ($err == 'gc_not_enough_money')
        $paymentid = 0;

    $redirect = $xcart_catalogs['customer']
        . '/cart.php?mode=checkout'
        . '&paymentid=' . $paymentid
        . '&err=' . $err;

    func_header_location($redirect);
}

/**
 * Process order
 */
require_once $xcart_dir . '/include/payment_wait.php';

$customer_notes = $Customer_Notes;

$orderids = func_place_order(
    stripslashes($payment_method),
    'I',
    '', 
    $customer_notes
);

func_split_checkout_check_decline_order($cart, $orderids);

if (
    empty($orderids)
    || in_array($orderids, XCPlaceOrderErrors::getAllCodes())
) {

    $top_message = array(
        'content'   => func_get_langvar_by_name('txt_err_place_order_' . $orderids),
        'type'    => 'E',
    );

    func_header_location($xcart_catalogs['customer'] . "/cart.php?mode=checkout&paymentid=" . $paymentid);

}

define('STATUS_CHANGE_REF', 9);

func_change_order_status($orderids, 'P');

$_orderids = func_get_urlencoded_orderids ($orderids);

/**
 * Remove all from cart
 */

$cart = '';

func_header_location($xcart_catalogs['customer'] . "/cart.php?mode=order_message&orderids=" . $_orderids);

?>
