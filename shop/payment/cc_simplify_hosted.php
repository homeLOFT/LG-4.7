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
 * "Simplify" payment module (credit card processor)
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Payment interface
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    4a6ada25f2bf53f6ef718453ff0d967978529ff3, v5 (xcart_4_7_0), 2015-02-26 16:00:26, cc_simplify_hosted.php, mixon
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if (
    $_SERVER['REQUEST_METHOD'] == 'GET'
    && !empty($_GET['signature']) && !empty($_GET['reference'])
) {

    require __DIR__.'/auth.php';

    //
    // Form data is accepted by Simplify and results are returned with $signature
    //
    if (empty($active_modules['Simplify'])
        || !func_is_active_payment(XC_SIMPLIFY_CC_HOSTED)) {
        exit;
    }

    x_load('payment');

    x_session_register('cart');
    x_session_register('secure_oid');

    $module_params = func_simplify_get_pm_params(XC_SIMPLIFY_CC_HOSTED);

    if (!empty($module_params)) {
        // Gateway is configured and has required params

        if (defined('XC_SIMPLIFY_HOSTED_DEBUG') || defined('DEVELOPMENT_MODE')) {

            $response = array(
                'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'],
                'GET' => $_GET,
            );

            func_pp_debug_log('simplify_hosted', 'R', $response);
        }

        $simplify_private_key = $module_params[XC_Simplify_Key_Type::PRIVATE_KEY];

        $simplify_order_reference = $reference;

        // restore order session
        $bill_output['sessid'] = func_query_first_cell("SELECT sessid FROM $sql_tbl[cc_pp3_data] WHERE ref = '" . $simplify_order_reference . "'");

        $payment_return['total'] = $amount / 100; // to check order totals

        // check request signature
        $result = func_simplify_hosted_check_signature($_GET, $simplify_private_key);

        if ($result) {
            switch ($paymentStatus) {
                case XC_Simplify_Payment_Status::APPROVED:
                    $bill_output['code'] = 1;
                    $bill_output['billmes'] = "(paymentId: $paymentId, authCode: $authCode)\n";
                    break;
                case XC_Simplify_Payment_Status::DECLINED:
                    $bill_output['code'] = 2;
                    break;
            }
        } else{
            $bill_output['code'] = 2;
            $bill_output['billmes'] = 'Signature check failed';
        }

    }

    require $xcart_dir.'/payment/payment_ccend.php';

} else {

    if (!defined('XCART_START')) { header("Location: ../"); die("Access denied"); }

    $module_params = func_simplify_get_pm_params(XC_SIMPLIFY_CC_HOSTED);

    $order_reference = implode('-', $secure_oid);

    $simplify_public_key = $module_params[XC_Simplify_Key_Type::PUBLIC_KEY];

    $simplify_order_reference = $config[XC_SIMPLIFY]['simplify_order_prefix'] . $order_reference;
    $simplify_order_total = $cart['total_cost'] * 100;

    $simplify_order_name = substr('Order #' . $order_reference, 0, 255);
    $simplify_order_description = substr($config['Company']['company_name'], 0, 255);

    $simplify_return_url = $current_location . '/payment/' . $module_params['processor'];

    $fields = array (
        'sc-key' => $simplify_public_key,
        'name' => $simplify_order_name,
        'description' => $simplify_order_description,
        'reference' => $simplify_order_reference,
        'amount' => $simplify_order_total,
        'redirect-url' => $simplify_return_url,
    );

    $fields['customer-email'] = substr($userinfo['email'], 0, 50);

    $fields['customer-name'] = substr($userinfo['b_firstname'] . ' ' . $userinfo['b_lastname'], 0, 50);
    if (strlen($fields['customer-name']) < 2) {
        unset($fields['customer-name']);
    }

    $fields['address'] = substr($userinfo['b_address'], 0, 50);
    if (strlen($fields['address']) < 2) {
        unset($fields['address']);
    }

    $fields['address-city'] = substr($userinfo['b_city'], 0, 50);
    if (strlen($fields['address-city']) < 2) {
        unset($fields['address-city']);
    }

    $fields['address-state'] = substr($userinfo['b_state'], 0, 2);
    if (strlen($fields['address-state']) < 2) {
        unset($fields['address-state']);
    }

    $fields['address-zip'] = substr($userinfo['b_zipcode'], 0, 9);
    if (strlen($fields['address-zip']) < 3) {
        unset($fields['address-zip']);
    }

    $fields['address-country'] = substr($userinfo['b_country'], 0, 2);
    if (strlen($fields['address-country']) < 2) {
        unset($fields['address-country']);
    }

    $smarty->assign('fields', $fields);
    $smarty->assign('payment', $module_params['module_name']);

    if (!$duplicate) {
        db_query("REPLACE INTO $sql_tbl[cc_pp3_data] (ref,sessid) VALUES ('" . $simplify_order_reference . "','" . $XCARTSESSID . "')");
    }

    if (defined('XC_SIMPLIFY_HOSTED_DEBUG') || defined('DEVELOPMENT_MODE')) {
        func_pp_debug_log('simplify_hosted', 'I', $fields);
    }

    func_flush(func_display('modules/Simplify/hosted_form.tpl', $smarty, false));

    exit;
}

?>
