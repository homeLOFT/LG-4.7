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
 * "Beanstream INTERAC Online" payment module (credit card processor)
 * http://www.beanstream.com/public/interac.asp
 * Payment method for Canadian merchants
 * Integrated method: Server-to-Server
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Payment interface
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    3968cba5ecdb78320d43cbe05a25fe35597bc800, v10 (xcart_4_7_0), 2015-02-17 13:29:01, cc_bean_interaco.php, aim
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

$iDebitFields = array();
$postURL = 'https://www.beanstream.com:443/scripts/process_transaction.asp';

if (!defined('XCART_START')) {

    require __DIR__.'/auth.php';

    x_session_register('cart');
    x_session_register('secure_oid');

    // Get parameters of the payment module
    $module_params = func_query_first("SELECT * FROM " . $sql_tbl['ccprocessors'] . " where processor='cc_bean_interaco.php'");

    // Load user functions
    x_load('user');

    // Get user info
    $userinfo = func_userinfo($logged_userid, !empty($login) ? $user_account['usertype'] : '');

    if (defined('BEANSTREAM_DEBUG')) {

        $response = array(
            'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'],
            'GET' => $_GET,
            'POST' => $_POST,
        );

        func_pp_debug_log('beanstream', 'R', $response);
    }

    if (isset($_GET['funded'])) {

        define('BEANSTREAM_INTERAC_RESPONSE', true);

        $requiredPostFields = array(
            'IDEBIT_MERCHDATA',
            'IDEBIT_INVOICE',
            'IDEBIT_AMOUNT',
            'IDEBIT_FUNDEDURL',
            'IDEBIT_NOTFUNDEDURL',
            'IDEBIT_ISSLANG',
            'IDEBIT_TRACK2',
            'IDEBIT_ISSCONF',
            'IDEBIT_ISSNAME',
            'IDEBIT_VERSION',
        );

        $iDebitFields = array(
            'funded=' . $_GET['funded'],
        );

        foreach ($requiredPostFields as $field) {
            $iDebitFields[] = $field . '=' . $_POST[$field];
        }

        $postURL = 'https://www.beanstream.com:443/scripts/process_transaction_auth.asp';
    }
}

// List of fields that is required to be displayed to customer after transaction complete (approved or declined)
$ioReportFields = array(
    'trnId'          => 'Transaction ID',
    'trnOrderNumber' => 'Order Number',
    'trnAmount'      => 'Transaction Amount',
    'ioInstName'     => 'IO Institution Name',
    'ioConfCode'     => 'IO ConfirmationCode',
    'authCode'       => 'Authorization Code',
    'messageId'      => 'Message ID',
    'messageText'    => 'Message Text',
    'trnDate'        => 'Transaction Date',
);


x_load('http');


// Prepare post request to Beanstream    
$post = array();

if (!empty($iDebitFields)) {

    // Repost fields received from Bank
    foreach ($iDebitFields as $_dField) {
        $post[] = $_dField;
    }

} else {

    // Prepare initial post to Beanstream

    $merchant_id  = $module_params['param01']; // Merchant ID: 9 digits
    $order_prefix = $module_params['param04'];
    $_orderids = implode('-', $secure_oid);
    
    if (!$duplicate) {
        db_query("REPLACE INTO $sql_tbl[cc_pp3_data] (ref,sessid,param1) VALUES ('".addslashes($_orderids)."','".$XCARTSESSID."','".(float)price_format($cart["total_cost"])."')");
    }

    $post[] = 'requestType=BACKEND';
    $post[] = 'merchant_id=' . $merchant_id;

    if ($module_params['param02'] == 'Y') {
        $post[] = 'username=' . $module_params['param03'];
        $post[] = 'password=' . $module_params['param05'];
    }

    $post[] = 'paymentMethod=IO';
    $post[] = 'trnOrderNumber=' . $order_prefix . $_orderids;
    $post[] = 'trnType=P';

    $post[] = 'trnAmount=' . $cart['total_cost'];

    $post[] = 'ordName=' . trim($userinfo['b_firstname'] . ' ' . $userinfo['b_lastname']);
    $post[] = 'ordEmailAddress=' . $userinfo['email'];
    $post[] = 'ordPhoneNumber=' . $userinfo['b_phone'];
    $post[] = 'ordAddress1=' . $userinfo['b_address'];
    $post[] = 'ordCity=' . $userinfo['b_city'];
    $post[] = 'ordProvince=' . (strlen($userinfo['b_state']) != 2 ? '--' : $userinfo['b_state']);
    $post[] = 'ordPostalCode=' . preg_replace('/ /', '', $userinfo['b_zipcode']);
    $post[] = 'ordCountry=' . $userinfo['b_country'];

    $post[] = 'shipAddress1=' . $userinfo['s_address'];
    $post[] = 'shipCity=' . $userinfo['s_city'];
    $post[] = 'shipProvince=' . (strlen($userinfo['s_state']) != 2 ? '--' : $userinfo['s_state']);
    $post[] = 'shipPostalCode=' . preg_replace('/ /', '', $userinfo['s_zipcode']);
    $post[] = 'shipCountry=' . $userinfo['s_country'];
    $post[] = 'shipPhoneNumber=' . $userinfo['s_phone'];

    $post[] = 'ref1=' . $_orderids;

}

