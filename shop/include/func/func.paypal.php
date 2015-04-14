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
 * PayPal functions
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Lib
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v131 (xcart_4_7_0), 2015-02-17 23:56:28, func.paypal.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if (!defined('XCART_START')) {
    header('Location: ../../');
    die('Access denied');
}

x_load('payment');

define('X_PAYPAL_EC_TOKEN_TTL', 10800);

// Lineitems are disabled for orders having more products than defined limit bt#133418
define('XC_PAYPAL_LINEITEMS_LIMIT', 20);

/**
 * Convert PayPal timestamp format to Unix timestamp
 */
function func_paypal_timestamp($ts)
{
    if (!preg_match("/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2})(?:\.\d{2})?Z$/", trim($ts), $match))
        return false;

    return gmmktime($match[4], $match[5], $match[6], $match[2], $match[3], $match[1]);
}

/**
 * Parse error for US version
 */
function func_paypal_prepare_errors($res)
{
    $errors = array();
    $i = 0;

    while(
        isset($res['l_errorcode' . $i])
        && isset($res['l_shortmessage' . $i])
        && isset($res['l_longmessage' . $i])
    ) {
        $errors[] = array(
            'code'     => $res['l_errorcode' . $i],
            'title' => $res['l_shortmessage' . $i],
            'desc'     => $res['l_longmessage' . $i]
        );

        $i++;
    }

    return $errors;
}

/**
 * Parse errors for UK version
 */
function func_paypal_prepare_errors_uk($res)
{
    if (empty($res)) {

        return array(
            'status'     => 'error',
            'error'     => 'Unknown error',
        );

    } elseif (!isset($res[2])) {

        return array(
            'status'     => 'error',
            'error'     => 'Unknown error: ' . $res[1],
        );

    } elseif (
        isset($res[2]['result'])
        && $res[2]['result'] > 0
    ) {

        return array(
            'status'         => 'error',
            'error'         => $res[2]['result'] . "// " . $res[2]['respmsg'],
            'error_code'     => $res[2]['result']
        );

    }

    return false;
}

/**
 * Common request to PayPal US version
 */
function func_paypal_request($request, $regexp=false)
{
    global $pp_url, $pp_cert_file, $pp_signature, $pp_use_cert;

    x_load('http');

    if (defined('PAYPAL_DEBUG'))
        $log_str = "*** Request:\n\n$request\n\n";

    $post = explode("\n",$request);

    if ($pp_use_cert)
        list($headers, $response) = func_https_request('POST', $pp_url, $post, '', '', 'text/xml', '', $pp_cert_file);
    else
        list($headers, $response) = func_https_request('POST', $pp_url, $post, '', '', 'text/xml', '');

    if (
        function_exists('utf8_decode')
        && preg_match("/encoding=\"UTF-8\"/i", $response)
    ) {
        $response = utf8_decode($response);
    }

    if (defined('PAYPAL_DEBUG')) {

        $log_str .= "*** Response:\n\n$headers\n\n$response\n\n";

        x_log_add('paypal', $log_str);

    }

    if ($headers == '0') {
        return array(
            'success'     => false,
            'error'     => array('ShortMessage' => $response),
        );
    }

    $result = array (
        'headers'     => $headers,
        'response'     => $response,
    );

    if (!empty($regexp)) {

        $matches = array();

        preg_match($regexp, $response, $matches);

        $result['matches'] = $matches;

    }

    // Parse and fill common fields

    $result['success'] = false;

    $ord_fields = array (
        'Ack',
        'TransactionID',
        'Token', // Note: expires after three hours (Express Checkout Integration Guide, p30)
        'AVSCode',
        'CVV2Code',
        'PayerID',
        'PayerStatus',
        'FirstName',
        'LastName',
        'ContactPhone',
        'TransactionType', // e.g. express-checokut
        'PaymentStatus', // e.g. Pending
        'PendingReason', // e.g. authorization
        'ReasonCode',
        'GrossAmount',
        'FeeAmount',
        'SettleAmount',
        'TaxAmount',
        'ExchangeRate',
        'FMFDetails',
    );

    foreach ($ord_fields as $field) {
        if (preg_match('/<' . $field . '(?: [^>]*)?>([^>]+)<\/' . $field . '>/', $response, $out)) {
            $result[$field] = $out[1];
        }
    }

    if (
        !strcasecmp($result['Ack'], 'Success')
        || !strcasecmp($result['Ack'], 'SuccessWithWarning')
    ) {
        $result['success'] = true;
    }    

    if (preg_match('!<Payer(?:\s[^>]*)?>([^>]+)</Payer>!', $response, $out)) {
        $result['Payer'] = $out[1]; // e-mail address
    }

    if (preg_match('!<Errors[^>]*>(.+)</Errors>!', $response, $out_err)) {
        $error = array();

        if (preg_match('!<SeverityCode[^>]*>([^>]+)</SeverityCode>!', $out_err[1], $out))
            $error['SeverityCode'] = $out[1];

        if (preg_match('!<ErrorCode[^>]*>([^>]+)</ErrorCode>!', $out_err[1], $out)) {
            
            $error['ErrorCode'] = $out[1];

            if (!empty($result['success'])) {
                // code 11610 indicates that one or more filters caused the transaction to be pended, awaiting your review
                if ($error['ErrorCode'] == '11610')
                    $result['fmf'] = true;
            } else {
                // Code 11611 indicates that one or more filters caused the transaction to be denied
                if ($error['ErrorCode'] == '11611')
                    $result['fmf'] = true;
            }      

            if (!empty($result['fmf']))
                $result['filters'] = serialize(func_ps_paypal_pro_parse_filters($result['FMFDetails']));
        }    

        if (preg_match('!<ShortMessage[^>]*>([^>]+)</ShortMessage>!', $out_err[1], $out))
            $error['ShortMessage'] = $out[1];

        if (preg_match('!<LongMessage[^>]*>([^>]+)</LongMessage>!', $out_err[1], $out))
            $error['LongMessage'] = $out[1];

        $result['error'] = $error;
    }

    if (preg_match('!<Address[^>]*>(.+)</Address>!', $response, $out)) {

        $out_addr = $out[1];

        $address = array();

        if (preg_match('!<Name[^>]*>([^>]+)</Name>!', $out_addr, $out)) {
            $__name = explode(' ',$out[1], 2);
            $address['FirstName'] = $__name[0];
            $address['LastName'] = $__name[1];
            unset($__name);
        }

        if (preg_match('!<Street1[^>]*>([^>]+)</Street1>!', $out_addr, $out))
            $address['Street1'] = $out[1];
        if (preg_match('!<Street2[^>]*>([^>]+)</Street2>!', $out_addr, $out))
            $address['Street2'] = $out[1];

        if (preg_match('!<CityName[^>]*>([^>]+)</CityName>!', $out_addr, $out))
            $address['CityName'] = $out[1];

        if (preg_match('!<StateOrProvince[^>]*>([^>]+)</StateOrProvince>!', $out_addr, $out))
            $address['StateOrProvince'] = $out[1];

        if (preg_match('!<Country[^>]*>([^>]+)</Country>!', $out_addr, $out))
            $address['Country'] = $out[1];

        if (preg_match('!<PostalCode[^>]*>([^>]+)</PostalCode>!', $out_addr, $out))
            $address['PostalCode'] = $out[1];

        if (preg_match('!<AddressOwner[^>]*>([^>]+)</AddressOwner>!', $out_addr, $out))
            $address['AddressOwner'] = $out[1];

        if (preg_match('!<AddressStatus[^>]*>([^>]+)</AddressStatus>!', $out_addr, $out))
            $address['AddressStatus'] = $out[1];

        $result['address'] = $address;
    }

    if (defined('PAYPAL_DEBUG')) {
        $log_str = "*** Parsed result:\n\n" . print_r($result, true) . "\n\n";
        x_log_add('paypal', $log_str);
    }

    return $result;
}

/**
 * Create refund
 */
function func_paypal_create_refund($orderid, $amount, $note)
{
    global $sql_tbl;

    $data = func_query_first("SELECT $sql_tbl[orders].paymentid, $sql_tbl[order_extras].value FROM $sql_tbl[orders], $sql_tbl[order_extras] WHERE $sql_tbl[orders].orderid = $sql_tbl[order_extras].orderid AND $sql_tbl[order_extras].khash IN ('paypal_txnid', 'pnref') AND $sql_tbl[orders].orderid = '$orderid'");

    if (empty($data) || empty($data['value']))
        return false;

    $paypal = func_paypal_get_order_data($orderid);

    if (empty($paypal) || $paypal['no_refund_total'] <= 0)
        return false;

    $note = stripslashes($note);

    if ($paypal['method'] == 'UK') {

        $res = func_paypal_refund_uk($data['paymentid'], $data['value'], $note);

        if ($res['status'] == 'error')
            return array(
                array(
                    'desc' => $res['error']
                )
            );

        $res['refundtransactionid'] = $res['pnref'];
        $res['amount'] = $paypal['no_refund_total'];

    } elseif ($paypal['method'] == 'AD') {

        $amount = price_format($amount);

        if ($amount <= 0)
            return false;

        if ($amount > $paypal['no_refund_total'])
            $amount = $paypal['no_refund_total'];

        if ($amount == $paypal['no_refund_total'] && $amount == $paypal['order_total'])
            $amount = false;

        $res = func_paypal_refund_ad($data['paymentid'], $data['value'], $amount);

        if ($res['status'] == 'error')
            return array(
                array(
                    'desc' => $res['error']
                )
            );

        $res['refundtransactionid'] = $res['pnref'];
        $res['amount'] = $amount;

    } else {

        if ($amount <= 0)
            return false;

        if ($amount > $paypal['no_refund_total'])
            $amount = $paypal['no_refund_total'];

        if ($amount == $paypal['no_refund_total'] && $amount == $paypal['order_total'])
            $amount = false;

        $res = func_paypal_refund($data['paymentid'], $data['value'], $amount, $note);

        $res['amount'] = $res['grossrefundamt'];

        if (empty($res))
            return false;

        if (strtolower($res['ack']) != 'success')
            return func_paypal_prepare_errors($res);

            func_paypal_update_order($orderid);

    }
    $res['note'] = $note;

    return func_paypal_reg_refund(
        $data['value'],
        $res['refundtransactionid'],
        $res
    );
}

