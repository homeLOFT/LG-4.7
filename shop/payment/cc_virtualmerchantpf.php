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
 * Virtual Merchant (Provided Form)
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Payment interface
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    3968cba5ecdb78320d43cbe05a25fe35597bc800, v34 (xcart_4_7_0), 2015-02-17 13:29:01, cc_virtualmerchantpf.php, aim
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if ($_SERVER['REQUEST_METHOD'] == "GET" && (isset($_GET['errorCode']) || isset($_GET['ssl_result']))) {
    require __DIR__.'/auth.php';

    if (defined('VIRTUALMERCHANT_DEBUG')) {
        func_pp_debug_log('virtualmerchantpf', 'R', $_GET);
    }

    if (!func_is_active_payment('cc_virtualmerchantpf.php'))
        exit;

    $module_params = func_get_pm_params('cc_virtualmerchantpf.php');
    if (empty($ssl_invoice_number)) {
        // Use current session_id if ssl_invoice_number was not passed in the response
        $bill_output['sessid'] = $XCARTSESSID;
    } else {
        $bill_output['sessid'] = func_query_first_cell("SELECT sessid FROM $sql_tbl[cc_pp3_data] WHERE ref='" . $ssl_invoice_number . "'");
    }

    $avserr = array(
        'A' => "Address (Street) matches, ZIP does not",
        'B' => "Street address match, Postal code in wrong format. (International issuer)",
        'C' => "Street address and postal code in wrong formats",
        'D' => "Street address and postal code match (international issuer)",
        'E' => "AVS error",
        'G' => "Service not supported by non-US issuer",
        'I' => "Address information not verified by international issuer.",
        'M' => "Street Address and Postal code match (international issuer)",
        'N' => "No Match on Address (Street) or ZIP",
        'O' => "No Response sent",
        'P' => "AVS not applicable for this transaction",
        'R' => "Retry. System unavailable or timed out",
        'S' => "Service not supported by issuer",
        'U' => "Address information is unavailable",
        'W' => "9 digit ZIP matches, Address (Street) does not match",
        'X' => "Exact AVS Match",
        'Y' => "Address (Street) and 5 digit ZIP match",
        'Z' => "5 digit ZIP matches, Address (Street) does not match"
    );

    $cvverr = array(
        'M' => "CVV2 Match",
        'N' => "CVV2 No Match",
        'P' => "Not Processed",
        'S' => "Issuer indicates that CVV2 data should be present on the card, but the merchant has indicated that the CVV2 data is not resent on the card",
        'U' => "Issuer has not certified for CVV2 or Issuer has not provided Visa with the CVV2 encryption Keys"
    );

    if (isset($errorCode)) {
        $bill_output['code'] = 2;
        $bill_output['billmes'] = $errorName . ': ' . $errorMsg . ' (error code: ' . $errorCode . ')';

    } else {

        x_session_register('secure_oid');

        $bill_output['code'] = $ssl_result == 0 ? 1 : 2;
        if (isset($ssl_result_message))
            $bill_output['billmes'] = $ssl_result_message;

        if ($bill_output['code'] == 1 && ($module_params['use_preauth'] == 'Y' || func_is_preauth_force_enabled($secure_oid)))
            $bill_output['is_preauth'] = true;

        if (isset($ssl_txn_id)) {
            $extra_order_data = array(
                'txnid' => $ssl_txn_id,
                'approval_code' => $ssl_approval_code,
                'capture_status' => $bill_output['is_preauth'] ? 'A' : ''
            );

            $bill_output['billmes'] .= ' (Transaction ID: ' . $ssl_txn_id . '; Approval code: ' . $ssl_approval_code . ')';
        }

        if ($ssl_avs_response)
            $bill_output['avsmes'] = 'AVS Code: ' . $avserr[$ssl_avs_response] . ' (' . $ssl_avs_response . ')';

        if ($ssl_cvv2_response)
            $bill_output['cvvmes'] = 'CVV Code: ' . $cvverr[$ssl_cvv2_response] . ' (' . $ssl_cvv2_response . ')';

    }

    require($xcart_dir.'/payment/payment_ccend.php');

} else {

    if (!defined('XCART_START')) { header("Location: ../"); die("Access denied"); }

    $ordr = $module_params['param04'] . join("-", $secure_oid);
    if (!$duplicate)
        db_query("REPLACE INTO $sql_tbl[cc_pp3_data] (ref,sessid) VALUES ('".addslashes($ordr)."','".$XCARTSESSID."')");

    $fields = array(
        'ssl_invoice_number' => $ordr,
        'ssl_merchant_id' => substr($module_params['param01'], 0, 15),
        'ssl_user_id' => substr($module_params['param07'], 0, 15),
        'ssl_pin' => substr($module_params['param02'], 0, 6),
           'ssl_customer_code' => '1111',
           'ssl_salestax' => $cart['tax_cost'],
        'ssl_description' => substr($module_params['param04'] . join("-", $secure_oid), 0, 255),
        'ssl_amount' => price_format($cart['total_cost']),
        'ssl_transaction_type' => ($module_params['use_preauth'] == 'Y' || func_is_preauth_force_enabled($secure_oid)) ? 'CCAUTHONLY' : 'CCSALE',

        'ssl_company' => substr($userinfo['company'], 0, 50),
        'ssl_first_name' => substr($bill_firstname, 0, 15),
        'ssl_last_name' => substr($bill_lastname, 0, 30),
        'ssl_address1' => substr($userinfo['b_address'], 0, 30),
        'ssl_address2' => substr($userinfo['b_address_2'], 0, 30),
        'ssl_city' => substr($userinfo['b_city'], 0, 30),
        'ssl_state' => substr(($userinfo['b_state'] ? $userinfo['b_state'] : 'n/a'), 0, 10),
        'ssl_zip' => substr($userinfo['b_zipcode'], 0, 10),
        'ssl_country' => func_get_3letters_country_code($userinfo['b_country']),
        'ssl_phone' => substr($userinfo['phone'], 0, 20),
        'ssl_email' => substr($userinfo['email'], 0, 100),
        'ssl_show_form' => 'TRUE',

        'ssl_ship_to_company' => substr($userinfo['company'], 0, 50),
        'ssl_ship_to_first_name' => substr($userinfo['s_firstname'], 0, 20),
        'ssl_ship_to_last_name' => substr($userinfo['s_lastname'], 0, 30),
        'ssl_ship_to_address' => substr($userinfo['s_address'], 0, 30),
        'ssl_ship_to_city' => substr($userinfo['s_city'], 0, 30),
        'ssl_ship_to_state' => substr(($userinfo['s_state'] ? $userinfo['s_state'] : 'n/a'), 0, 10),
        'ssl_ship_to_country' => substr($userinfo['s_country'], 0, 50),
        'ssl_ship_to_zip' => substr($userinfo['s_zipcode'], 0, 10),

        'ssl_result_format' => 'ASCII',
        'ssl_receipt_apprvl_method' => 'REDG',
        'ssl_receipt_apprvl_get_url' => $current_location . '/payment/cc_virtualmerchantpf.php',
        'ssl_receipt_decl_method' => 'REDG',
        'ssl_receipt_decl_get_url' => $current_location . '/payment/cc_virtualmerchantpf.php',
        'ssl_error_url' => $current_location . '/payment/cc_virtualmerchantpf.php'
    );

    if ($module_params['testmode'] != 'N')
        $fields['ssl_test_mode'] = 'TRUE';

    if ($module_params['param06'] == 'Y') {
        $fields['ssl_avs_address'] = substr($userinfo['b_address'], 0, 20);
        $fields['ssl_avs_zip'] = substr($userinfo['b_zipcode'], 0, 10);
    }

    if ($module_params['param05'] == 'Y') {
        $fields['ssl_cvv2cvc2_indicator'] = 'present';
    }

    if ($module_params['testmode'] == 'D') {
        $url = 'https://demo.myvirtualmerchant.com/VirtualMerchantDemo/process.do';
    } else {
        $url = 'https://www.myvirtualmerchant.com/VirtualMerchant/process.do';
    }

    if (defined('VIRTUALMERCHANT_DEBUG')) {
        func_pp_debug_log('virtualmerchantpf', 'I', print_r($fields, true) . print_r($cc_fields, true));
    }

    func_create_payment_form($url, $fields, 'Virtual Merchant', 'post');
}
exit;

?>
