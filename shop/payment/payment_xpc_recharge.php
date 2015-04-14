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
 * Administration page
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Modules
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v12 (xcart_4_7_0), 2015-02-17 23:56:28, payment_xpc_recharge.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */


require '../include/payment_method.php';

x_load(
    'cart',
    'order',
    'payment'
);

if (empty($active_modules['XPayments_Connector'])) {
    func_header_location('home.php');
    exit;
}
func_xpay_func_load();

$saved_cards = func_xpc_get_saved_cards();

if (empty($saved_cards[$saved_card_id])) {
    func_403(200);
}
$recharge_orderid = $saved_cards[$saved_card_id]['orderid'];

if (!empty($recharge_orderid) && !func_xpc_check_order_for_user($logged_userid, $recharge_orderid)) {
    $recharge_orderid = 0;
}

/**
 * Process order
 */
require_once $xcart_dir . '/include/payment_wait.php';

if (!empty($recharge_orderid)) {
    $recharge_payment_method = func_query_first_cell("SELECT payment_method FROM $sql_tbl[orders] WHERE orderid = '$recharge_orderid'");
} else {
    $recharge_payment_method = func_xpc_compose_payment_method_text($saved_cards[$saved_card_id]['paymentid']);
}

$orderids = func_place_order(
    $recharge_payment_method,
    'I',
    '',
    $Customer_Notes
);

if (
    empty($orderids)
    || in_array($orderids, XCPlaceOrderErrors::getAllCodes())
) {

    $top_message = array(
        'content' => func_get_langvar_by_name('txt_err_place_order_' . $orderids),
        'type'    => 'E',
    );

    func_header_location($xcart_catalogs['customer'] . "/cart.php?mode=checkout&paymentid=" . $paymentid);

}

list($xpc_status, $xpc_result) = func_xpc_process_recharge($saved_card_id, $cart['total_cost'], $orderids);

$bill_error = '';
if (
    !$xpc_status
    ||
    XPC_AUTH_ACTION != $xpc_result['status'] && XPC_CHARGED_ACTION != $xpc_result['status']
) {
    $bill_error = func_get_langvar_by_name('lbl_recharge_failed', array(), false, true);
}

if ($bill_error) {

    // Order declined. Redirect to the error page.
    func_header_location($xcart_catalogs['customer'] . '/error_message.php?error=error_ccprocessor_error&bill_message=' . urlencode($bill_error));

} else {

    // Order placed successfully. Cleanup cart and redirect to the invoice.
    $cart = '';
    $_orderids = func_get_urlencoded_orderids ($orderids);

    func_header_location($xcart_catalogs['customer'] . "/cart.php?mode=order_message&orderids=" . $_orderids);
}

?>