/**
 * Registration refund record for PayPal transaction
 */
function func_paypal_reg_refund($txid, $refundid, $uk_refund_data = false)
{
    global $sql_tbl;

    $oids = func_query("SELECT $sql_tbl[orders].orderid, $sql_tbl[orders].paymentid, $sql_tbl[orders].extra FROM $sql_tbl[orders], $sql_tbl[order_extras] WHERE $sql_tbl[orders].orderid = $sql_tbl[order_extras].orderid AND $sql_tbl[order_extras].khash IN ('paypal_txnid', 'pnref') AND $sql_tbl[order_extras].value = '$txid'");

    if (empty($oids))
        return false;

    $refund = array();

    foreach($oids as $v) {

        $extra = unserialize($v['extra']);

        if (!isset($extra['paypal']))
            $extra['paypal'] = array();

        if (!isset($extra['paypal']['subtrans']))
            $extra['paypal']['subtrans'] = array();

        if ($uk_refund_data) {

            $extra['paypal']['subtrans'][$refundid] = array(
                'type'             => 'Refunded',
                'date'             => XC_TIME,
                'amount'         => $uk_refund_data['amount'],
                'note'             => $uk_refund_data['note'],
                'currencycode'     => $uk_refund_data['currencycode'],
            );

        } else {

            if (empty($refund)) {

                $refund = func_paypal_get_status($v['paymentid'], $refundid);

                if (empty($refund))
                    return false;

            }

            $extra['paypal']['subtrans'][$refundid] = array(
                'type'                 => 'Refunded',
                'pendingreason'     => $refund['pendingreason'],
                'reason'             => $refund['reasoncode'],
                'amount'             => abs($refund['amt']),
                'currencycode'         => $refund['currencycode'],
                'note'                 => $refund['note'],
                'date'                 => func_paypal_timestamp($refund['ordertime']),
            );
        }

        func_array2update(
            'orders',
            array(
                'extra' => addslashes(serialize($extra)),
            ),
            "orderid = '$v[orderid]'"
        );
    }

    return true;
}

/**
 * Update order's PayPal transaction data (only US version)
 */
function func_paypal_update_order($orderid)
{
    global $sql_tbl;

    $data = func_query_first("SELECT $sql_tbl[orders].paymentid, $sql_tbl[orders].extra, $sql_tbl[order_extras].value FROM $sql_tbl[orders], $sql_tbl[order_extras] WHERE $sql_tbl[orders].orderid = $sql_tbl[order_extras].orderid AND $sql_tbl[order_extras].khash = 'paypal_txnid' AND $sql_tbl[orders].orderid = '$orderid'");

    if (empty($data) || empty($data['value']))
        return false;

    $res = func_paypal_get_status($data['paymentid'], $data['value']);

    if (empty($res) || strtolower($res['ack']) != 'success')
        return false;

    $extra = unserialize($data['extra']);

    $extra['paypal']['main'] = array(
        'txnid'             => $data['value'],
        'txn_type'             => $res['transactiontype'],
        'address_status'     => $res['addressstatus'],
        'payer_id'             => $res['payerid'],
        'payment_status'     => $res['paymentstatus'],
        'payer_status'         => $res['payerstatus'],
        'payer_email'         => $res['email'],
        'receiver_id'         => $res['receiverid'],
        'amount'             => $res['amt'],
        'currencycode'         => $res['currencycode'],
        'pendingreason'     => $res['pendingreason'],
        'reasoncode'         => $res['reasoncode'],
        'update_date'         => XC_TIME,
    );

    func_array2update(
        'orders',
        array(
            'extra' => addslashes(serialize($extra)),
        ),
        "orderid = '$orderid'"
    );

    return true;
}

/**
 * Do RefundTransaction request (US version)
 */
function func_paypal_refund($paymentid, $txnid, $refund_type = true, $note = '')
{
    $query = array(
        'method'         => 'RefundTransaction',
        'TransactionID' => $txnid,
        'RefundType'     => ((is_numeric($refund_type) && !empty($refund_type)) ? 'Partial' : 'Full')
    );

    if ($query['RefundType'] == 'Partial')
        $query['Amt'] = $refund_type;

    if (!empty($note))
        $query['Note'] = $note;

    $res = func_paypal_do_nvp($paymentid, $query);

    if (empty($res))
        return false;

    return $res[2];
}

/**
 * Do GetTransactionDetails request (US version)
 */
function func_paypal_get_status($paymentid, $txnid)
{
    $res = func_paypal_do_nvp(
        $paymentid,
        array(
            'method'         => 'GetTransactionDetails',
            'TransactionID' => $txnid,
        )
    );

    if (empty($res))
        return false;

    return $res[2];
}

/**
 * Do Set ExpressCheckout request (UK version)
 */
function func_paypal_sec_uk($paymentid, $cart, $userinfo, $is_mark_mode = FALSE, $is_bml = FALSE) { //{{{

    global $xcart_catalogs, $current_location, $shop_language, $pp_final_action;

    static $paypal_languages = array(
        'AU' => 'AU',
        'DE' => 'DE',
        'FR' => 'FR',
        'UK' => 'GB',
        'IT' => 'IT',
        'JP' => 'JP',
        'US' => 'US',
    );

    if (!$is_bml) {

        $returnurl = $current_location . '/payment/ps_paypal_pro.php?mode=express_return';

        if ($is_mark_mode) {
            $returnurl .= '&useraction=commit';
            $cancelurl = $xcart_catalogs['customer'] . '/cart.php?mode=checkout&express_cancel';
        } else {
            $cancelurl = $xcart_catalogs['customer'] . '/cart.php?express_cancel';
        }

    } else {

        $returnurl = $current_location . '/payment/ps_paypal_bml.php?mode=express_return';

        if ($is_mark_mode) {
            $returnurl .= '&useraction=commit';
            $cancelurl = $xcart_catalogs['customer'] . '/cart.php?mode=checkout&bml_cancel';
        } else {
            $cancelurl = $xcart_catalogs['customer'] . '/cart.php?bml_cancel';
        }

    }

    if (is_array($cart)) {
        $amount = func_paypal_convert_to_BasicAmountType($cart['total_cost']);
    } else {
        // Assume it is a total cost
        $amount = func_paypal_convert_to_BasicAmountType($cart);
        $cart = array();
    }

    $post = array(
        'action'                => 'S',
        'trxtype'               => $pp_final_action == 'Sale' ? 'S' : 'A',
        'tender'                => 'P',
        'AMT'                   => $amount,
        'currency'              => true,
        'returnurl'             => $returnurl,
        'cancelurl'             => $cancelurl,
        'pagestyle'             => true,
        'payflowcolor'          => true,
        'hdrimg'                => true,
     );

    if ($is_bml) {
        $post['PAYMENTREQUEST_0_OFFERCODE'] = 'BML-PPA';
        $post['USERSELECTEDFUNDINGSOURCE'] = 'BML';
        $post['SOLUTIONTYPE'] = 'SOLE';
        $post['_TRAILER_PASSTHROUGH__'] = 'Y';
    }

    $line_items = func_paypal_get_line_items_payflow($cart, $amount);

    if (!empty($line_items)) {
        $post = array_merge($post, $line_items);
    }

    if (!$is_mark_mode) {
        // Express Checkout Shortcut mode
        $post['AMT'] = $post['ITEMAMT'];
        $post['FREIGHTAMT'] = 0;
        $post['HANDLINGAMT'] = 0;
        $post['TAXAMT'] = 0;

    } else {
        // Express Checkout Mark mode
        $post['ADDROVERRIDE'] = '1';

    }

    if (isset($paypal_languages[$shop_language]))
        $post['localecode'] = $paypal_languages[$shop_language];

    $profile = func_paypal_get_userinfo_payflow($userinfo);

    if (!empty($profile)) {
        $post = array_merge($post, $profile);
    }

    $res = func_paypal_do_uk($paymentid, $post);

    $err = func_paypal_prepare_errors_uk($res);

    if ($err)
        return $err;

    $res = $res[2];
    $res['status'] = 'success';

    $access = func_paypal_define_access($paymentid);

    $res['redirect_url'] = (
        $access['test_mode']
            ? "https://www.sandbox.paypal.com"
            : "https://www.paypal.com/"
    ) . '/cgi-bin/webscr?cmd=_express-checkout&token=' . $res['token'];

    return $res;
} //}}}

