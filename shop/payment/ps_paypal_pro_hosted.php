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
 * PayPal Payments Pro Hosted
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Payment interface
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    3968cba5ecdb78320d43cbe05a25fe35597bc800, v7 (xcart_4_7_0), 2015-02-17 13:29:01, ps_paypal_pro_hosted.php, aim
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if (
    isset($_GET['mode'])
    && ($_GET['mode'] == 'success')
    && isset($_GET['secureid'])
) {

    require_once __DIR__.'/auth.php';

    $ccprocessor = 'ps_paypal_pro_hosted.php';

    x_load('payment');

    if (!func_is_active_payment($ccprocessor)) {
        exit;
    }

    if (defined('PAYPAL_DEBUG')) {
        x_log_add('paypal', "*** Callback: \nGET:\n" . print_r($_GET, true) . "\nPOST:\n" . print_r($_POST, true));
    }

    $extra_order_data = array(
    );

    if (isset($_GET['tx'])) {

        // Paid via Credit Card

        x_load('paypal');

        $module_params = func_get_pm_params($ccprocessor);

        $skey = $_GET['secureid'];
        $bill_output['sessid'] = func_query_first_cell("SELECT sessid FROM $sql_tbl[cc_pp3_data] WHERE ref='$skey'");
        $txn_id = $_GET['tx'];

        $res = func_paypal_get_status($module_params['paymentid'], $txn_id);

        if (!empty($res)) {

            $bill_output['billmes'] = $res['paymentstatus'];

            if (strcasecmp($res['paymentstatus'], 'Completed') == 0) {
                $bill_output['code'] = 1;
            } else if (strcasecmp($res['paymentstatus'], 'Pending') == 0) {
                $bill_output['code'] = 3;
            } else {
                $bill_output['code'] = 2;
                $bill_output['is_error'] = true;
            }

        } else {
            $bill_output['code'] = 2;
            $bill_output['is_error'] = true;
        }

        $bill_output['is_preauth'] = ($module_params['use_preauth'] == 'Y');

        $extra_order_data['paypal_type'] = 'PH';

    } elseif (
        $_SERVER['REQUEST_METHOD'] == 'POST'
        && isset($_POST['payment_type'])
    ) {

        // Paid via PayPal - parse order status using IPN script

        $ipn_skip_payment_ccmid = true;
        $notify_from = 'pro_hosted';
        require $xcart_dir . '/payment/ps_paypal_ipn.php';

        $extra_order_data['paypal_type'] = 'PHEC';

    }

    if (!empty($txn_id)) {

        $extra_order_data['paypal_txnid'] = $txn_id;
        $extra_order_data['capture_status'] = (!empty($bill_output['is_preauth'])) ? 'A' : '';

    } else {

        $bill_output['code'] = 2;
        $bill_output['is_error'] = true;
        $bill_output['billmes'] = 'Can\'t resolve Transaction ID';

    }

    require $xcart_dir . '/payment/payment_ccend.php';

} elseif (
    isset($_GET['mode'])
    && ($_GET['mode'] == 'cancel' || $_GET['mode'] == 'cancel_ec')
    && isset($_GET['secureid'])
) {

    require_once __DIR__.'/auth.php';

    $ccprocessor = 'ps_paypal_pro_hosted.php';

    if (!func_is_active_payment($ccprocessor)) {
        exit;
    }

    if (defined('PAYPAL_DEBUG')) {
        x_log_add('paypal', "*** Cancel request:\n", print_r($_GET,true));
    }

    $skey = $_GET['secureid'];

    $bill_output['code'] = 2;
    $bill_output['billmes'] = 'Canceled by the user';
    $bill_output['sessid'] = func_query_first_cell("SELECT sessid FROM $sql_tbl[cc_pp3_data] WHERE ref='" . $skey . "'");

    if ($_GET['mode'] == 'cancel') {
        $is_iframe_canceled = true;
    }

    require $xcart_dir . '/payment/payment_ccend.php';

} else {

    if (!defined('XCART_START')) {
        header('Location: ../');
        die('Access denied');
    }

    $ccprocessor = 'ps_paypal_pro_hosted.php';

    x_load('http', 'payment', 'paypal');

    $module_params = func_get_pm_params($ccprocessor);

    $oid = $module_params['params']['order_prefix'] . join('-', $secure_oid);

    $pp_currency = func_paypal_get_currency($module_params);
    $pp_total = func_paypal_convert_to_BasicAmountType($cart['total_cost'], $pp_currency);

    $variables = array(
        'template' => 'templateD',
        'paymentaction' => $module_params['use_preauth'] == 'Y' ? 'authorization' : 'sale',
        'currency_code' => $pp_currency,
        'bn' => 'XCart_Cart_HostedPro',
        'cbt' => func_get_langvar_by_name('lbl_return_to_x', array('x' => $config['Company']['company_name']), false, true),
        'invoice' => $oid,
        'custom' => $order_secureid,
        'return'  => $current_location . '/payment/ps_paypal_pro_hosted.php?mode=success&secureid=' . $order_secureid,
        'cancel_return' => $current_location . '/payment/ps_paypal_pro_hosted.php?mode=cancel_ec&secureid=' . $order_secureid,
        'notify_url' => $current_location . '/payment/ps_paypal_ipn.php?notify_from=pro_hosted',
        'showBillingAddress' => 'false',
        'showShippingAddress' => 'true',
        'showHostedThankyouPage' => 'false',
    );


    $totals = func_paypal_is_line_items_allowed($cart, $pp_total);

    if (!empty($totals)) {
        $variables['subtotal'] = $totals['ItemTotal'];
        $variables['shipping'] = $totals['ShippingTotal'];
        $variables['tax'] = $totals['TaxTotal'];
        $variables['handling'] = $totals['HandlingTotal'];
    } else {
        $variables['subtotal'] = $pp_total;
    }

    if (!empty($module_params['params']['payflow_vendor'])) {
        $variables['vendor'] = $module_params['params']['payflow_vendor'];
        $variables['partner'] = $module_params['params']['payflow_partner'];
    }

    if (!empty($userinfo) && is_array($userinfo)) {

        $variables['address_override'] = 'true';

        $variables['buyer_email'] = $userinfo['email'];

        $phone = preg_replace('/[^0-9]/','', ($userinfo['s_phone']) ? $userinfo['s_phone'] : $userinfo['b_phone']); 
        $variables['night_phone_b'] = $phone;

        foreach (array('s_', 'b_') as $type) {
            $prefix = ($type == 'b_') ? 'billing_' : '';
            $firstname = !empty($userinfo[$type . 'firstname']) ? $userinfo[$type . 'firstname'] : $userinfo['firstname'];
            $lastname = !empty($userinfo[$type . 'lastname']) ? $userinfo[$type . 'lastname'] : $userinfo['lastname'];

            $variables[$prefix . 'first_name']    = $firstname;
            $variables[$prefix . 'last_name']     = $lastname;

            $variables[$prefix . 'address1']      = $userinfo[$type . 'address'];
            if (!empty($userinfo[$type . 'address_2'])) {
                $variables[$prefix . 'address_2'] = $userinfo[$type . 'address_2'];
            }
            $variables[$prefix . 'city']          = $userinfo[$type . 'city'];
            $variables[$prefix . 'country']       = $userinfo[$type . 'country'];
            $variables[$prefix . 'state']         = func_paypal_get_state($userinfo, $type);
            $variables[$prefix . 'zip']           = $userinfo[$type . 'zipcode'];

            if ($config['General']['zip4_support'] == 'Y' && $variables[$prefix . 'country'] == 'us' && !empty($userinfo[$type . 'zip4'])) {
                $variables[$prefix . 'zip']      .= '-' . $userinfo[$type . 'zip4'];
            }

        }
    }

    $button_params = array(
        'BUTTONCODE' => 'TOKEN',
        'BUTTONTYPE' => 'PAYMENT',
    );

    $res = func_paypal_bmcreatebutton($module_params['paymentid'], $button_params, $variables);

    if (!empty($res) && $res['ack'] == 'Success') {

        $iframe_src = $res['emaillink'];

        db_query("REPLACE INTO $sql_tbl[cc_pp3_data] (ref, sessid, trstat) VALUES ('" . addslashes($order_secureid) . "','$XCARTSESSID','GO|" . implode('|', $secure_oid) . "')");

        $smarty->assign('iframe_src', $iframe_src);
        $smarty->assign('cancel_url', $current_location .'/payment/ps_paypal_pro_hosted.php?mode=cancel&secureid=' . $order_secureid);

        func_flush(func_display('payments/ps_paypal_pro_hosted_iframe.tpl', $smarty, false));

        exit;

    } else {

        $bill_output['code'] = 2;
        $bill_output['billmes'] = '(' . $res['l_errorcode0'] . ') ' . $res['l_longmessage0'];
        $is_iframe = true;

        require $xcart_dir . '/payment/payment_ccend.php';

    }

}

?>
