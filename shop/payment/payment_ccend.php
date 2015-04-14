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
 * Last phase of the payment action - process response
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Payment interface
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v57 (xcart_4_7_0), 2015-02-17 23:56:28, payment_ccend.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if (!defined('XCART_START')) { header("Location: ../"); die("Access denied"); }

x_load('crypt');

require $xcart_dir . '/payment/payment_ccmid.php';

if (!empty($is_iframe_canceled) && !empty($active_modules['One_Page_Checkout'])) {
    // Exit to minimize delays if order has been canceled from iframe
    exit;
}

x_session_register('cart');

if ($bill_error) {

    $request = $current_location
        . DIR_CUSTOMER
        . "/error_message.php?"
        . $sessurl
        . "error="
        . $bill_error
        . $reason;

} else {

    if (
        empty($cart['split_query'])
        && $bill_output['code'] != 5
    ) {

        $request = $xcart_catalogs['customer']
            . '/cart.php?'
            . $sessurl
            . 'mode=order_message&orderids='
            . $_orderids;

        $cart = '';

    } else {

        $top_message = array(
            'type'      => 'I',
            'content'   => func_get_langvar_by_name('txt_order_was_partially_paid'),
        );

        $request = $xcart_catalogs['customer']
            . '/cart.php?mode=checkout&paymentid=' . $paymentid;

    }

}

x_load('paypal');
func_paypal_clear_ec_token();

// Do not remove as some payment calls take a lot of time
x_session_save();

require $xcart_dir . '/payment/payment_ccredirect.php';

?>