/**
 * Do GetExpressCheckout request (UK version)
 */
function func_paypal_gec_uk($paymentid, $token)
{
    global $pp_final_action;

    $res = func_paypal_do_uk(
        $paymentid,
        array(
            'action'     => 'G',
            'trxtype'     => $pp_final_action == 'Sale' ? 'S' : 'A',
            'tender'     => 'P',
            'token'     => $token,
        )
    );

    $err = func_paypal_prepare_errors_uk($res);

    if ($err)
        return $err;

    $res = $res[2];
    $res['status'] = 'success';

    return $res;
}

/**
 * Do DoExpressCheckout request (UK version)
 */
function func_paypal_dec_uk($paymentid, $token, $payerid, $cart, $userinfo, $orderid, $secureid)
{
    global $pp_final_action;

    if (is_array($cart)) {
        $amt = func_paypal_convert_to_BasicAmountType($cart['total_cost']);
    } else {
        // Assume it is a total cost
        $amt = func_paypal_convert_to_BasicAmountType($cart);
        $cart = array();
    }

    $post =
        array(
            'action'        => 'D',
            'trxtype'       => $pp_final_action == 'Sale' ? 'S' : 'A',
            'tender'        => 'P',
            'token'         => $token,
            'payerid'       => $payerid,
            'amt'           => $amt,
            'invnum'        => $orderid,
            'custom'        => $secureid,
            'currency'      => true,
            'buttonsource'  => 'X-Cart_Cart_PRO2EC',
        );

    $line_items = func_paypal_get_line_items_payflow($cart, $amt);

    if (!empty($line_items)) {
        $post = array_merge($post, $line_items);
    }

    $profile = func_paypal_get_userinfo_payflow($userinfo);

    if (!empty($profile)) {
        $post = array_merge($post, $profile);
    }

    $res = func_paypal_do_uk($paymentid, $post);

    $err = func_paypal_prepare_errors_uk($res);

    if ($err)
        return $err;

    $res = $res[2];
    $res['status'] = 'success';

    return $res;
}

/**
 * Refund routine workaround for PayPal Express Checkout Payflow (only)
 */

function func_paypal_express_payflow_refund_mode($paymentid, $orderid) { //{{{

    return 'P';

} //}}} 

function func_ps_paypal_pro_do_refund($order, $amount = 0) { //{{{

    global $sql_tbl;

    $orderid = $order['order']['orderid'];

    $data = func_query_first("SELECT $sql_tbl[orders].paymentid, $sql_tbl[order_extras].value FROM $sql_tbl[orders], $sql_tbl[order_extras] WHERE $sql_tbl[orders].orderid = $sql_tbl[order_extras].orderid AND $sql_tbl[order_extras].khash IN ('paypal_txnid', 'pnref') AND $sql_tbl[orders].orderid = '$orderid'");

    if (empty($data) || empty($data['value'])) {
        return array(false, 'Inner error', false);
    }

    $paypal = func_paypal_get_order_data($orderid);

    if (empty($paypal) || $paypal['method'] != 'UK') {
        return array(false, 'Inner error', false);
    }

    $res = func_paypal_refund_uk($data['paymentid'], $data['value'], '', $amount);

    if ($res['status'] == 'error') {
        return array(false, $res['error'], false);
    }

    $res['refundtransactionid'] = $res['pnref'];
    $res['amount'] = $amount;

    if (empty($order['order']['extra']['paypal'])) {
        $order['order']['extra']['paypal'] = array();
        $order['order']['extra']['paypal']['subtrans'] = array();
    }
    $order['order']['extra']['paypal']['subtrans'][$res['ppref']] = array(
        'type' => 'Refunded',
        'amount' => $amount,
        'date' => XC_TIME,
    );

    func_array2update(
        'orders',
        array(
            'extra' => addslashes(serialize($order['order']['extra'])),
        ),
        "orderid = '" . $order['order']['orderid'] . "'"
    );

    return array(true, '', false);

//    return func_paypal_create_refund($order['order']['orderid'], $amount, '');

} //}}}


/**
 * Do RefundTransaction request (UK version)
 */
function func_paypal_refund_uk($paymentid, $pnref, $note, $amount = 0)
{

    $post = 
        array(
            'trxtype'     => 'C',
            'tender'     => 'P',
            'origid'     => $pnref,
            'memo'         => $note,
        );

    if (!empty($amount)) {
        $post['AMT'] = func_paypal_convert_to_BasicAmountType($amount);
    }

    $res = func_paypal_do_uk(
        $paymentid,
        $post
    );
    $err = func_paypal_prepare_errors_uk($res);

    if ($err)
        return $err;

    $res = $res[2];
    $res['status'] = 'success';

    return $res;
}

/**
 * Do RefundTransaction request (Advanced version)
 */
function func_paypal_refund_ad($paymentid, $pnref, $amount)
{
    global $module_params;

    func_pm_load('ps_paypal_advanced');

    $module_params = func_get_pm_params('ps_paypal_advanced.php');

    $result = func_payflow_call(
        array(
            'TRXTYPE' => 'C',
            'ORIGID' => $pnref,
            'AMT' => $amount,
        ),
        $module_params
    );

    if ($result['RESULT'] == '0') {
        $res = array(
            'status' => 'success',
            'pnref' => $result['PNREF'],
        );
    } else {
        $res = array(
            'status' => 'error',
            'error' => 'Result: ' . $result['RESULT'] . '; RESPMSG: ' . $result['RESPMSG'] . ';',
            'error_code' => $result['RESULT'],
        );
    }

    return $res;
}

/**
 * Do Capture (US & UK version)
 */
function func_paypal_capture($orderid)
{
    global $sql_tbl, $shop_language, $http_location, $https_location, $xcart_dir;

    $paypal = func_paypal_get_order_data($orderid);

    if (empty($paypal) || $paypal['capture_status'] == '')
        return false;

    if ($paypal['capture_status'] == 'A')
        return 'captured';

    if ($paypal['method'] == 'UK') {
        $res = func_paypal_do_uk(
            $paypal['paymentid'],
            array(
                'trxtype'     => 'D',
                'tender'     => 'P',
                'origid'     => $paypal['main']['txnid'],
            )
        );

        $err = func_paypal_prepare_errors_uk($res);

        if ($err)
            return $err;

        $res = $res[2];
        $res['status'] = 'success';

        return $res;

    } else {

        $query = array(
            'method'             => 'DoCapture',
            'AuthorizationID'     => $paypal['main']['txnid'],
            'AMT'                 => $paypal['order_total'],
            'completetype'         => 'COMPLETE',
        );

        $res = func_paypal_do_nvp(
            $paypal['paymentid'],
            array(
                'method'             => 'DoCapture',
                'AuthorizationID'     => $paypal['main']['txnid'],
                'AMT'                 => $paypal['order_total'],
                'completetype'         => 'COMPLETE',
            )
        );

        if (empty($res))
            return false;

        return $res[2];
    }
}

/**
 * Define access parameters for payment module
 */
