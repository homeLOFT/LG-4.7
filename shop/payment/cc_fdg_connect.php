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
 * First Data Global Gateway Connect
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Payment interface
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    3968cba5ecdb78320d43cbe05a25fe35597bc800, v32 (xcart_4_7_0), 2015-02-17 13:29:01, cc_fdg_connect.php, aim
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if (!isset($REQUEST_METHOD)) {
    $REQUEST_METHOD = $_SERVER['REQUEST_METHOD'];
}

if ($REQUEST_METHOD == 'POST' && (isset($_POST['status']) || isset($_POST['oid']))) {

    require __DIR__.'/auth.php';

    x_load('payment');
    func_pm_load('cc_fdg_connect');

    x_session_register('secure_oid');

    $transaction_data = func_query_first("SELECT sessid, param1 FROM $sql_tbl[cc_pp3_data] WHERE ref='$oid'");
    $bill_output['sessid'] = $transaction_data['sessid'];
    $fdg_check_str = $transaction_data['param1'];

    $module_params = func_get_pm_params('cc_fdg_connect.php');

    $fdg_region = $module_params['param01'];

    if ($fdg_region == 'EMEA') {

        $signature = func_cc_fdg_encrypt(str_replace(FDG_CONNECT_APPROVAL_TOKEN, $approval_code, $fdg_check_str), $fdg_region);

        if (strcmp($signature, $response_hash) != 0) {
            $status = 'DECLINED';
            $fail_reason = 'Signature check failed';
        }

    }

    if ($status != 'APPROVED') {

        $bill_output['code'] = 2;
        $bill_output['billmes'] = $status . ': ' . $fail_reason;

    } else {

        $bill_output['code'] = ($approval_code) ? 1 : 2;

        if ($bill_output['code'] == 1) {

            $is_preauth = ($module_params['use_preauth'] == 'Y' || func_is_preauth_force_enabled($secure_oid));

            if ($is_preauth) {
                $bill_output['is_preauth'] = true;
                $extra_order_data['capture_status'] = 'A';
            }

            $bill_output['billmes'] .= func_cc_fdg_parse_response($approval_code);

            if (
                $response_code_3dsecure
                && isset($fdg_sec3dcodes[$response_code_3dsecure])
            ) {
                $bill_output['billmes'] .= "\n3D secure check: " . $fdg_sec3dcodes[$response_code_3dsecure];
            }
        }

    }

    require $xcart_dir . '/payment/payment_ccend.php';

} else {

    if (!defined('XCART_START')) {
        header('Location: ../');
        die('Access denied');
    }

    $fdg_region         = $module_params['param01'];
    $fdg_currency       = $module_params['param02'];
    $fdg_storename      = $module_params['param03'];
    $fdg_mode           = $module_params['param04'];
    $fdg_prefix         = $module_params['param05'];
    $fdg_secret_key     = $module_params['param06'];
    $fdg_timezome       = $module_params['param07'];

    $is_preauth         = ($module_params['use_preauth'] == 'Y' || func_is_preauth_force_enabled($secure_oid));

    $fdg_total_cost     = number_format($cart['total_cost'], 2, '.', '');
    $trantype           = ($is_preauth) ? 'preauth' : 'sale';
    $fdg_oid            = fdg_trim($fdg_prefix . join('-', $secure_oid), 100);
    $fdg_subtotal       = number_format($cart['total_cost'] - $cart['shipping_cost'] - $cart['tax_cost'], 2, '.', '');
    $fdg_response_url   = $current_location . '/payment/' . $module_params['processor'];
    $fdg_trim_limit     = ($fdg_region == 'EMEA') ? 30 : 96;
    $fdg_user_name      = fdg_trim($userinfo['firstname'] . ' ' . $userinfo['lastname'], $fdg_trim_limit);

    $post = array(

        'storename'         => $fdg_storename,
        'txntype'           => $trantype,
        'oid'               => $fdg_oid,
        'chargetotal'       => $fdg_total_cost,
        'mode'              => $fdg_mode,
        'subtotal'          => $fdg_subtotal,
        'shipping'          => number_format($cart['shipping_cost'], 2, '.', ''),
        'tax'               => number_format($cart['tax_cost'], 2, '.', ''),
        'comments'          => fdg_trim($customer_notes, 1024),

        'bcompany'          => fdg_trim($userinfo['company'], $fdg_trim_limit),
        'bname'             => $fdg_user_name,
        'baddr1'            => fdg_trim($userinfo['b_address'], $fdg_trim_limit),
        'baddr2'            => fdg_trim(@$userinfo['b_address_2'], $fdg_trim_limit),
        'bcity'             => fdg_trim($userinfo['b_city'], $fdg_trim_limit),
        'bcountry'          => $userinfo['b_country'],
        'bzip'              => $userinfo['b_zipcode'],

        'sname'             => $fdg_user_name,
        'saddr1'            => fdg_trim($userinfo['s_address'], $fdg_trim_limit),
        'saddr2'            => fdg_trim(@$userinfo['s_address_2'], $fdg_trim_limit),
        'scity'             => fdg_trim($userinfo['s_city'], $fdg_trim_limit),
        'scountry'          => $userinfo['s_country'],
        'szip'              => $userinfo['s_zipcode'],

        'phone'             => fdg_trim($userinfo['phone'], 20),
        'fax'               => fdg_trim($userinfo['fax'], 20),
        'email'             => fdg_trim($userinfo['email'], 45),
        'userid'            => fdg_trim($userinfo['login'], 30),
        'customerid'        => fdg_trim($userinfo['login'], 30),

    );

    if (empty($fdg_timezome)) {
        $fdg_timezome = 'GMT';
    }
    date_default_timezone_set($fdg_timezome);
    $fdg_date      = date('Y:m:d-H:i:s');
    $fdg_hash_str  = $fdg_storename . $fdg_date . $fdg_total_cost . $fdg_currency . $fdg_secret_key;
    $fdg_check_str = $fdg_secret_key . FDG_CONNECT_APPROVAL_TOKEN . $fdg_total_cost . $fdg_currency . $fdg_date . $fdg_storename;
    $fdg_3dsecure  = $module_params['param08'];

    $post += array(
        'currency'    => $fdg_currency,
        'hash'        => func_cc_fdg_encrypt($fdg_hash_str, $fdg_region),
        'timezone'    => $fdg_timezome,
        'txndatetime' => $fdg_date,
        'authenticateTransaction' => ($fdg_3dsecure == 'Y') ? 'true' : 'false',
    );

    foreach (array('b', 's') as $state_type) {
        if ($userinfo[$state_type . '_country'] == 'US') {
            $post[$state_type . 'state'] = $userinfo[$state_type . '_state'];
        } else {
            $fdg_state = func_get_state($userinfo[$state_type . '_state'], $userinfo[$state_type . '_country']);
            $post[$state_type . 'state2'] = ($fdg_state) ? fdg_trim($fdg_state, 30) : $userinfo[$state_type . '_state'];
        }
    }

    $post['responseURL'] =
    $post['responseFailURL'] =
    $post['responseSuccessURL'] = $fdg_response_url;

    if (!$duplicate) {
        db_query("REPLACE INTO $sql_tbl[cc_pp3_data] (ref, sessid, param1) VALUES ('" . addslashes($fdg_oid) . "', '" . $XCARTSESSID . "', '" . addslashes($fdg_check_str) . "')");
    }

    $fdg_url = func_cc_fdg_get_gateway_url();

    func_create_payment_form($fdg_url, $post, 'First Data Global Gateway', 'POST');
    exit();

}

?>
