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
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Customer interface
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    3968cba5ecdb78320d43cbe05a25fe35597bc800, v5 (xcart_4_7_0), 2015-02-17 13:29:01, amazon_checkout.php, aim
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

require __DIR__.'/auth.php';

if (empty($active_modules['Amazon_Payments_Advanced'])) {
    func_page_not_found();
}

x_load('cart', 'product', 'user', 'shipping', 'xml');

x_session_register('cart');

x_session_register('intershipper_rates');
x_session_register('intershipper_recalc');

define('ALL_CARRIERS', 1);

// can't checkout with empty cart
if (func_is_cart_empty($cart)) {
    func_header_location('cart.php');
}

$cart = func_cart_set_paymentid($cart, 0);

if ($REQUEST_METHOD == 'GET') {
    $intershipper_recalc = 'Y';
}

$products = func_products_in_cart($cart, @$userinfo['membershipid']);

$cart = func_array_merge(
    $cart,
    func_calculate(
        $cart,
        $products,
        0, // $logged_userid, // always anonymous
        $current_area,
        0
    )
);


if ($REQUEST_METHOD == 'POST') {

    if ($mode == 'check_address' && !empty($orefid)) {

        $addr_set = false;
        $res = func_amazon_pa_request('GetOrderReferenceDetails', array(
            'AmazonOrderReferenceId' => $orefid
        ));
        if ($res) {
            $res = func_array_path($res, 'GetOrderReferenceDetailsResponse/GetOrderReferenceDetailsResult/OrderReferenceDetails/Destination/PhysicalDestination/0/#');
            if ($res) {
                $uinfo = func_userinfo(0, $login_type, false, false, 'H');
                $tmp['zipcode'] = $res['PostalCode'][0]['#'];
                $tmp['country'] = $res['CountryCode'][0]['#'];
                $tmp['state'] = $res['StateOrRegion'][0]['#'];
                $tmp['city'] = $res['City'][0]['#'];
                foreach ($tmp as $k => $v) {
                    $uinfo['address']['B'][$k] = $v;
                    $uinfo['address']['S'][$k] = $v;
                }

                func_set_anonymous_userinfo($uinfo);
                $addr_set = true;
            }
        }

        if (!$addr_set) {
            echo 'error';
            func_amazon_pa_error("check address error: orefid=$orefid");
        } else {
            echo 'ok';
        }

    } elseif ($mode == 'change_shipping' && !empty($shippingid)) {

        $cart = func_cart_set_shippingid($cart, $shippingid);
        echo 'ok';
        
    } elseif ($action == 'place_order' && !empty($amazon_pa_orefid)) {

        if (func_is_cart_empty($cart)) {
            $top_message['type'] = 'E';
            $top_message['content'] = 'cart is empty';
            func_header_location('amazon_checkout.php?amz_pa_ref=' . $amazon_pa_orefid);
        }

        x_load('order');

        $customer_notes = $Customer_Notes;
        $extra = array();
        if ($config['Amazon_Payments_Advanced']['amazon_pa_mode'] == 'test') {
            $extra['in_testmode'] = true;
        }
        $extra['AmazonOrderReferenceId'] = $amazon_pa_orefid;

        $payment_method_text = func_get_langvar_by_name('module_name_Amazon_Payments_Advanced', null, false, true);

        // SetOrderReferenceDetails
        $res = func_amazon_pa_request('SetOrderReferenceDetails', array(
            'AmazonOrderReferenceId' => $amazon_pa_orefid,
            'OrderReferenceAttributes.OrderTotal.Amount' => $cart['total_cost'],
            'OrderReferenceAttributes.OrderTotal.CurrencyCode' => $config['Amazon_Payments_Advanced']['amazon_pa_currency'],
            'OrderReferenceAttributes.PlatformId' => AMAZON_PA_PLATFORM_ID,
            'OrderReferenceAttributes.SellerNote' => '',
        ));

        // ConfirmOrderReference - response is common requestid
        $res = func_amazon_pa_request('ConfirmOrderReference', array(
            'AmazonOrderReferenceId' => $amazon_pa_orefid,
        ));

        // get more order details using GetOrderReferenceDetails after confirmation
        $res = func_amazon_pa_request('GetOrderReferenceDetails', array(
            'AmazonOrderReferenceId' => $amazon_pa_orefid,
        ));
        if ($res) {
            $dest = func_array_path($res, 'GetOrderReferenceDetailsResponse/GetOrderReferenceDetailsResult/OrderReferenceDetails/Destination/PhysicalDestination/0/#');
            $buyer = func_array_path($res, 'GetOrderReferenceDetailsResponse/GetOrderReferenceDetailsResult/OrderReferenceDetails/Buyer/0/#');
            if ($dest) {
                //address
                $uinfo = func_userinfo(0, $login_type, false, false, 'H');
                $tmp['zipcode'] = $dest['PostalCode'][0]['#'];
                $tmp['country'] = $dest['CountryCode'][0]['#'];
                $tmp['state'] = $dest['StateOrRegion'][0]['#'];
                $tmp['city'] = $dest['City'][0]['#'];
                $tmp['phone'] = $dest['Phone'][0]['#'];
                $tmp['address'] = $dest['AddressLine1'][0]['#'];
                if (isset($dest['AddressLine2'])) {
                    $tmp['address_2'] = $dest['AddressLine2'][0]['#'];
                }
                list($tmp['firstname'], $tmp['lastname']) = explode(' ', $dest['Name'][0]['#'], 2);

                foreach ($tmp as $k => $v) {
                    $uinfo['address']['B'][$k] = $v;
                    $uinfo['address']['S'][$k] = $v;
                }

                // email, name
                if ($buyer) {
                    $uinfo['email'] = $buyer['Email'][0]['#'];
                    list($uinfo['firstname'], $uinfo['lastname']) = explode(' ', $buyer['Name'][0]['#'], 2);
                }

                func_set_anonymous_userinfo($uinfo);
            }
        }

        // confirmed, place not finished order
        $old_logged_userid = $logged_userid; // simulate anonymous checkout
        $logged_userid = 0;
        $orderids = func_place_order(
            $payment_method_text,
            'I',
            '',
            $customer_notes,
            $extra
        );
        $logged_userid = $old_logged_userid;

        if (is_null($orderids) || $orderids === false) {
            $top_message = array(
                'content'   => func_get_langvar_by_name("err_product_in_cart_expired_msg"),
                'type'      => 'E',
            );
            func_header_location($xcart_catalogs['customer'] . '/cart.php');
        }

        $_orderids = func_get_urlencoded_orderids($orderids);
        $order_status = 'F';
        $amz_authorized = false;
        $amz_authorization_id = '';
        $amz_captured = false;
        $amz_capture_id = '';
        $advinfo = array("AmazonOrderReferenceId: $amazon_pa_orefid");

        // Authorize
        $_tmp = array(
            'AmazonOrderReferenceId' => $amazon_pa_orefid,
            'AuthorizationAmount.Amount' => $cart['total_cost'],
            'AuthorizationAmount.CurrencyCode' => $config['Amazon_Payments_Advanced']['amazon_pa_currency'],
            'AuthorizationReferenceId' => 'auth_' . $_orderids,
            'SellerAuthorizationNote' => '',
        );
        if ($config['Amazon_Payments_Advanced']['amazon_pa_capture_mode'] == 'C') {
            // capture immediate
            $_tmp['CaptureNow'] = 'true';
        }
        if ($config['Amazon_Payments_Advanced']['amazon_pa_mode'] == 'test' && !empty($customer_notes)) {
            // simulate decline
            if ($customer_notes == 'decline') {
                $_tmp['SellerAuthorizationNote'] = urlencode('{"SandboxSimulation":{"State":"Declined","ReasonCode":"AmazonRejected"}}');
            }
        }
        if ($config['Amazon_Payments_Advanced']['amazon_pa_sync_mode'] == 'S') {
            // sync request (returns only "open" or "declined" status, no "pending")
            $_tmp['TransactionTimeout'] = '0';
        }
        $res = func_amazon_pa_request('Authorize', $_tmp);
        if ($res) {
            $_auth_details = func_array_path($res, 'AuthorizeResponse/AuthorizeResult/AuthorizationDetails/0/#');
            if ($_auth_details) {
                $amz_authorization_id = $_auth_details['AmazonAuthorizationId'][0]['#'];
                $_reply_status = $_auth_details['AuthorizationStatus'][0]['#']['State'][0]['#'];
                $advinfo[] = "AmazonAuthorizationId: $amz_authorization_id";
                $advinfo[] = "AuthorizationStatus: $_reply_status";
                func_amazon_pa_save_order_extra($orderids, 'amazon_pa_auth_id', $amz_authorization_id);
                func_amazon_pa_save_order_extra($orderids, 'amazon_pa_auth_status', $_reply_status);

                if ($_reply_status == 'Declined') {
                    $order_status = 'D';
                }

                if ($_reply_status == 'Pending') {
                    $order_status = 'Q'; // wait for IPN message
                }

                if ($_reply_status == 'Open') {
                    $amz_authorized = true;
                }

                if ($_reply_status == 'Closed') {
                    // capture now mode
                    if ($config['Amazon_Payments_Advanced']['amazon_pa_capture_mode'] == 'C') {
                        $amz_authorized = true;
                        $amz_captured = true;
                        $_capt_id = $_auth_details['IdList'][0]['#']['member'][0]['#'];
                        func_amazon_pa_save_order_extra($orderids, 'amazon_pa_capture_id', $_capt_id);
                    }
                }

            } else {
                // log error
                func_amazon_pa_error('Unexpected authorize reply: res=' . print_r($res, true));
            }
        }

        if ($amz_authorized) {
            if ($amz_captured) {
                // capture now mode, order is actually processed here
                $order_status = 'P';
            } else {
                // pre-auth
                $order_status = 'A';
            }
        }

        // change order status
        $override_completed_status = ($order_status != 'P');
        func_change_order_status($orderids, $order_status, join("\n", $advinfo), $override_completed_status);

        // show invoice or error message
        if ($order_status == 'F' || $order_status == 'D') {

            if (!empty($cart['applied_giftcerts'])) {
                $cart['applied_giftcerts_db_block_is_needed'] = true;
            }

            $bill_error = 'error_ccprocessor_error';
            $reason = "&bill_message=";
            if ($order_status == 'F') {
                // some error
                $reason .= urlencode(func_get_langvar_by_name('txt_payment_transaction_error', null, false, true));
            } elseif ($order_status == 'D') {
                // transaction declined
                $reason .= urlencode(func_get_langvar_by_name('txt_payment_transaction_is_failed', null, false, true));
            }
            func_header_location($xcart_catalogs['customer'] . "/error_message.php?" . "error=" . $bill_error . $reason);

        } else {

            if ($order_status == 'P' || $order_status == 'Q' || $order_status == 'A') {
                $cart = '';
            }

            func_header_location($xcart_catalogs['customer'] . "/cart.php?mode=order_message&orderids=$_orderids");
        }
    }

    exit();
}

include $xcart_dir . '/include/common.php';

$checkout_module = '';
$userinfo = func_userinfo(0, $login_type, false, false, 'H');
include $xcart_dir . '/include/cart_calculate_totals.php';

$smarty->assign('cart', $cart);
$smarty->assign('products', $products);

$smarty->assign('main', 'checkout');
$smarty->assign('checkout_module', 'Amazon_Payments_Advanced');
$smarty->assign('page_container_class', 'opc-container checkout-container');

func_display('customer/home.tpl',$smarty);
?>