function func_paypal_define_access($access)
{
    global $sql_tbl, $xcart_dir, $config;

    if (empty($access))
        return false;

    if (is_array($access) && $config['paypal_solution'] != 'advanced' && $config['paypal_solution'] != 'payflowlink') {

        if (!$access['vendor'] && !$access['partner']) {

            if (empty($access['user']) || empty($access['pwd']) || empty($access['access_value']))
                return false;

            if (!isset($access['test_mode']))
                $access['test_mode'] = false;

            if (!in_array($access['access_type'], array('C', 'S'))) {

                $access['access_type'] = file_exists($access['access_value']) ? 'C' : 'S';

                if ($access['access_type'] == 'S' && file_exists($xcart_dir . '/payment/certs/' . $access['access_value'])) {

                    $access['access_type']     = 'C';
                    $access['access_value'] = $xcart_dir . '/payment/certs/' . $access['access_value'];

                }

            }

        }

    } else {

        if ($config['paypal_solution'] == 'advanced' || $config['paypal_solution'] == 'payflowlink') {
            $payment_method = func_get_pm_params(($config['paypal_solution'] == 'advanced') ? 'ps_paypal_advanced.php' : 'ps_paypal_payflowlink.php');

            if (empty($payment_method)) {
                return false;
            }

            $access = array(
                'vendor'              => $payment_method['param01'],
                'partner'             => $payment_method['param02'],
                'user'                => $payment_method['param04'],
                'pwd'                 => $payment_method['param05']
            );

            foreach (array('pagestyle', 'hdrimg', 'payflowcolor') as $_param) {
                if (!empty($payment_method['params'][$_param])) {
                    $access[$_param] = $payment_method['params'][$_param];
                }
            }

            $access['prefix'] = $payment_method['param06'];

        } else if ($config['paypal_solution'] == 'pro_hosted') {

            $payment_method = func_get_pm_params('ps_paypal_pro_hosted.php');

            if (empty($payment_method)) {
                return false;
            }

            $access = array(
                'user'            => $payment_method['params']['api_username'],
                'pwd'             => $payment_method['params']['api_password'],
                'access_type'     => $payment_method['params']['api_method'],
                'access_value'    => $payment_method['params']['api_method'] == 'S'
                    ? $payment_method['params']['api_signature']
                    : $xcart_dir . '/payment/certs/' . $payment_method['params']['api_certificate'],
            );

        } else {

            $payment_method = func_query_first("SELECT $sql_tbl[ccprocessors].* FROM $sql_tbl[ccprocessors], $sql_tbl[payment_methods] WHERE $sql_tbl[ccprocessors].paymentid = '$access' AND $sql_tbl[ccprocessors].paymentid = $sql_tbl[payment_methods].paymentid");

            if (empty($payment_method) || $payment_method['processor'] == 'ps_paypal_bml.php') {
                $payment_method = func_query_first("SELECT $sql_tbl[ccprocessors].* FROM $sql_tbl[ccprocessors], $sql_tbl[payment_methods] WHERE $sql_tbl[ccprocessors].processor = 'ps_paypal_pro.php' AND $sql_tbl[ccprocessors].paymentid = $sql_tbl[payment_methods].paymentid");
            }

            if (empty($payment_method) || ($payment_method['processor'] != 'ps_paypal_pro.php' && $payment_method['processor'] != 'ps_paypal.php')) {
                return false;
            }

            if ($config['paypal_solution'] == 'uk') {

                $access = array(
                    'vendor'          => $payment_method['param01'],
                    'partner'         => $payment_method['param02'],
                    'user'            => $payment_method['param04'],
                    'pwd'             => $payment_method['param05'],
                    'payflowcolor'    => $payment_method['param07'],
                    'pagestyle'       => $payment_method['param08'],
                    'hdrimg'          => $payment_method['param09'],
                );

            } else {

                $access = array(
                    'user'            => $payment_method['param01'],
                    'pwd'             => $payment_method['param02'],
                    'access_type'     => $payment_method['param07'],
                    'access_value'    => $payment_method['param07'] == 'S'
                        ? $payment_method['param05']
                        : $xcart_dir . '/payment/certs/' . $payment_method['param04'],
                );

            }

            $access['prefix'] = $payment_method['param06'];

        }

        $access['test_mode']    = $payment_method['testmode'] == 'Y';
        $access['currency']     = func_paypal_get_currency($payment_method);
    }

    return $access;
}

/**
 * Do NVP API request (US version)
 */
function func_paypal_do_nvp($access, $post)
{

    if (empty($post['method']) && empty($post['METHOD']))
        return false;

    $access = func_paypal_define_access($access);

    if (!$access)
        return false;

    $post['version']     = '65.2';
    $post['user']         = $access['user'];
    $post['pwd']         = $access['pwd'];

    if (!empty($access['access_value']) && $access['access_type'] == 'S')
        $post['signature'] = $access['access_value'];

    if (!empty($access['currency'])) {
        $post['currencycode'] = $access['currency'];
    }

    $str = array();

    foreach($post as $k => $v) {
        $str[] = strtoupper($k) . "=" . $v;
    }

    if (defined('PAYPAL_DEBUG')) {

        $log_str = "*** Request:\n\n" . implode("\n", $str);

        x_log_add('paypal', $log_str);

    }

    x_load('http');

    if ($access['access_type'] == 'C') {

        $url = $access['test_mode']
            ? 'https://api.sandbox.paypal.com:443/nvp'
            : 'https://api.paypal.com:443/nvp';

        list($headers, $response) = func_https_request('POST', $url, $str, '&', '', 'text/html', '', $access['access_value']);

     } else {

        $url = $access['test_mode']
            ? "https://api-3t.sandbox.paypal.com:443/nvp"
            : "https://api-3t.paypal.com:443/nvp";

        list($headers, $response) = func_https_request('POST', $url, $str, '&', '', 'text/html', '');
    }

    if (defined('PAYPAL_DEBUG')) {

        $log_str = "*** Response:\n\n$headers\n\n$response\n\n";

        x_log_add('paypal', $log_str);

    }

    if (!preg_match("/\s*HTTP\/\d\.\d\s+200\s+/", $headers)) {
        return array(
            $headers,
            $response,
            'Server error: ' . $response,
        );
    }

    $res = func_parse_str($response, '&', '=', 'urldecode');

    $result = array();

    foreach($res as $k => $v) {
        $result[strtolower($k)] = $v;
    }

    return array(
        $headers,
        $response,
        $result,
    );
}

/**
 * Get PayPal-specific order data
 */
function func_paypal_get_order_data($orderid)
{
    global $sql_tbl, $config;

    $orderid = intval($orderid);

    if ($orderid < 1)
        return false;

    $data = func_query_first_cell("SELECT extra FROM $sql_tbl[orders] WHERE orderid = '$orderid'");

    if (empty($data))
        return false;

    $type = func_query_first_cell("SELECT value FROM $sql_tbl[order_extras] WHERE orderid = '$orderid' AND khash = 'paypal_type'");
    $txnid = func_query_first_cell("SELECT value FROM $sql_tbl[order_extras] WHERE orderid = '$orderid' AND khash IN ('paypal_txnid', 'pnref')");
    $ppref = func_query_first_cell("SELECT value FROM $sql_tbl[order_extras] WHERE orderid = '$orderid' AND khash = 'ppref'");

    $capture_status = func_query_first_cell("SELECT value FROM $sql_tbl[order_extras] WHERE orderid = '$orderid' AND khash = 'paypal_capture_status'");

    if (empty($txnid) || empty($type))
        return false;

    if (empty($ppref)) {
        $ppref = $txnid;
    }

    $_orders_result =  func_query_first("SELECT total, paymentid FROM " . $sql_tbl['orders'] . " WHERE orderid = '" . $orderid . "'");
    $data = unserialize($data);

    $result = array(
        'main'                 => array(
            'txnid' => $txnid,
            'ppref' => $ppref,
        ),
        'method'             => substr($type, 0, 2),
        'api'                 => substr($type, 2),
        'capture_status'     => $capture_status,
        'no_refund_total'     => isset($data['paypal']['main']['amount']) ? $data['paypal']['main']['amount'] : 0,
        'paymentid'         => $_orders_result['paymentid'],
        'fmf'                 => func_query_first_cell("SELECT value FROM $sql_tbl[order_extras] WHERE orderid = '$orderid' AND khash = 'fmf'"),
        'filters'             => func_query_first_cell("SELECT value FROM $sql_tbl[order_extras] WHERE orderid = '$orderid' AND khash = 'filters'")
    );

    $result['order_total'] = $result['no_refund_total'];

    $is_save = false;

    if (isset($data['paypal'])) {

        if (isset($data['paypal']['subtrans'])) {

            foreach($data['paypal']['subtrans'] as $tid => $t) {

                if ($t['type'] != 'Refunded')
                    continue;

                func_unset($t, 'type');

                if (!isset($result['refunds']))
                    $result['refunds'] = array();
                if (isset($t['date']))
                    $t['date'] += $config['Appearance']['timezone_offset'];

                $result['refunds'][$tid] = $t;
                $result['no_refund_total'] -= $t['amount'];

            }

        }

        if (isset($data['paypal']['main']) && !empty($data['paypal']['main']) && is_array($data['paypal']['main'])) {

            if ($result['main']['txnid'] == $data['paypal']['main']['txnid']) {

                $result['main'] = func_array_merge($result['main'], $data['paypal']['main']);

            } else {

                func_unset($data['paypal'], 'main');

                $is_save = true;

            }

        }

        if (isset($result['main']['pendingreason']) && !empty($result['main']['pendingreason'])) {
            $result['main']['pendingreason_text'] = func_get_langvar_by_name('txt_paypal_pendingreason_' . $result['main']['pendingreason'], array(), false, true);
        }

        if (isset($result['main']['reasoncode']) && !empty($result['main']['reasoncode'])) {
            $result['main']['reasoncode_text'] = func_get_langvar_by_name('txt_paypal_reversereason_' . $result['main']['reasoncode'], array(), false, true);
        }

    }

    if ($result['method'] == 'UK' && !empty($result['refunds']))
        $result['no_refund_total'] = 0;

    if (!empty($result['filters'])) {
        $result['filters'] = unserialize($result['filters']);
    }

    if ($is_save)
        func_array2update(
            'orders',
            array(
                'extra' => addslashes(serialize($data)),
            ),
            "orderid = '$orderid'"
        );

    return $result;
}

/**
 * Do NVP API request (UK version)
 */
