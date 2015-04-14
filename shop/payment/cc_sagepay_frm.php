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
 * "Sage Pay Go - Form protocol v3.0" payment module (credit card processor)
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Payment interface
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    3968cba5ecdb78320d43cbe05a25fe35597bc800, v2 (xcart_4_7_0), 2015-02-17 13:29:01, cc_sagepay_frm.php, aim
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

// Uncomment the below line to enable the debug log
// define('XC_SAGEPAY_FORM_DEBUG', 1);

if (!isset($REQUEST_METHOD)) {
    $REQUEST_METHOD = $_SERVER['REQUEST_METHOD'];
}

if ($REQUEST_METHOD == 'GET' && isset($_GET['crypt'])) {

    require __DIR__.'/auth.php';

    if (defined('XC_SAGEPAY_FORM_DEBUG')) {
        func_pp_debug_log('sagepay-frm', 'C', $_GET);
    }

    x_load('payment');

    func_pm_load('cc_sagepay_common');

    $pass = func_query_first_cell("SELECT param02 FROM $sql_tbl[ccprocessors] WHERE processor='cc_sagepay_frm.php'");

    $response = array();

    try {

        parse_str(XCSagepayUtil::decryptAes($crypt, $pass), $response);

        if (trim($response['Status']) == "OK") {
            $bill_output['code'] = 1;
            $bill_output['billmes'] = "AuthNo: ".$response['TxAuthNo'];
        } else {
            $bill_output['code'] = 2;
            $bill_output['billmes'] = "Status: ".$response['StatusDetail']." (".trim($response['Status']).') ';
        }

    } catch (Exception $ex) {

        $bill_output['code'] = 2;
        $bill_output['billmes'] = 'Status: VPSSignature is incorrect! ' . 'Error: ' . $ex->getMessage();
    }

    if (defined('XC_SAGEPAY_FORM_DEBUG')) {
        func_pp_debug_log('sagepay-frm', 'R', $response);
    }

    $bill_output['sessid'] = func_query_first_cell("select sessid from $sql_tbl[cc_pp3_data] where ref='".func_addslashes($response['VendorTxCode'])."'");

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
        'FraudResponse'     => 'FraudResponse',
        'BankAuthCode'      => 'BankAuthCode',
    );

    foreach($arr as $k => $v) {
        if(!empty($response[$v])) {
            $bill_output['billmes'] .= "\n\r" . $k . ': ' . $response[$v];
        }
    }

    if (!empty($response['Amount'])) {
        $payment_return = array(
            'total' => str_replace(",", '', $response['Amount'])
        );
    }

    define('DISABLE_IP_CHECK',true); #bt:112622

    require($xcart_dir.'/payment/payment_ccend.php');

} else {

    if (!defined('XCART_START')) { header("Location: ../"); die("Access denied"); }

    if (!func_is_active_payment('cc_sagepay_frm.php')) {
        exit;
    }

    func_pm_load('cc_sagepay_common');

    $pp_merch = $module_params['param01'];
    $pp_pass = $module_params['param02'];
    $pp_curr = $module_params['param03'];

    // Determine request URL (simulator, test server or live server)
    switch ($module_params['testmode']) {
    case 'S':
        $pp_test = 'https://test.sagepay.com/Simulator/VSPFormGateway.asp';
        break;
    case 'Y':
        $pp_test = 'https://test.sagepay.com/gateway/service/vspform-register.vsp';
        break;
    default:
        $pp_test = 'https://live.sagepay.com/gateway/service/vspform-register.vsp';
    }
    $pp_shift = preg_replace('/[^\w\d_-]/S', '', $module_params['param05']);
    $_orderids = join('-',$secure_oid);

    if (!$duplicate) {
        db_query("REPLACE INTO $sql_tbl[cc_pp3_data] (ref,sessid) VALUES ('" . addslashes($pp_shift . $_orderids) . "','" . $XCARTSESSID . "')");
    }

    $crypt = array(); // initialize the array

    $crypt['VendorTxCode'] = $pp_shift.$_orderids;
    $crypt['ReferrerID'] = "653E8C42-AD93-4654-BB91-C645678FA97B";
    $crypt['Amount'] = price_format($cart['total_cost']);
    $crypt['Currency'] = $pp_curr;
    $crypt['Description'] = "Your Cart";
    $crypt['SuccessURL'] = $current_location.'/payment/cc_sagepay_frm.php';
    $crypt['FailureURL'] = $current_location.'/payment/cc_sagepay_frm.php';

    $crypt['CustomerName'] = $bill_name;
    $crypt['CustomerEMail'] = $userinfo['email'];
    $crypt['VendorEMail'] = $config['Company']['orders_department'];
    $crypt['SendEMail'] = 1;

    // Billing information
    $crypt['BillingSurname'] = $bill_lastname;
    $crypt['BillingFirstnames'] = $bill_firstname;
    $crypt['BillingAddress1'] = $userinfo['b_address'];
    if (!empty($userinfo['b_address_2'])) {
        $crypt['BillingAddress2'] = $userinfo['b_address_2'];
    }
    $crypt['BillingCity'] = $userinfo['b_city'];
    $crypt['BillingPostCode'] = $userinfo['b_zipcode'];
    $crypt['BillingCountry'] = $userinfo['b_country'];
    if (
        $userinfo['b_country'] == 'US'
        && !empty($userinfo['b_state'])
        && $userinfo['b_state'] != 'Other'
    ) {
        $crypt['BillingState'] = $userinfo['b_state'];
    }

    // Shipping information
    $crypt['DeliverySurname'] = $ship_lastname;
    $crypt['DeliveryFirstnames'] = $ship_firstname;
    $crypt['DeliveryAddress1'] = $userinfo['s_address'];
    if (!empty($userinfo['s_address_2'])) {
        $crypt['DeliveryAddress2'] = $userinfo['s_address_2'];
    }
    $crypt['DeliveryCity'] = $userinfo['s_city'];
    $crypt['DeliveryPostCode'] = $userinfo['s_zipcode'];
    $crypt['DeliveryCountry'] = $userinfo['s_country'];
    if (
        $userinfo['s_country'] == 'US'
        && !empty($userinfo['s_state'])
        && $userinfo['s_state'] != 'Other'
    ) {
        $crypt['DeliveryState'] = $userinfo['s_state'];
    }

    $crypt['Basket'] = func_cc_sagepay_get_basket();

    $crypt['AllowGiftAid'] = '0';
    $crypt['ApplyAVSCV2'] = $module_params['param06'];
    $crypt['Apply3DSecure'] = $module_params['param07'];

    // Tide up the entire values
    $crypt = func_sagepay_clean_inputs($crypt);

    $crypt_str = join('&',$crypt);

    $form_fields = array(
        'VPSProtocol' => '3.00',
        'Vendor' => $pp_merch,
        'TxType' => 'PAYMENT',
        'Crypt' => XCSagepayUtil::encryptAes($crypt_str, $pp_pass)
    );

    if (defined('XC_SAGEPAY_FORM_DEBUG')) {
        func_pp_debug_log('sagepay-frm', 'I', $form_fields);
    }

    func_create_payment_form(
        $pp_test,
        $form_fields,
        'Sage Pay'
    );
}
exit;

?>