if (defined('BEANSTREAM_DEBUG')) {
    func_pp_debug_log('beanstream', 'I', array('Post URL' => $postURL, 'data' => $post));
}

list($a, $response) = func_https_request('POST', $postURL, $post);

if (!empty($response)) {

    $responseReport = $responseData = array();

    // Parse response
    $responseRows = explode('&', $response);
    foreach ($responseRows as $row) {
        list($key, $val) = explode('=', $row);
        if (!empty($key)) {
            $responseData[$key] = urldecode($val);
            if (isset($ioReportFields[$key])) {
                if ('trnAmount' == $key) {
                    $responseReport[] = sprintf('%s: $%s (CAD)', $ioReportFields[$key], urldecode($val));
                } else {
                    $responseReport[] = sprintf('%s: %s', $ioReportFields[$key], urldecode($val));
                }
            }
        }
    }

    if (defined('BEANSTREAM_DEBUG')) {
        func_pp_debug_log('beanstream', 'R', $responseData);
    }

    if (isset($responseData['pageContents'])) {
        // We got an HTML for redirecting customer to the Beanstream site (on initial request). Just echo it and exit.
        die($responseData['pageContents']);

    } elseif (defined('BEANSTREAM_INTERAC_RESPONSE') && isset($responseData['trnApproved'])) {

        // Final response: prepare the transaction status

        if (!empty($responseData['ref1'])) {
            $cart_order_id = $responseData['ref1'];
            $tmp = func_query_first("SELECT sessid,param1 FROM $sql_tbl[cc_pp3_data] WHERE ref='$cart_order_id'");
            $tmp['param1'] = price_format($tmp['param1']);
            $bill_output['sessid'] = $tmp['sessid'];

            x_session_id($bill_output['sessid']);
        }

        $_report = array();
        $_report['interaco'] = 'Y';
        foreach ($ioReportFields as $key => $value) {
            $_report['interaco_' . $key] = $responseData[$key];
        }

        $extra_order_data_inner['interaco'] = $_report;

        if (1 == $responseData['trnApproved']) {

            // Successful transaction
            $bill_output['code'] = 1;
            $bill_output['billmes'] = $responseData['messageText'];

        } else {

            // Declined transaction
            $errorMsg = $responseData['messageText'];
            $errorMsg .= "\n-------------------------\nBeanstream INTERAC Online transaction result:\n";
            $errorMsg .= implode("\n", $responseReport);
            $errorMsg .= "\n-------------------------\n";

        }

        if ($errorMsg) {
            $bill_output['billmes'] = $errorMsg;
            $bill_output['code'] = 2;
        }

        require($xcart_dir.'/payment/payment_ccend.php');

        exit;
 
    } else {
        $errorMsg = 'Payment gateway returned unexpected response' . (isset($responseData['messageText']) ? sprintf(' (%s)', $responseData['messageText']) : '');
    }
}

if ($errorMsg) {
    $bill_output['billmes'] = $errorMsg;
    $bill_output['code'] = 2;
}