function func_paypal_do_uk($access, $post)
{
    $access = func_paypal_define_access($access);

    if (!$access) {
        return array('', 'Inner error: The payment method is disabled or its configuration settings are incorrect');
    }

    x_load('http');

    $str = array();

    $str['vendor']     = $access['vendor'];
    $str['partner'] = $access['partner'];
    $str['user']     = $access['user'];
    $str['pwd']         = $access['pwd'];

    $requestid = isset($post['requestid']) ? $post['requestid'] : XC_TIME;

    func_unset($post, 'requestid');

    foreach (array('currency', 'pagestyle', 'hdrimg', 'payflowcolor') as $_param) {
        if (isset($post[$_param])) {
            if (!empty($access[$_param])) {
                $post[$_param] = $access[$_param];
            } else {
                unset($post[$_param]);
            }
        }
    }

    if (isset($post['invnum'])) {
        $post['invnum'] = $access['prefix'] . $post['invnum'];
    }


    if (isset($post['notifyurl'])) {

        $post['notifyurl'] = func_get_securable_current_location() . '/payment/ps_paypal_ipn.php?notify_from=pro';

    }

    $str = func_array_merge($str, $post);

    $data = array();

    foreach($str as $k => $v) {

        $data[] = strtoupper($k) . "[" . strlen($v) . "]=" . $v;

    }

    $data = implode("&", $data);

    $url = $access['test_mode']
        ? "https://pilot-payflowpro.paypal.com:443"
        : "https://payflowpro.paypal.com:443";

    $headers = array(
        "X-VPS-REQUEST-ID"                     => $requestid,
        "X-VPS-VIT-CLIENT-CERTIFICATION-ID" => '7894b92104f04ffb4f38a8236ca48db3',
    );


    if (defined('PAYPAL_DEBUG')) {

        x_log_add('paypal', "*** Request:\n\n" . print_r($str, true));

    }

    list($headers, $response) = func_https_request('POST', $url, $data, '', '', "application/x-www-form-urlencoded", '', '', '', $headers);

    if (defined('PAYPAL_DEBUG')) {

        x_log_add('paypal', "*** Response:\n\n$response");

    }

    if (empty($response))
        return array($headers, $response);

    $result = array();

    $tmp = array();

    parse_str($response, $tmp);

    if (empty($tmp) || !is_array($tmp))
        return array($headers, $response);

    if (defined('PAYPAL_DEBUG')) {

        x_log_add('paypal', "*** Parsed response:\n\n" . print_r($tmp, true));

    }

    foreach($tmp as $k => $v) {

        $result[strtolower($k)] = urldecode($v);

    }

    return array($headers, $response, $result);
}

/**
 * Detect state by PayPal response data
 */
function func_paypal_detect_state($country, $state, $zipcode, &$err)
{
    global $sql_tbl;

    if (empty($state)) {
        $err = 3;
        return '';
    }    

    $state         = func_addslashes($state);
    $country     = func_addslashes($country);

    $state_exists = (func_query_first_cell("SELECT COUNT(*) FROM $sql_tbl[states] WHERE country_code = '$country' AND code = '$state'") > 0);

    if ($state_exists)
        return $state;

    $country_data = func_query_first("SELECT code, display_states FROM $sql_tbl[countries] WHERE code = '$country' AND active = 'Y'");

    if (empty($country_data)) {
        $err = 1;
        return '';
    }

    if ($country_data['display_states'] != 'Y')
        return $state;

    $has_states = (func_query_first_cell("SELECT COUNT(*) FROM $sql_tbl[states] WHERE country_code = '$country'") > 0);

    if (!$has_states)
        return $state;

    $state_code = func_query_first_cell("SELECT code FROM $sql_tbl[states] WHERE country_code = '$country' AND state = '$state'");

    if (!empty($state_code))
        return $state_code;

    if ($country == 'GB') {
        x_load('user');
        if ($code = func_detect_state_by_zipcode($country, $zipcode)) {
            return $code;
        }   
    }

    $err = 2;

    return func_query_first_cell("SELECT code FROM $sql_tbl[states] WHERE country_code = '$country'");

}

/*
 Get applicabale state name/code for user country
*/
function func_paypal_get_state($userinfo, $prefix = 's_')
{

    if (
        (
            $userinfo[$prefix . 'country'] == 'US' 
            || $userinfo[$prefix . 'country'] == 'CA'
        )
    ) {
        $state = $userinfo[$prefix . 'state'];
    } elseif($userinfo[$prefix . 'statename'] != '') {
        $state = $userinfo[$prefix . 'statename'];
    } else {
        $state = 'Other';
    }

    return $state;
}

function func_paypal_get_capture_orderid($txnid)
{
    global $sql_tbl;

    $res = func_query_column("SELECT cs.orderid FROM $sql_tbl[order_extras] as cs INNER JOIN $sql_tbl[order_extras] as txnid ON txnid.khash = 'paypal_txnid' AND txnid.value = '$txnid' WHERE cs.khash = 'capture_status' AND cs.value = 'A' AND cs.orderid = txnid.orderid");

    return $res
        ? array_unique($res)
        : false;
}

function func_ps_paypal_pro_do_capture($order)
{
    global $config;

    if ($order['order']['paypal']['fmf'])
        return array(
            false,
            func_get_langvar_by_name('txt_paypal_fmf_note', array(), false, true),
        );

    if (
        $config['paypal_solution'] == 'uk'
        || $config['paypal_solution'] == 'advanced'
        || $config['paypal_solution'] == 'payflowlink'
    ) {

        if ($order['order']['paypal']['method'] != 'UK')
            return array(false, false);

        $extra = array(
            'name'     => 'pnref',
            'value' => $order['order']['paypal']['main']['txnid'],
        );

        $post = array(
            'tender'     => 'P',
            'trxtype'     => 'D',
            'origid'     => $order['order']['paypal']['main']['txnid'],
        );

        $res = func_paypal_do_uk($order['order']['paymentid'], $post);

        $err = func_paypal_prepare_errors_uk($res);

        if ($err && $err['error_code'] == '111')
            return array(
                true,
                $err['error'], $extra
            );
        
        // Change transation id to captured transation id
        if (
            !$err
            && !empty($res[2]['pnref'])
        ) {
            $extra['value'] = $res[2]['pnref'];
            func_array2insert(
                'order_extras',
                array(
                    'orderid' => $order['order']['orderid'],
                    'khash' => $extra['name'],
                    'value' => $extra['value']
                ),
                true
            );
        }

        return array(
            !$err,
            ($err ? $err['error'] : ''),
            $extra);

    } else {

        if (
            $order['order']['paypal']['method'] != 'US'
            && $order['order']['paypal']['method'] != 'PH'
        ) {
            return array(
                false,
                false,
            );
        }

        $query = array(
            'method'             => 'DoCapture',
            'AuthorizationID'     => $order['order']['paypal']['main']['txnid'],
            'AMT'                 => $order['order']['total'],
            'CompleteType'         => 'Complete',
        );

        $res = func_paypal_do_nvp($order['order']['paymentid'], $query);

        $status = false;

        $err_msg = false;

        $extra = array(
            'name'     => 'paypal_txnid',
            'value' => $order['order']['paypal']['main']['txnid'],
        );

        if ($res) {

            if (strtolower($res[2]['ack']) != 'success') {

                $err = func_paypal_prepare_errors($res[2]);

                foreach ($err as $e) {

                    if ($e['code'] == 10602)
                        $status = true;

                    $err_msg .= $e['desc'] . " (error code: " . $e['code'] . ")<br />\n";

                }

            } else {

                if (strcasecmp($res[2]['paymentstatus'], 'Pending') == 0) {
                    $extra['is_pending'] = true;
                }

                $status = true;

            }

        }

        // Change transation id to captured transation id to work refund transaction properly
        if (
            $status
            && !empty($res[2]['transactionid'])
        ) {
            $extra['value'] = $res[2]['transactionid'];
            func_array2insert(
                'order_extras',
                array(
                    'orderid' => $order['order']['orderid'],
                    'khash' => $extra['name'],
                    'value' => $extra['value']
                ),
                true
            );
        }

        if ($status) {
            func_paypal_update_order($order['order']['orderid']);
        }

        return array(
            $status,
            $err_msg,
            $extra,
        );

    }

}

function func_ps_paypal_pro_do_void($order)
{
    global $config;

    if (
        $config['paypal_solution'] == 'uk'
        || $config['paypal_solution'] == 'advanced'
        || $config['paypal_solution'] == 'payflowlink'
    ) {

        if ($order['order']['paypal']['method'] != 'UK')
            return array(
                false,
                false,
            );

        $extra = array(
            'name'     => 'pnref',
            'value' => $order['order']['paypal']['main']['txnid'],
        );

        $post = array(
            'tender'     => 'P',
            'trxtype'     => 'V',
            'origid'     => $order['order']['paypal']['main']['txnid'],
        );

        $res = func_paypal_do_uk($order['order']['paymentid'], $post);

        $err = func_paypal_prepare_errors_uk($res);

        if ($err && $err['error_code'] == '12')
            return array(
                true,
                $err['error'],
                $extra,
            );

        return array(
            !$err,
            ($err ? $err['error'] : ''),
            $extra,
        );

    } else {

        if (
            $order['order']['paypal']['method'] != 'US'
            && $order['order']['paypal']['method'] != 'PH'
        ) {
            return array(false, false);
        }

        $query = array(
            'method'             => 'DoVoid',
            'AuthorizationID'     => $order['order']['paypal']['main']['txnid'],
        );

        $res = func_paypal_do_nvp($order['order']['paymentid'], $query);

        $status = false;

        $err_msg = false;

        if ($res) {

            if (strtolower($res[2]['ack']) != 'success') {

                $err = func_paypal_prepare_errors($res[2]);

                foreach ($err as $e) {

                    if ($e['code'] == 10600)
                        $status = true;

                    $err_msg .= $e['desc'] . " (error code: " . $e['code'] . ")<br />\n";
                }

            } else {

                $status = true;

                func_paypal_update_order($order['order']['orderid']);

            }

        }

        $extra = array(
            'name'     => 'paypal_txnid',
            'value' => $order['order']['paypal']['main']['txnid'],
        );

        return array(
            $status,
            $err_msg,
            $extra,
        );

    }

}

