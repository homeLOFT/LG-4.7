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
 * PayPoint Fast Track
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Payment interface
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    3968cba5ecdb78320d43cbe05a25fe35597bc800, v2 (xcart_4_7_0), 2015-02-17 13:29:01, amazon_pa_ipn_recv.php, aim
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */


require __DIR__.'/auth.php';

if (empty($active_modules['Amazon_Payments_Advanced'])) {
    exit();
}

$request_body = file_get_contents('php://input');
if (empty($request_body)) {
    // empty request
    exit();
}

$message = json_decode($request_body, true);
$json_error = json_last_error();
if ($json_error != 0) {
    func_amazon_pa_error("incorrect IPN call (can not parse json data (err=$json_error) request=" . $request_body);
    exit();
}

// verify signature
if (!func_amazon_pa_ipn_verify_singature($message)) {
    func_amazon_pa_error("ERROR: can't verify signature. IPN message=" . print_r($message, true));
    exit();
}

// handle message
func_amazon_pa_debug("IPN message received: $message[Message]");

x_load('xml', 'order');

$notification = json_decode($message['Message'], true);
$res = func_xml_parse($notification['NotificationData'], $parse_error);
$advinfo = array();
switch ($notification['NotificationType']) {

    case 'PaymentAuthorize':
        $_auth_details = func_array_path($res, 'AuthorizationNotification/AuthorizationDetails/0/#');
        if ($_auth_details) {
            $_reply_status = $_auth_details['AuthorizationStatus'][0]['#']['State'][0]['#'];
            $_reply_reason = $_auth_details['AuthorizationStatus'][0]['#']['ReasonCode'][0]['#'];
            $_authorization_id = $_auth_details['AmazonAuthorizationId'][0]['#'];
            $_oid = str_replace('auth_', '', $_auth_details['AuthorizationReferenceId'][0]['#']);

            $advinfo[] = "AmazonAuthorizationId: $_authorization_id";
            $advinfo[] = "AuthorizationStatus: $_reply_status";
            func_amazon_pa_save_order_extra($_oid, 'amazon_pa_auth_status', $_reply_status);
            if (!empty($_reply_reason)) {
                $advinfo[] = "AuthorizationReason: $_reply_reason";
            }

            if ($_reply_status == 'Open') {
                if ($config['Amazon_Payments_Advanced']['amazon_pa_capture_mode'] == 'A') {
                    // authorized
                    func_change_order_status($_oid, 'A', join("\n", $advinfo));
                }
            }
            if ($_reply_status == 'Declined') {
                // declined
                func_change_order_status($_oid, 'D', join("\n", $advinfo));
            }
        }
        break;

    case 'PaymentCapture':
        $_capt_details = func_array_path($res, 'CaptureNotification/CaptureDetails/0/#');
        if ($_capt_details) {
            $_reply_status = $_capt_details['CaptureStatus'][0]['#']['State'][0]['#'];
            $_reply_reason = $_capt_details['CaptureStatus'][0]['#']['ReasonCode'][0]['#'];
            $_capture_id = $_capt_details['AmazonCaptureId'][0]['#'];

            $_oid = str_replace('capture_', '', $_capt_details['CaptureReferenceId'][0]['#']);
            $_oid = str_replace('auth_', '', $_oid); // captureNow mode

            $advinfo[] = "AmazonCaptureId: $_capture_id";
            $advinfo[] = "CaptureStatus: $_reply_status";
            if (!empty($_reply_reason)) {
                $advinfo[] = "CaptureReason: $_reply_reason";
            }
            func_amazon_pa_save_order_extra($_oid, 'amazon_pa_capture_status', $_reply_status);
            func_amazon_pa_save_order_extra($_oid, 'amazon_pa_capture_id', $_capture_id); // captureNow mode

            if ($_reply_status == 'Completed') {
                // captured, order is processed
                func_change_order_status($_oid, 'P', join("\n", $advinfo));
            }
            if ($_reply_status == 'Declined') {
                // declined
                func_change_order_status($_oid, 'D', join("\n", $advinfo));
            }
        }
        break;

    case 'PaymentRefund':
        $_ref_details = func_array_path($res, 'RefundNotification/RefundDetails/0/#');
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
            func_amazon_pa_save_order_extra($orderid, 'amazon_pa_refund_status', $_reply_status);

            if ($_reply_status == 'Completed') {
                // refunded
                func_change_order_status($_oid, 'D', join("\n", $advinfo));
            }
        }
        break;
}

exit();

?>
