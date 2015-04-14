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
 * Amazon order-related operations
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Admin interface
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    3968cba5ecdb78320d43cbe05a25fe35597bc800, v6 (xcart_4_7_0), 2015-02-17 13:29:01, amazon_pa_order.php, aim
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

require __DIR__.'/auth.php';
require $xcart_dir . '/include/security.php';

if ($mode == 'amazon_pa_enable_module') {
    db_query("UPDATE $sql_tbl[modules] SET active = 'Y' WHERE module_name = 'Amazon_Payments_Advanced'");
    db_query("UPDATE $sql_tbl[modules] SET active = 'N' WHERE module_name = 'Amazon_Checkout'");
    func_remove_xcart_caches(true, func_get_cache_dirs());
    func_header_location("payment_methods.php");
}

if (empty($active_modules['Amazon_Payments_Advanced'])) {
    func_page_not_found();
}

x_load('mail', 'product', 'user', 'order', 'xml');

if ($REQUEST_METHOD == 'POST' && !empty($mode) && !empty($orderid)) {

    $order = func_order_data($orderid);
    if (empty($order)) {
        func_page_not_found();
    }

    $err_msg = '';
    $order_status = '';
    $advinfo = array();

    switch ($mode) {

        case 'capture':

            $amz_captured = false;

            $res = func_amazon_pa_request('Capture', array(
                'AmazonAuthorizationId' => $order['order']['extra']['amazon_pa_auth_id'],
                'CaptureAmount.Amount' => $order['order']['total'],
                'CaptureAmount.CurrencyCode' => $config['Amazon_Payments_Advanced']['amazon_pa_currency'],
                'CaptureReferenceId' => 'capture_' . $orderid,
                'SellerCaptureNote' => '',
            ));
            if ($res) {
                $_capt_details = func_array_path($res, 'CaptureResponse/CaptureResult/CaptureDetails/0/#');
                if ($_capt_details) {
                    $amz_capture_id = $_capt_details['AmazonCaptureId'][0]['#'];
                    $_reply_status = $_capt_details['CaptureStatus'][0]['#']['State'][0]['#'];
                    $amz_captured = ($_reply_status == 'Completed');
                    $captured_total = $_capt_details['CaptureAmount'][0]['#']['Amount'][0]['#'];

                    $advinfo[] = "AmazonCaptureId: $amz_capture_id";
                    $advinfo[] = "CaptureStatus: $_reply_status";
                    func_amazon_pa_save_order_extra($orderid, 'amazon_pa_capture_id', $amz_capture_id);
                    func_amazon_pa_save_order_extra($orderid, 'amazon_pa_capture_status', $_reply_status);

                    if ($_reply_status == 'Declined') {
                        $order_status = 'D';
                    }
                    $err_msg = "Status=$_reply_status";
                } else {
                    // log error
                    $err_msg = 'Unexpected Capture reply';
                    func_amazon_pa_error('Unexpected Capture reply: ' . func_xml_format($res, 2));
                }
            }

            if ($amz_captured) {
                // captured
                $order_status = 'P';
            }

            if (!empty($order_status)) {
                $override_completed_status = ($order_status != 'P');
                func_change_order_status($orderid, $order_status, join("\n", $advinfo), $override_completed_status);
            }

            if ($amz_captured) {
                $top_message['content'] = func_get_langvar_by_name('lbl_payment_capture_successfully_differ', array('captured_total' => $captured_total));
            } else {
                $top_message['type'] = 'E';
                $top_message['content'] = func_get_langvar_by_name('lbl_payment_capture_error', array('error_message' => $err_msg));
            }
            break;

        case 'void':
            $amz_voided = false;

            $res = func_amazon_pa_request('CloseAuthorization', array(
                'AmazonAuthorizationId' => $order['order']['extra']['amazon_pa_auth_id'],
                'ClosureReason' => '',
            ));

            if ($res) {
                $amz_voided = true;
            } else {
                $err_msg = 'Void error';
            }

            if ($amz_voided) {
                func_change_order_status($orderid, 'D'); // cancelled status?

                $top_message['content'] = func_get_langvar_by_name('lbl_payment_void_successfully');
            } else {
                $top_message['type'] = 'E';
                $top_message['content'] = func_get_langvar_by_name('lbl_payment_void_error', array('error_message' => $err_msg));
            }
            break;

        case 'refund':
            $amz_refunded = false;

            $res = func_amazon_pa_request('Refund', array(
                'AmazonCaptureId' => $order['order']['extra']['amazon_pa_capture_id'],
                'RefundAmount.Amount' => $order['order']['total'],
                'RefundAmount.CurrencyCode' => $config['Amazon_Payments_Advanced']['amazon_pa_currency'],
                'RefundReferenceId' => 'refund_' . $orderid,
                'SellerRefundNote' => '',
            ));
            if ($res) {
                $_ref_details = func_array_path($res, 'RefundResponse/RefundResult/RefundDetails/0/#');
                if ($_ref_details) {
                    $amz_ref_id = $_ref_details['AmazonRefundId'][0]['#'];
                    $_reply_status = $_ref_details['RefundStatus'][0]['#']['State'][0]['#'];
                    $amz_refunded = ($_reply_status == 'Completed');
                    $refunded_total = $_ref_details['RefundAmount'][0]['#']['Amount'][0]['#'];

                    $advinfo[] = "AmazonRefundId: $amz_ref_id";
                    $advinfo[] = "RefundStatus: $_reply_status";
                    func_amazon_pa_save_order_extra($orderid, 'amazon_pa_refund_id', $amz_ref_id);
                    func_amazon_pa_save_order_extra($orderid, 'amazon_pa_refund_status', $_reply_status);

                    $err_msg = "Status=$_reply_status";
                } else {
                    // log error
                    $err_msg = 'Unexpected Refund reply';
                    func_amazon_pa_error('Unexpected Refund reply: ' . func_xml_format($res, 2));
                }
            }

            if ($amz_refunded) {
                func_change_order_status($orderid, 'D', join("\n", $advinfo));

                $top_message['content'] = func_get_langvar_by_name('lbl_payment_refund_successfully');
            } else {
                $top_message['type'] = 'E';
                $top_message['content'] = func_get_langvar_by_name('lbl_payment_refund_error', array('error_message' => $err_msg), false, true);
            }

            break;

        case 'refresh':

            $res = func_amazon_pa_request('GetAuthorizationDetails', array(
                'AmazonAuthorizationId' => $order['order']['extra']['amazon_pa_auth_id'],
            ));
            $_auth_details = func_array_path($res, 'GetAuthorizationDetailsResponse/GetAuthorizationDetailsResult/AuthorizationDetails/0/#');
            if ($_auth_details) {
                $_reply_status = $_auth_details['AuthorizationStatus'][0]['#']['State'][0]['#'];
                $_reply_reason = $_auth_details['AuthorizationStatus'][0]['#']['ReasonCode'][0]['#'];
                $_oid = str_replace('auth_', '', $_auth_details['AuthorizationReferenceId'][0]['#']);

                func_amazon_pa_save_order_extra($_oid, 'amazon_pa_auth_status', $_reply_status);
                $advinfo[] = "AuthorizationStatus: $_reply_status";
                if (!empty($_reply_reason)) {
                    $advinfo[] = "AuthorizationReason: $_reply_reason";
                }

                if ($_reply_status == 'Open') {
                    if ($config['Amazon_Payments_Advanced']['amazon_pa_capture_mode'] == 'A') {
                        // pre-authorized
                        func_change_order_status($_oid, 'A', join("\n", $advinfo));
                    }
                }

                if ($_reply_status == 'Closed') {
                    $_a_amnt = $_auth_details['AuthorizationAmount'][0]['#']['Amount'][0]['#'];
                    $_c_amnt = $_auth_details['CapturedAmount'][0]['#']['Amount'][0]['#'];
                    if ($_c_amnt > 0 && $_c_amnt == $_a_amnt) {

                        // capture now mode, funds were captured successfully, save captureID
                        $_capt_id = $_auth_details['IdList'][0]['#']['member'][0]['#'];
                        func_amazon_pa_save_order_extra($_oid, 'amazon_pa_capture_id', $_capt_id);
                        $advinfo[] = "AmazonCaptureId: $_capt_id";

                        func_change_order_status($_oid, 'P', join("\n", $advinfo));
                    }
                }

                if ($_reply_status == 'Declined') {
                    // declined
                    func_change_order_status($_oid, 'D', join("\n", $advinfo));
                }
            }

            break;

        case 'refresh_refund_status':

            $res = func_amazon_pa_request('GetRefundDetails', array(
                'AmazonRefundId' => $order['order']['extra']['amazon_pa_refund_id'],
            ));
            $_ref_details = func_array_path($res, 'GetRefundDetailsResponse/GetRefundDetailsResult/RefundDetails/0/#');
            if ($_ref_details) {
                $amz_ref_id = $_ref_details['AmazonRefundId'][0]['#'];
                $_reply_status = $_ref_details['RefundStatus'][0]['#']['State'][0]['#'];
                $_reply_reason = $_ref_details['RefundStatus'][0]['#']['ReasonCode'][0]['#'];
                $_oid = str_replace('refund_', '', $_ref_details['RefundReferenceId'][0]['#']);

                $advinfo[] = "AmazonRefundId: $amz_ref_id";
                $advinfo[] = "RefundStatus: $_reply_status";
                if (!empty($_reply_reason)) {
                    $advinfo[] = "RefundReason: $_reply_reason";
                }
                func_amazon_pa_save_order_extra($_oid, 'amazon_pa_refund_status', $_reply_status);

                if ($_reply_status == 'Completed') {
                    // refunded
                    func_change_order_status($_oid, 'D', join("\n", $advinfo));
                }
            }

            break;

        case 'refresh_capture_status':
            // not used
            break;

    } // switch

    func_header_location("order.php?orderid=$orderid");
}

?>
