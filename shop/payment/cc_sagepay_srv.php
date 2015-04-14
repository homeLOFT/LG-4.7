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
 * "Sage Pay Go - Server protocol v3.0" payment module (credit card processor)
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Payment interface
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v3 (xcart_4_7_0), 2015-02-17 23:56:28, cc_sagepay_srv.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

// Uncomment the below line to enable the debug log
// define('XC_SAGEPAY_SERVER_DEBUG', 1);

if (!isset($REQUEST_METHOD)) {
    $REQUEST_METHOD = $_SERVER['REQUEST_METHOD'];
}

if ($REQUEST_METHOD == 'POST' && $_POST['Status'] && $_POST['VendorTxCode']) {

    // Callback received

    require __DIR__.'/auth.php';

    if (!func_is_active_payment('cc_sagepay_srv.php')) {
        exit;
    }

    if (defined('XC_SAGEPAY_SERVER_DEBUG')) {
        func_pp_debug_log('sagepay-srv', 'C', $_POST);
    }

    $tmp = func_query_first("SELECT sessid, param1, param2 FROM $sql_tbl[cc_pp3_data] WHERE ref='" . $_POST['VendorTxCode'] . "'");
    $module_params = func_get_pm_params('cc_sagepay_srv.php');

    $bill_output['sessid'] = $tmp['sessid'];

    if(in_array($_POST['Status'], array('OK', 'OK REPEATED')))
    {
        // Signature string
        // VPSTxId+VendorTxCode+Status+TxAuthNo+VendorName+AVSCV2+SecurityKey+AddressResult+PostCodeResult+CV2Result+GiftAid+3DSecureStatus+CAVV+AddressStatus+PayerStatus+CardType+Last4Digits+DeclineCode+ExpiryDate+FraudResponse+BankAuthCode
        $sign_string = $_POST['VPSTxId']
        . $_POST['VendorTxCode']
        . $_POST['Status']
        . $_POST['TxAuthNo']
        . strtolower($module_params['param01'])
        . $_POST['AVSCV2']
        . $tmp['param1']
        . $_POST['AddressResult']
        . $_POST['PostCodeResult']
        . $_POST['CV2Result']
        . $_POST['GiftAid']
        . $_POST['3DSecureStatus']
        . $_POST['CAVV']
        . $_POST['AddressStatus']
        . $_POST['PayerStatus']
        . $_POST['CardType']
        . $_POST['Last4Digits']
        . $_POST['DeclineCode']
        . $_POST['ExpiryDate']
        . $_POST['FraudResponse']
        . $_POST['BankAuthCode'];

        $sign = strtoupper(md5($sign_string));

        $bill_output['code'] = $_POST['VPSSignature'] == $sign ? 1 : 3;
        $bill_output['billmes'] = ($_POST['VPSSignature'] == $sign ? '' : 'VPSSignature is incorrect! ') . 'AuthNo: ' . $_POST['TxAuthNo'];

        if ($module_params['use_preauth'] == 'Y' || func_is_preauth_force_enabled($secure_oid)) {
            $bill_output['is_preauth'] = true;
            $extra_order_data = array(
                'txnid' => $_POST['VPSTxId']."\n".$tmp['param1']."\n".$_POST['TxAuthNo']."\n".$_POST['VendorTxCode'],
                'capture_status' => 'A'
            );
        }
    }
    else
    {
        $bill_output['code'] = 2;
        $bill_output['billmes'] = 'Status: ' . $_POST['StatusDetail'] . ' ('.$_POST['Status'].') ';
    }

    $arr = array(
        'TxID'              => 'VPSTxID',
        'AVS/CVV2'          => 'AVSCV2',
        'AddressResult'     => 'AddressResult',
        'PostCodeResult'    => 'PostCodeResult',
        'CV2Result'         => 'CV2Result',
        '3DSecureStatus'    => '3DSecureStatus',
        'CAVV'              => 'CAVV',
        'PayerStatus'       => 'PayerStatus',
        'CardType'          => 'CardType',
        'Last4Digits'       => 'Last4Digits',
        'DeclineCode'       => 'DeclineCode',
        'ExpiryDate'        => 'ExpiryDate',
        'FraudResponse'     => 'FraudResponse',
        'BankAuthCode'      => 'BankAuthCode',
    );

    foreach($arr as $k => $v) {
        if(!empty($_POST[$v])) {
            $bill_output['billmes'] .= "\n\r" . $k . ': ' . $_POST[$v];
        }
    }

    require $xcart_dir . '/payment/payment_ccmid.php';

    if (defined('XC_SAGEPAY_SERVER_DEBUG')) {
        func_pp_debug_log('sagepay-srv', 'C', $bill_output);
    }

    $redirect_url = $bill_error
        ? 'error_message.php?' . 'error=' . $bill_error . $reason
        : 'cart.php?' . 'mode=order_message&orderids=' . $_orderids;

    echo "Status=OK\r\n"
    . 'RedirectURL=' . $current_location . '/' . $redirect_url . "\r\n"
    . "StatusDetail=\r\n";

    exit();

} else {

    if (!defined('XCART_START')) { header('Location: ../'); die('Access denied'); }

    if (!func_is_active_payment('cc_sagepay_srv.php')) {
        exit;
    }

    x_load('http');

    $pp_merch = $module_params['param01'];
    $pp_curr = $module_params['param03'];

    // Determine request URL (simulator, test server or live server)
    switch ($module_params['testmode']) {
        case 'S':
            $pp_test = 'https://test.sagepay.com:443/Simulator/VSPServerGateway.asp?Service=VendorRegisterTx';
            break;
        case 'Y':
            $pp_test = 'https://test.sagepay.com:443/gateway/service/vspserver-register.vsp';
            break;
        default:
            $pp_test = 'https://live.sagepay.com:443/gateway/service/vspserver-register.vsp';
    }

    func_pm_load('cc_sagepay_common');

    $pp_shift = $module_params['param05'];
    $_orderids = join('-', $secure_oid);

    $post = array(); // initialize the array

    $post['VPSProtocol'] = '3.00';
    $post['TxType'] = (($module_params['use_preauth'] == 'Y' || func_is_preauth_force_enabled($secure_oid)) ? 'DEFERRED' : 'PAYMENT');
    $post['Vendor'] = $pp_merch;
    $post['VendorTxCode'] = $pp_shift.$_orderids;
    $post['ReferrerID'] = '653E8C42-AD93-4654-BB91-C645678FA97B';
    $post['Amount'] = $cart['total_cost'];
    $post['Currency'] = $pp_curr;
    $post['Description'] = 'Your Cart';
    $post['NotificationURL'] = $current_location . '/payment/cc_sagepay_srv.php';
    $post['Profile'] = 'LOW';

    // Billing information
    $post['BillingSurname'] = $bill_lastname;
    $post['BillingFirstnames'] = $bill_firstname;
    $post['BillingAddress1'] = $userinfo['b_address'];
    if (!empty($userinfo['b_address_2'])) {
        $post['BillingAddress2'] = $userinfo['b_address_2'];
    }
    $post['BillingCity'] = $userinfo['b_city'];
    $post['BillingPostCode'] = $userinfo['b_zipcode'];
    $post['BillingCountry'] = $userinfo['b_country'];
    if (
        $userinfo['b_country'] == 'US'
        && !empty($userinfo['b_state'])
        && $userinfo['b_state'] != 'Other'
    ) {
        $post['BillingState'] = $userinfo['b_state'];
    }

    // Shipping information
    $post['DeliverySurname'] = $ship_lastname;
    $post['DeliveryFirstnames'] = $ship_firstname;
    $post['DeliveryAddress1'] = $userinfo['s_address'];
    if (!empty($userinfo['s_address_2'])) {
        $post['DeliveryAddress2'] = $userinfo['s_address_2'];
    }
    $post['DeliveryCity'] = $userinfo['s_city'];
    $post['DeliveryPostCode'] = $userinfo['s_zipcode'];
    $post['DeliveryCountry'] = $userinfo['s_country'];
    if (
        $userinfo['s_country'] == 'US'
        && !empty($userinfo['s_state'])
        && $userinfo['s_state'] != 'Other'
    ) {
        $post['DeliveryState'] = $userinfo['s_state'];
    }

    $post['CustomerEMail'] = $userinfo['email'];
    $post['Basket'] = func_cc_sagepay_get_basket();
    $post['GiftAidPayment'] = '0';
    $post['ApplyAVSCV2'] = $module_params['param06'];
    $post['Apply3DSecure'] = $module_params['param07'];
    $post['ClientIPAddress'] = func_get_valid_ip($REMOTE_ADDR);


    // Tide up the entire values
    $post = func_sagepay_clean_inputs($post);

    if (defined('XC_SAGEPAY_SERVER_DEBUG')) {
        func_pp_debug_log('sagepay-srv', 'I', $post);
    }

    // Send initial request and obtain the key
    list($a, $return) = func_https_request('POST', $pp_test, $post);

    if (defined('XC_SAGEPAY_SERVER_DEBUG')) {
        func_pp_debug_log('sagepay-srv', 'C', $return);
    }

    // Parse response
    $ret = str_replace("\r\n", '&', $return);
    $ret_arr = explode('&', $ret);

    $response = array();

    foreach ($ret_arr as $ret) {
        if (preg_match('/([^=]+?)=(.+)/', $ret, $matches)) {
            $response[$matches[1]] = $matches[2];
        }
    }

    if ($response['Status'] == 'OK' && $response['NextURL']) {

        // Redirect to SagePay
        $cc_data = array(
            'ref' => $pp_shift.$_orderids,
            'param1' => $response['SecurityKey'],
            'param2' => $response['VPSTxId'],
            'sessid' => $XCARTSESSID

        );
        func_array2insert('cc_pp3_data', $cc_data, true);

        func_header_location($response['NextURL']);
        exit();
    }
    else {

        // Return with error
        $bill_output['code'] = 2;

        $bill_output['billmes'] = 'Status: ' . $response['StatusDetail'] . ' (' . $response['Status'] . ')';
        if (!empty($response['VPSTxID'])) {
            $bill_output['billmes'] .= ' (TxID: ' . $response['VPSTxID'] . ')';
        }

        require $xcart_dir . '/payment/payment_ccend.php';
    }
}
?>