function funct_ps_paypal_pro_fmf($orderid, $approve)
{
    global $sql_tbl, $config;

    $order = func_order_data($orderid);

    if (
        $config['paypal_solution'] == 'uk'
        || $config['paypal_solution'] == 'advanced'
        || $config['paypal_solution'] == 'payflowlink'
    ) {

       if ($order['order']['paypal']['method'] != 'UK')
            return array(
                false,
                false,
            );

/*
        $extra = array(
            'name' => 'pnref',
            'value' => $order['order']['paypal']['main']['txnid']
        );

        $post = array(
            'tender' => 'P',
            'trxtype' => 'V',
            'origid' => $order['order']['paypal']['main']['txnid']
        );

        $res = func_paypal_do_uk($order['order']['paymentid'], $post);

        $err = func_paypal_prepare_errors_uk($res);

        if ($err && $err['error_code'] == '12')
            return array(true, $err['error'], $extra);

        return array(!$err, ($err ? $err['error'] : ''), $extra);
*/
    } else {

        if ($order['order']['paypal']['method'] != 'US')
            return array(
                false,
                false,
            );

        $query = array(
            'method'         => 'ManagePendingTransactionStatus',
            'transactionid' => $order['order']['paypal']['main']['txnid'],
            'action'         => $approve ? 'Accept' : 'Deny',
        );

        $res = func_paypal_do_nvp($order['order']['paymentid'], $query);

        $status = false;

        $err_msg = false;

        if ($res) {

            if (strtolower($res[2]['ack']) != 'success') {

                $err = func_paypal_prepare_errors($res[2]);

                foreach ($err as $e) {
                    $err_msg .= $e['desc'] . " (error code: " . $e['code'] . ")<br />\n";
                }

            } else {

                $status = true;

                $orderids = func_get_order_ids($order['order']['orderid']);

                db_query("UPDATE $sql_tbl[order_extras] SET value = '0' WHERE khash = 'fmf' AND orderid IN ('" . implode("','", $orderids). "')");

                if ($approve) {

                    // Change status of all orders to P if current status is Q
                    $current_status = func_query_first_cell("SELECT status FROM $sql_tbl[orders] WHERE orderid = '" . $order['order']['orderid']. "'");

                    if ($current_status == 'Q') {

                        foreach ($orderids as $oid) {

                            func_change_order_status($oid, 'P');

                        }

                    }

                } else {

                    // Decline all orders with this transaction if transaction is declined
                    foreach ($orderids as $oid) {

                        func_change_order_status($oid, 'D');

                    }

                }

            }

        }

        return array(
            $status,
            $err_msg,
        );

    }

}

function func_ps_paypal_do_capture($order)
{
    return func_ps_paypal_pro_do_capture($order);
    
}

function func_ps_paypal_do_void($order)
{
    return func_ps_paypal_pro_do_void($order);
}

function func_ps_paypal_pro_hosted_do_capture($order) { //{{{

    return func_ps_paypal_pro_do_capture($order);
    
} //}}}

function func_ps_paypal_pro_hosted_do_void($order) { //{{{

    return func_ps_paypal_pro_do_void($order);

} //}}}

/**
 * Parse FMF details
 */
function func_ps_paypal_pro_parse_filters($response)
{
    return array();
}

function func_paypal_check_ec_token_ttl()
{
    global $paypal_token, $paypal_token_ttl;

    x_session_register('paypal_token_ttl');
    x_session_register('paypal_token');

    if (
        !empty($paypal_token)
        && $paypal_token_ttl + X_PAYPAL_EC_TOKEN_TTL < XC_TIME
    ) {

        func_paypal_clear_ec_token();

        return false;

    }

    return true;
}

function func_paypal_clear_ec_token()
{
    global $paypal_token, $paypal_express_details, $paypal_token_ttl;

    x_session_register('paypal_token');
    x_session_register('paypal_express_details');
    x_session_register('paypal_token_ttl');

    $paypal_token             = false;
    $paypal_express_details = false;
    $paypal_token_ttl         = false;

    return true;
}

function func_paypal_get_cart_total_discount($cart) { //{{{

    $pp_currency = func_paypal_get_currency();

    $discount = func_paypal_convert_to_BasicAmountType($cart['discount'], $pp_currency);

    if (
        !empty($cart['coupon_discount'])
        && (empty($cart['coupon_type']) || $cart['coupon_type'] != 'free_ship')
    ) {
        $discount += func_paypal_convert_to_BasicAmountType($cart['coupon_discount'], $pp_currency);
    }

    if (!empty($cart['giftcert_discount'])) {
        $discount += func_paypal_convert_to_BasicAmountType($cart['giftcert_discount'], $pp_currency);
    }

    return $discount;

} //}}}

function func_paypal_get_cart_giftwrap_cost($cart) { //{{{

    $pp_currency = func_paypal_get_currency();

    $giftwrap_cost = 0;

    if (
        !empty($cart['giftwrap_cost'])
        && (
            !empty($cart['need_giftwrap'])
            && $cart['need_giftwrap'] == 'Y'
        )
    ) {
        $giftwrap_cost = func_paypal_convert_to_BasicAmountType($cart['giftwrap_cost'], $pp_currency);
    }

    return $giftwrap_cost;
} //}}}

/*
 * Calculates totals for PayPal and check if
 * OrderTotal = ItemTotal + ShippingTotal + TaxTotal + HandlingTotal
 */
function func_paypal_is_line_items_allowed($cart, $pp_total = 0) { //{{{

    if (empty($cart) || count($cart['products']) > XC_PAYPAL_LINEITEMS_LIMIT) {
        return array();
    }

    $pp_currency = func_paypal_get_currency();

    if (empty($pp_total)) {
        $pp_total = func_paypal_convert_to_BasicAmountType($cart['total_cost'], $pp_currency);
    }

    $item_total = func_paypal_convert_to_BasicAmountType($cart['subtotal'], $pp_currency);

    $item_total_itms = 0;

    foreach ($cart['products'] as $p) {
        $item_total_itms += $p['amount'] * func_paypal_convert_to_BasicAmountType($p['price'], $pp_currency);
    }

    if (!empty($cart['giftcerts']) && count($cart['giftcerts'])) {
        foreach ($cart['giftcerts'] as $g) {
            $item_total_itms += func_paypal_convert_to_BasicAmountType($g['amount'], $pp_currency);
        }
    }

    $giftwrap_cost = func_paypal_get_cart_giftwrap_cost($cart);
    $discount = func_paypal_get_cart_total_discount($cart);

    // since all values are rounded to two digits
    $result = array(
        'OrderTotal'     => $pp_total,
        'ItemTotal'      => $item_total + $giftwrap_cost - $discount,
        'ShippingTotal'  => func_paypal_convert_to_BasicAmountType($cart['shipping_cost'], $pp_currency),
        'TaxTotal'       => func_paypal_convert_to_BasicAmountType($cart['tax_cost'], $pp_currency),
        'HandlingTotal'  => func_paypal_convert_to_BasicAmountType($cart['payment_surcharge'], $pp_currency),
        'DiscountTotal'  => func_paypal_convert_to_BasicAmountType($discount, $pp_currency),
        'ItemsTotalDiff' => $item_total - $item_total_itms,
    );

    // thus delta should not be more than
    $delta = 0.01;

    // PayPal does not support taxes included into the product price
    // thus in certain tax configurations X-Cart cannot pass precise prices to PayPal
    // since PayPal limits all values to two digits after the decimal delimiter point

    // in case correction is required correct discount or taxes
    if ($result['ItemsTotalDiff'] > 0) {
        if ($result['DiscountTotal'] > 0) {
            $result['DiscountTotal'] -= $result['ItemsTotalDiff'];
        } else if ($result['TaxTotal'] > 0) {
            $result['TaxTotal'] += $result['ItemsTotalDiff'];
        }
    }

    // calculate diff after correction to avoid any errors related to total an correction
    $diff = abs( $result['OrderTotal'] + $discount // original discount
        - $result['DiscountTotal']
        - $result['ItemTotal']
        - $result['ShippingTotal']
        - $result['TaxTotal']
        - $result['HandlingTotal']
    );

    if ($diff > $delta) {
        // To avoid total mismatch clear totals
        $result = array();
    }

    return $result;

} //}}}

