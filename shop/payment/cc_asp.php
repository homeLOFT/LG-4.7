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
 * "Amazon Simple Pay" payment module (credit card processor)
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Payment interface
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    3968cba5ecdb78320d43cbe05a25fe35597bc800, v29 (xcart_4_7_0), 2015-02-17 13:29:01, cc_asp.php, aim
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if (!isset($REQUEST_METHOD))
    $REQUEST_METHOD = $_SERVER['REQUEST_METHOD'];

if (
    $REQUEST_METHOD == 'GET' 
    && isset($_GET['referenceId']) 
    && isset($_GET['result'])
) {
    require __DIR__.'/auth.php';
    x_load('payment');
    func_pm_load('cc_asp');

    if (!func_is_active_payment('cc_asp.php'))
        exit;

    $module_params = func_get_pm_params('cc_asp.php');

    $pp_secretKey = $module_params['param02'];
    $use_preauth  = $module_params['use_preauth'];

    $response = $_GET;

    $pp_warning = '';

    # Verify signature
    $signature_result = func_cc_asp_verify_signature($response);
    if ($signature_result !== true)
        $pp_warning = $signature_result;

    $pp3_data = func_query_first("SELECT sessid,trstat FROM $sql_tbl[cc_pp3_data] WHERE ref='".$response['referenceId']."'");

    $bill_output['sessid'] = $pp3_data['sessid'];

    $bill_output['code'] = 2;

    if (
        $response['status'] == 'PS' 
        || $response['status'] == 'PR'
    ) {
        $bill_output['code'] = 1;

        $secure_oid = explode('|', $pp3_data['trstat']);

        if (
            $module_params['use_preauth'] == "Y" 
            || func_is_preauth_force_enabled($secure_oid)
        ) {

            $bill_output['is_preauth'] = true;

            $extra_order_data = array(
                'asp_txnid'      => $response['transactionId'],
                'capture_status' => 'A',
            );

        }

    }

    if (
        $response['status'] == 'PI' 
        || $pp_warning != ''
    ) {
        $bill_output['code'] = 3;
    }

    if ($bill_output['code'] != 1) {
        $bill_output['billmes'] = $response['errorMessage'] . $pp_warning;
    }

    require($xcart_dir . '/payment/payment_ccend.php');

} else {

    if (!defined('XCART_START')) { header("Location: ../"); die("Access denied"); }

    $pp_test = ($module_params['testmode']=='Y') 
        ? 'https://authorize.payments-sandbox.amazon.com/pba/paypipeline'
        : 'https://authorize.payments.amazon.com/pba/paypipeline';

    $pp_accessKey = $module_params['param01'];
    $pp_secretKey = $module_params['param02'];
    $pp_paymentsAccountId = $module_params['param03'];
    $pp_collectShippingAddress = ($module_params['param04'] == 'Y') ? '1' : '0';

    $ordr = $module_params['param05'] . join('-', $secure_oid);

    if(!$duplicate)
        db_query("REPLACE INTO $sql_tbl[cc_pp3_data] (ref,sessid,trstat) VALUES ('".addslashes($ordr)."','".$XCARTSESSID."','".implode('|',$secure_oid)."')");

    $return_url = $current_location . '/payment/cc_asp.php';

    $post = array (
        'amount'                    => 'USD ' . $cart['total_cost'],
        'description'               => $ordr,
        'referenceId'               => $ordr,
        'immediateReturn'           => '1',
        'returnUrl'                 => $return_url . '?result=success',
        'abandonUrl'                => $return_url . '?result=cancel',
        'accessKey'                 => $pp_accessKey,
        'processImmediate'          => ($module_params['use_preauth'] != 'Y' && !func_is_preauth_force_enabled($secure_oid)) ? '1' : '0',
        'collectShippingAddress'    => $pp_collectShippingAddress,
        'addressName'               => $userinfo['b_firstname'] . ' ' . $userinfo['b_lastname'],
        'addressLine1'              => $userinfo['b_address'],
        'addressLine2'              => $userinfo['b_address_2'],
        'city'                      => $userinfo['b_city'],
        'state'                     => $userinfo['b_state'],
        'zip'                       => $userinfo['b_zipcode'],
        'country'                   => $userinfo['b_country'],
        'phoneNumber'               => $userinfo['phone'],
        'signatureMethod'           => 'HmacSHA1',
        'signatureVersion'          => '2'
    );

    $post['signature'] = func_get_asp_signature($post, $pp_secretKey, $pp_test);

    func_create_payment_form($pp_test, $post, 'Amazon Simple Pay');
}

exit;
?>
