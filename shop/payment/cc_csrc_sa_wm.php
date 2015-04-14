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
 * "CyberSource - Secure Acceptance Web/Mobile" payment module (credit card processor)
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Payment interface
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    3968cba5ecdb78320d43cbe05a25fe35597bc800, v7 (xcart_4_7_0), 2015-02-17 13:29:01, cc_csrc_sa_wm.php, aim
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if (!isset($REQUEST_METHOD)) {
    $REQUEST_METHOD = $_SERVER['REQUEST_METHOD'];
}

define('XC_CSRC_SA_WM', 'cc_csrc_sa_wm.php');

if ($REQUEST_METHOD == 'POST' && !empty($_POST['signed_field_names'])) {

    require __DIR__.'/auth.php';

    if (!func_is_active_payment(XC_CSRC_SA_WM)) {
        exit;
    }

    x_load('payment');

    x_session_register('cart');
    x_session_register('secure_oid');

    func_pm_load(XC_CSRC_SA_WM);

    $module_params = func_get_pm_params(XC_CSRC_SA_WM);

    $cs_secret_key = $module_params['params']['secret_key'];

    $result = func_cc_csrc_sa_wm_verify_signature($_POST, $cs_secret_key);

    include $xcart_dir . '/payment/cc_csrc.resp_codes.php';

    if (defined('XC_CSRC_SA_WM_DEBUG')) {

        $response = array(
            'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'],
            'GET' => $_GET,
            'POST' => $_POST,
        );

        func_pp_debug_log('csrc_sa_wm', 'R', $response);
    }

    $bill_output['sessid'] = func_query_first_cell("SELECT sessid FROM $sql_tbl[cc_pp3_data] WHERE ref = '" . $req_transaction_uuid . "'");

    if ($result) {
        if (strcasecmp($mode, 'cancel') == 0
            || (
                strcasecmp($mode, 'transaction') == 0
                && strcasecmp($decision, 'ERROR') == 0
            )
        ) {
            $reason_code = $mode;
            $reason[$reason_code] = $message;
        }
        $bill_output['code'] = (strtoupper($decision) == 'ACCEPT') ? 1 : 2;
        $bill_output['billmes'] = $reason[$reason_code] . ' (code ' . $reason_code . ").\n";
    } else {
        $bill_output['code'] = 2;
        $bill_output['billmes'] = 'Signature check failed';
    }

    if ($auth_trans_ref_no) {
        $bill_output['billmes'] .= 'Transaction no: ' . $auth_trans_ref_no . "\n";
    }
    if ($req_reference_number) {
        $bill_output['billmes'] .= 'Order number: ' . $req_reference_number . "\n";
    }
    if ($auth_avs_code) {
        $bill_output['avsmes'] = $avserr[$auth_avs_code];
    }

    if ($bill_output['code'] == 1 && $req_transaction_type == 'authorization') {
        $bill_output['is_preauth'] = true;
    }

    $skey = $req_transaction_uuid;
    
    require $xcart_dir.'/payment/payment_ccend.php';

} else {

    if (!defined('XCART_START')) { header('Location: ../'); die('Access denied'); }

    $is_preauth = ($module_params['use_preauth'] == 'Y' || func_is_preauth_force_enabled($secure_oid));

    $cs_order_prefix            = $module_params['param04'];

    $cs_profile_id              = $module_params['param01'];
    $cs_access_key              = $module_params['params']['access_key'];
    $cs_secret_key              = $module_params['params']['secret_key'];

    $cs_transaction_uuid        = uniqid($cs_order_prefix.implode('-', $secure_oid) . ':', XC_TIME);
    $cs_transaction_type        = $is_preauth ? 'authorization' : 'sale';
    $cs_reference_number        = implode('-', $secure_oid);

    $cs_amount                  = $cart['total_cost'];
    $cs_currency                = $module_params['param03'];

    $cs_signed_date_time        = func_cc_csrc_sa_wm_get_timestamp();
    $cs_signed_field_names      = '';
    $cs_unsigned_field_names    = '';

    $cs_callback_url        = $current_location . '/payment/' . $module_params['processor'];
    $cs_return_url_success  = $current_location . DIR_CUSTOMER . '/cart.php?mode=order_message&orderids=' . implode(',', $secure_oid);
    $cs_return_url_decline  = $current_location . DIR_CUSTOMER . '/error_message.php?error=error_ccprocessor_error';
    $cs_return_link_text    = 'Return to ' . $config['Company']['company_name'];

    $csrc_sa_wm_url = 'https://'.(($module_params['testmode'] == 'Y') ? 'test' : '') . 'secureacceptance.cybersource.com/pay';

    if (!$duplicate) {
        db_query("REPLACE INTO $sql_tbl[cc_pp3_data] (ref, sessid, trstat) VALUES ('" . addslashes($cs_transaction_uuid) . "', '" . $XCARTSESSID . "', 'GO|" . implode('|', $secure_oid) . "')");
    }

    $post = array(
        'profile_id'            => $cs_profile_id,
        'access_key'            => $cs_access_key,

        'transaction_uuid'      => $cs_transaction_uuid,
        'transaction_type'      => $cs_transaction_type,
        'reference_number'      => $cs_reference_number,

        'bill_to_forename'      => substr($userinfo['b_firstname'], 0, 60),
        'bill_to_surname'       => substr($userinfo['b_lastname'], 0, 60),
        'bill_to_address_line1' => substr($userinfo['b_address'], 0, 60),
        'bill_to_address_city'  => substr($userinfo['b_city'], 0, 50),
        'bill_to_address_state' => substr((!empty($userinfo['b_state'])) ? $userinfo['b_state'] : 'N/A', 0, 60),
        'bill_to_address_postal_code'   => substr($userinfo['b_zipcode'], 0, 10),
        'bill_to_address_country'   => substr($userinfo['b_country'], 0, 2),
        'bill_to_phone'         => substr($userinfo['phone'], 0, 15),
        'bill_to_email'         => substr($userinfo['email'], 0, 255),

        'amount'                => $cs_amount,
        'currency'              => $cs_currency,

        'locale'                => 'en',

        'signed_date_time'      => $cs_signed_date_time,
        'signed_field_names'    => '',
        'unsigned_field_names'  => '',
    );

    ksort($post);

    $post['signed_field_names'] = implode(',', array_keys($post));

    $post['signature'] = func_cc_csrc_sa_wm_generate_signature($post, $cs_secret_key);

    if (defined('XC_CSRC_SA_WM_DEBUG')) {
        func_pp_debug_log('csrc_sa_wm', 'I', array('Post URL' => $csrc_sa_wm_url, 'data' => $post));
    }

    func_create_payment_form($csrc_sa_wm_url, $post, 'CyberSource');

    exit();

}

?>