function func_paypal_get_line_items_payflow($cart, $pp_total, $pp_currency = NULL) { //{{{

    $post = array();

    $pp_paypal_totals = func_paypal_is_line_items_allowed($cart, $pp_total);

    if (!empty($pp_paypal_totals)) {

        $item_n = 0;

        foreach ($cart['products'] as $product) {

            $post['L_NAME' . $item_n] = $product['product'];
            $post['L_SKU' . $item_n] = $product['productcode'];
            $post['L_COST' . $item_n] = func_paypal_convert_to_BasicAmountType($product['price'], $pp_currency);
            $post['L_QTY' . $item_n] = $product['amount'];

            $item_n++;

        }

        if (!empty($cart['giftwrap_cost']) && !empty($cart['need_giftwrap']) && $cart['need_giftwrap'] == 'Y') {

            $post['L_NAME' . $item_n] = func_get_langvar_by_name('lbl_giftreg_gift_wrapping', NULL, FALSE, TRUE);
            $post['L_COST' . $item_n] = func_paypal_convert_to_BasicAmountType($cart['giftwrap_cost'], $pp_currency);
            $post['L_QTY' . $item_n] = 1;

            $item_n++;
        }

        $discount = func_paypal_get_cart_total_discount($cart);

        if ($discount > 0) {
            $post['DISCOUNT'] = func_paypal_convert_to_BasicAmountType($discount, $pp_currency);
        }

        $post['ITEMAMT'] = $pp_paypal_totals['ItemTotal'];
        $post['FREIGHTAMT'] = $pp_paypal_totals['ShippingTotal'];
        $post['HANDLINGAMT'] = $pp_paypal_totals['HandlingTotal'];
        if ($pp_paypal_totals['TaxTotal'] > 0) {
            $post['TAXAMT'] = $pp_paypal_totals['TaxTotal'];
        }

    }

    return $post;

} //}}}

function func_paypal_get_userinfo_payflow($userinfo, $address_types = array('S'), $split_name = false) { //{{{

    global $config;

    $post = array();

    if (!is_array($address_types)) {
        $address_types = array($address_types);
    }

    if (!empty($userinfo) && is_array($userinfo)) {

        foreach ($address_types as $type) {

            $prefix = ($type == 'S') ? 'SHIPTO' : 'BILLTO';
            $type = strtolower($type) . '_';

            $ship_firstname = !empty($userinfo[$type . 'firstname']) ? $userinfo[$type . 'firstname'] : $userinfo['firstname'];
            $ship_lastname = !empty($userinfo[$type . 'lastname']) ? $userinfo[$type . 'lastname'] : $userinfo['lastname'];

            if (!$split_name) {
                $post[$prefix . 'NAME']      = $ship_firstname . ' ' . $ship_lastname;
            } else {
                $post[$prefix . 'FIRSTNAME'] = $ship_firstname;
                $post[$prefix . 'LASTNAME']  = $ship_lastname;
            }

            $post[$prefix . 'STREET']        = $userinfo[$type . 'address'];
            if (!empty($userinfo[$type . 'address_2'])) {
                $post[$prefix . 'STREET2']   = $userinfo[$type . 'address_2'];
            }
            $post[$prefix . 'CITY']          = $userinfo[$type . 'city'];
            $post[$prefix . 'COUNTRY']       = $userinfo[$type . 'country'];
            $post[$prefix . 'STATE']         = func_paypal_get_state($userinfo, $type);
            $post[$prefix . 'ZIP']           = $userinfo[$type . 'zipcode'];

            if ($config['General']['zip4_support'] == 'Y' && $post[$prefix . 'COUNTRY'] == 'US' && !empty($userinfo[$type . 'zip4'])) {
                $post[$prefix . 'ZIP']      .= '-' . $userinfo[$type . 'zip4'];
            }

        }

        $post['PHONENUM']         = $userinfo['phone'];
    }

    return $post;

} //}}}

function func_paypal_get_payment_details_items_soap($cart, $correction) { //{{{
   
    $products = $cart['products'];

    if (empty($products)) {
        return '';
    }

    x_load('clean_urls');

    $pp_currency = func_paypal_get_currency();

    $out = '';
    foreach ($products as $p) {
        $name = substr($p['product'], 0, 127);
        $url = func_get_resource_url('P', $p['productid']);

        $amount = func_paypal_convert_to_BasicAmountType($p['price'], $pp_currency);

        $out .= <<<EOT

          <PaymentDetailsItem>
            <Name>$name</Name>
            <Amount currencyID="$pp_currency">$amount</Amount>
            <Quantity>$p[amount]</Quantity>
            <ItemURL>$url</ItemURL>
          </PaymentDetailsItem>
EOT;
    }

    if (!empty($cart['giftcerts'])) {
        $giftcerts = $cart['giftcerts'];
        foreach ($giftcerts as $g) {
            $itemname = strip_tags(func_get_langvar_by_name('lbl_gift_certificate', NULL, FALSE, TRUE));
            $amount = func_paypal_convert_to_BasicAmountType($g['amount'], $pp_currency);

            $out .= <<<EOT

          <PaymentDetailsItem>
            <Name>$itemname</Name>
            <Amount currencyID="$pp_currency">$amount</Amount>
            <Quantity>1</Quantity>
          </PaymentDetailsItem>
EOT;
        }
    }

    if (!empty($cart['giftwrap_cost']) && !empty($cart['need_giftwrap']) && $cart['need_giftwrap'] == 'Y') {
        $itemname = func_get_langvar_by_name('lbl_giftreg_gift_wrapping', NULL, FALSE, TRUE);
        $out .= <<<EOT

          <PaymentDetailsItem>
            <Name>$itemname</Name>
            <Amount currencyID="$pp_currency">$cart[giftwrap_cost]</Amount>
            <Quantity>1</Quantity>
          </PaymentDetailsItem>
EOT;
    }

    $discount = func_paypal_get_cart_total_discount($cart);

    if (
        !empty($correction['DiscountTotal'])
    ) {
        $discount = $correction['DiscountTotal'];
    }

    if ($discount > 0) {
        $itemname = strip_tags(func_get_langvar_by_name('lbl_discount', NULL, FALSE, TRUE));
        $discount = func_paypal_convert_to_BasicAmountType($discount, $pp_currency);
        $out .= <<<EOT

          <PaymentDetailsItem>
            <Name>$itemname</Name>
            <Amount currencyID="$pp_currency">-$discount</Amount>
            <Quantity>1</Quantity>
          </PaymentDetailsItem>
EOT;
    }

    return $out . "\n";
} //}}}

function func_paypal_get_payment_details_items_rest($cart) { //{{{

    $products = $cart['products'];

    $line_items = array();
    $shipping_cost = 0;

    if (empty($products)) {
        return $line_items;
    }

    x_load('clean_urls');

    $pp_currency = func_paypal_get_currency();

    $counter = 0; // first element number

    foreach ($products as $p) {
        $itemname = substr($p['product'], 0, 127);
        $amount = func_paypal_convert_to_BasicAmountType($p['price'], $pp_currency);

        $counter++;

        $line_items["item_name_$counter"] = $itemname;
        $line_items["quantity_$counter"] = $p['amount'];
        $line_items["amount_$counter"] = $amount;
    }

    if (!empty($cart['shipping_cost'])) {
        $shipping_cost = func_paypal_convert_to_BasicAmountType($cart['shipping_cost'], $pp_currency);

        $line_items["shipping_$counter"] = $shipping_cost;
    }

    if (!empty($cart['giftcerts'])) {
        $giftcerts = $cart['giftcerts'];
        foreach ($giftcerts as $g) {
            $itemname = strip_tags(func_get_langvar_by_name('lbl_gift_certificate', NULL, FALSE, TRUE));
            $amount = func_paypal_convert_to_BasicAmountType($g['amount'], $pp_currency);

            $counter++;

            $line_items["item_name_$counter"] = $itemname;
            $line_items["quantity_$counter"] = 1;
            $line_items["amount_$counter"] = $amount;
        }
    }

    if (!empty($cart['giftwrap_cost']) && !empty($cart['need_giftwrap']) && $cart['need_giftwrap'] == 'Y') {
        $itemname = strip_tags(func_get_langvar_by_name('lbl_giftreg_gift_wrapping', NULL, FALSE, TRUE));

        $counter++;

        $line_items["item_name_$counter"] = $itemname;
        $line_items["quantity_$counter"] = 1;
        $line_items["amount_$counter"] = $cart['giftwrap_cost'];
    }

    return $line_items;
} //}}}

function func_paypal_convert_to_BasicAmountType($val, $pp_curr='')
{
    settype($val, 'float');

    if (empty($pp_curr))
        $pp_curr = func_paypal_get_currency();

    if (in_array($pp_curr, array('JPY','TWD','HUF'))) {
        #bt:0089724
        $new_val = sprintf("%0.2f", floor($val));
    } else {
        $new_val = price_format($val);
    }

    return $new_val;
}

function func_paypal_get_currency($params = array()) { //{{{

    if (empty($params)) {

        global $module_params;

        $params = $module_params;

    }

    if (!empty($params['params']['currency'])) {

        return $params['params']['currency'];

    } else if (!empty($params['param03'])) {

        return $params['param03'];

    } else {

        return 'USD';

    }

} //}}}

/**
 * Get payment_cc_data(Fast_Lane_Checkout) and get payment_data(XPayments_Connector) for emulated_paypal
 * Disable Direct paypal payment_template for emulated_paypal 
 */
function func_paypal_adjust_payment_data($payment_data, $checkout_module)
{
    global $sql_tbl, $active_modules, $config;

    $payment_cc_data = $_cc_data = func_query_first("SELECT * FROM $sql_tbl[ccprocessors] WHERE processor='ps_paypal_pro.php'");

    $is_emulated_paypal = false;

    if (!empty($active_modules['XPayments_Connector'])) {
        func_xpay_func_load();
        $is_emulated_paypal = xpc_is_emulated_paypal($payment_data['paymentid']);
        if ($is_emulated_paypal) {
            $payment_cc_data = $_cc_data = xpc_get_module_params($payment_data['paymentid']);
            $payment_data['payment_template'] = 'customer/main/payment_xpc.tpl';
        }
    }

    if ($checkout_module == 'One_Page_Checkout') {
        func_unset($_cc_data, 'paymentid', 'processor');
        $payment_data = func_array_merge($payment_data, $_cc_data);
    }


    if ($checkout_module == 'One_Page_Checkout')
        return array($payment_data); //For One_Page_Checkout
    else
        return array($payment_data, $payment_cc_data); //For Fast_Lane_Checkout
}

function func_paypal_save_profile($parsed_address, $parsed_profile) { //{{{

    global $login, $login_type, $logged_userid, $config, $cart;

    x_load('user');

    if (defined('PAYPAL_DEBUG')) {

        $log_str = "*** Received profile data:\n\n" . print_r($parsed_address, true) . "\n\n" . print_r($parsed_profile, true). "\n\n";

        x_log_add('paypal', $log_str);

    }

    if ($parsed_address['country'] == 'US') {
        // check if we have extanded zipcode format support enabled
        // trim zipcode if support is disabled or validation fail bt:0139751
        if (
            $config['General']['zip4_support'] == 'Y'
            && preg_match('/(\\d{5})(-*)(\\d{4})/', $parsed_address['zipcode'], $zip_matches)
            && isset($zip_matches[1])
            && isset($zip_matches[3])
        ) {
            $parsed_address['zipcode'] = $zip_matches[1];
            $parsed_address['zip4'] = $zip_matches[3];
        } else {
            $parsed_address['zipcode'] = substr($parsed_address['zipcode'], 0, 5);
        }
    }

    if (!empty($login) && $login_type == 'C') {

        $cart = func_set_cart_address($cart, 'S', $parsed_address);

        // Fill empty address book
        if (func_is_address_book_empty($logged_userid)) {
            $addrbook = $parsed_address;
            $addrbook['address'] = $addrbook['address'] . "\n" . $addrbook['address_2'];
            func_unset($addrbook, 'address_2');
            $addrbook['default_S'] = 'Y';
            func_save_address($logged_userid, 0, $addrbook);
        }

    } else if ($config['General']['enable_anonymous_checkout'] == 'Y') {

        // Fill-in anonymous customer profile

        $pp_anon_user = array (
            'title'         => '', // unknown
            'firstname'     => $parsed_profile['firstname'],
            'lastname'      => $parsed_profile['lastname'],
            'email'         => $parsed_profile['email'],
            'referer'       => $parsed_profile['referer'],
            'address'       => array(
                'B' => array(
                    'firstname' => $parsed_profile['firstname'],
                    'lastname'  => $parsed_profile['lastname'],
                    'phone'     => $parsed_profile['phone']
                ),
                'S' => array(
                    'firstname' => $parsed_address['firstname'],
                    'lastname'  => $parsed_address['lastname'],
                    'phone'     => $parsed_address['phone']
                ),
            )
        );

        foreach ($parsed_address as $k => $v) {
            if (empty($pp_anon_user['address']['B'][$k]))
                $pp_anon_user['address']['B'][$k] = $v;

            if (empty($pp_anon_user['address']['S'][$k]))
                $pp_anon_user['address']['S'][$k] = $v;
        }

        // save anonymous customer info in session
        func_set_anonymous_userinfo($pp_anon_user);
    }

} //}}}

function func_paypal_bmcreatebutton($access, $button_parameters, $variables) { //{{{

    $post = $button_parameters;

    $post['METHOD'] = 'BMCreateButton';

    $i = 0;
    foreach ($variables as $v_name => $v_value) {
        $post['L_BUTTONVAR' . $i] = $v_name . '=' . $v_value;
        $i++;
    }

    $res = func_paypal_do_nvp($access, $post);

    if ($res) {

        $res = $res[2];

        if (defined('PAYPAL_DEBUG')) {
            x_log_add('paypal', "*** BMCreateButton Response:\n" . print_r($res, true));
        }

        if (!is_array($res)) {
            $res = false;
        }
    }

    return $res;

} //}}}

/**
 * Adds all PayPal methods, returns paymentid of "main" ps_paypal.php method
 */
function func_paypal_add_payment_methods($_orderby = 999, $add_only = '') { //{{{

    global $config, $active_modules;

    $methods_to_add = array();

    $insert_params = array (
        'payment_script' => 'payment_cc.php',
        'active' => 'N',
        'orderby' => $_orderby,
    );

    // PayPal Standard
    $insert_params['payment_method'] = 'PayPal';
    $insert_params['processor_file'] = 'ps_paypal.php';
    $insert_params['payment_template'] = 'customer/main/payment_offline.tpl';
    $methods_to_add[] = array('params' => $insert_params, 'for_return' => TRUE, 'no_processor' => FALSE);

    // PayPal ExpressCheckout
    $insert_params['payment_method'] = 'PayPal';
    $insert_params['processor_file'] = 'ps_paypal_pro.php';
    $insert_params['payment_template'] = 'customer/main/payment_offline.tpl';
    $methods_to_add[] = array('params' => $insert_params, 'for_return' => FALSE, 'no_processor' => FALSE);

    // PayPal PayPal Credit
    $insert_params['payment_method'] = 'PayPal Credit';
    $insert_params['processor_file'] = 'ps_paypal_bml.php';
    $insert_params['payment_template'] = 'customer/main/payment_offline.tpl';
    $methods_to_add[] = array('params' => $insert_params, 'for_return' => FALSE, 'no_processor' => FALSE);

    // PayPal DirectPayment
    $insert_params['payment_method'] = 'Credit or Debit card';
    $insert_params['processor_file'] = 'ps_paypal_pro.php';
    $insert_params['payment_template'] = 'customer/main/payment_cc.tpl';
    $methods_to_add[] = array('params' => $insert_params, 'for_return' => FALSE, 'no_processor' => TRUE);

    // PayPal Advanced
    $insert_params['payment_method'] = 'Credit or Debit card';
    $insert_params['processor_file'] = 'ps_paypal_advanced.php';
    $insert_params['payment_template'] = 'customer/main/payment_cc.tpl';
    $methods_to_add[] = array('params' => $insert_params, 'for_return' => FALSE, 'no_processor' => FALSE);

    // PayPal Payflow Link
    $insert_params['payment_method'] = 'Credit or Debit card';
    $insert_params['processor_file'] = 'ps_paypal_payflowlink.php';
    $insert_params['payment_template'] = 'customer/main/payment_cc.tpl';
    $methods_to_add[] = array('params' => $insert_params, 'for_return' => FALSE, 'no_processor' => FALSE);

    // PayPal Pro Hosted
    $insert_params['payment_method'] = 'Credit or Debit card';
    $insert_params['processor_file'] = 'ps_paypal_pro_hosted.php';
    $insert_params['payment_template'] = 'customer/main/payment_cc.tpl';
    $methods_to_add[] = array('params' => $insert_params, 'for_return' => FALSE, 'no_processor' => FALSE);

    foreach ($methods_to_add as $method) {
        if (empty($add_only) || $add_only == $method['params']['processor_file']) {
            $_paymentid = func_array2insert('payment_methods', $method['params']);
            if (!$method['no_processor']) {
                func_array2update('ccprocessors', array('paymentid' => $_paymentid), "processor = '" . $method['params']['processor_file'] . "'");
            }
            if ($method['for_return']) {
                $return_paymentid = $_paymentid;
            }
        }
    }

    return $return_paymentid;

} //}}}

function func_paypal_remove_payment_methods() { //{{{

    global $sql_tbl;

    $paypal_methods = "('" . implode("','", func_get_allowed_paypal_processors()). "')";

    db_query("DELETE FROM $sql_tbl[payment_methods] WHERE processor_file IN $paypal_methods");

    db_query("UPDATE $sql_tbl[ccprocessors] SET paymentid='0' WHERE processor IN $paypal_methods");


} //}}}

/**
 * This functions matches only allowed PayPal processors from pre-defined list.
 * If PayPal method is found, but it is not from allowed list assertion will happen.
 */
function func_is_paypal_processor($processor) { //{{{

    $return = in_array($processor, func_get_allowed_paypal_processors());

    assert('$return || !preg_match("/ps_paypal.*\.php/", $processor) /* '.__FUNCTION__.': unknown paypal method detected!*/');

    return $return;

} //}}

function func_get_allowed_paypal_processors() { //{{{

    static $paypals;

    if (is_null($paypals)) {
        $paypals = array(
            'ps_paypal.php',
            'ps_paypal_pro.php',
            'ps_paypal_advanced.php',
            'ps_paypal_payflowlink.php',
            'ps_paypal_pro_hosted.php',
            'ps_paypal_bml.php',
        );
    }

    return $paypals;

} //}}}

function func_get_paypal_solution_processor($solution) { //{{{

    switch ($solution) {
        case 'ipn':
            return 'ps_paypal.php';
            break;
        case 'express':
        case 'uk':
        case 'pro':
            return 'ps_paypal_pro.php';
            break;
        case 'advanced':
            return 'ps_paypal_advanced.php';
            break;
        case 'payflowlink':
            return 'ps_paypal_payflowlink.php';
            break;
        case 'pro_hosted':
            return 'ps_paypal_pro_hosted.php';
            break;
    }

} //}}}

?>
