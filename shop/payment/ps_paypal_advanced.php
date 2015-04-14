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
 * PayPal Advanced
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Payment interface
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    3968cba5ecdb78320d43cbe05a25fe35597bc800, v36 (xcart_4_7_0), 2015-02-17 13:29:01, ps_paypal_advanced.php, aim
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !defined('XCART_START')) {
    require __DIR__.'/auth.php';

    if (empty($ccprocessor)) {
        $ccprocessor = basename($_SERVER['SCRIPT_FILENAME']);
    }

    if (!func_is_active_payment($ccprocessor))
        exit;

    x_load('payment');
    func_pm_load('ps_paypal_advanced');

    $module_params = func_get_pm_params($ccprocessor);

    if (isset($_POST['SECURETOKENID'])) {
        $bill_output['sessid'] = func_query_first_cell("SELECT sessid FROM $sql_tbl[cc_pp3_data] WHERE ref='" . $SECURETOKENID . "'");
    }

    if (defined('PAYPAL_DEBUG')) {
        func_pp_debug_log('paypal_advanced', 'callback', print_r($_POST, true));
    }

    if (isset($RESULT)) {

        if (isset($_POST['SECURETOKENID'])) {

            $post = array(
                'TRXTYPE' => 'I',
                'SECURETOKEN'   => $SECURETOKEN,
                'SECURETOKENID' => $SECURETOKENID,
            );

            $ret = func_payflow_call($post, $module_params);

            $bill_output['code'] = $ret['ORIGRESULT'] === '0' ? 1 : 2;
            $bill_output['billmes'] = '(' . $ret['RESULT'] . ') ' . $ret['RESPMSG'];
            if ($ret['ORIGRESULT'] != $RESULT) {
                $bill_output['billmes'] .= '; Response: (' . $RESULT . ') ' . $RESPMSG;
            }
            $extra_order_data = array(
                'ppref' => $ret['ORIGPPREF'],
                'pnref' => $ret['ORIGPNREF'],
                'paypal_type' => $ccprocessor == 'ps_paypal_advanced.php' ? 'ADPP' : 'PFPP',
                'capture_status' => $module_params['use_preauth'] == 'Y' ? 'A' : '',
                'SECURETOKEN' => $SECURETOKEN,
                'SECURETOKENID' => $SECURETOKENID,
            );

            if ($module_params['use_preauth'] == 'Y') {
                $bill_output['is_preauth'] = true;
                $extra_order_data['auth_pnref'] = $ret['PNREF'];
                #$extra_order_data['fmf'] = 1;
            }

            if ($ret['ORIGRESULT'] === '126') {
                $extra_order_data['paypal_fraud_info'] = 'Y';
            }
        } else {

            $bill_output['code'] = 2;
            $bill_output['billmes'] = '(' . $RESULT . ') ' . $RESPMSG;

        }

    } else {
        
        $bill_output['code'] = 2;
        $bill_output['billmes'] = 'Canceled by user';
    }

    $is_iframe = true;
    require($xcart_dir . '/payment/payment_ccend.php');
} elseif (
    isset($_GET['mode'])
    && $_GET['mode'] == 'cancel'
) {

    require __DIR__.'/auth.php';

    if (empty($ccprocessor)) {
        $ccprocessor = basename($_SERVER['SCRIPT_FILENAME']);
    }

    if (!func_is_active_payment($ccprocessor))
        exit;

    if (defined('PAYPAL_DEBUG')) {
        func_pp_debug_log('paypal_advanced', 'cancel_request', print_r($_GET,true));
    }

    $bill_output['code'] = 2;
    $bill_output['billmes'] = 'Canceled by the user';
    $bill_output['sessid'] = func_query_first_cell("SELECT sessid FROM $sql_tbl[cc_pp3_data] WHERE ref='" . addslashes($_GET['secureid']) . "'");

    $is_iframe_canceled = true;

    require $xcart_dir . '/payment/payment_ccend.php';

} else {

    if (!defined('XCART_START')) {
        header('Location: ../');
        die('Access denied');
    }

    x_load('http', 'payment', 'paypal');
    func_pm_load('ps_paypal_advanced');

    $oid = $module_params['param06'] . join('-', $secure_oid);

    if (empty($ccprocessor)) {
        $ccprocessor = 'ps_paypal_advanced.php';
    }

    $pp_currency = func_paypal_get_currency($module_params);
    $pp_total = func_paypal_convert_to_BasicAmountType($cart['total_cost'], $pp_currency);

    $post = array(
        'TRXTYPE'           => $module_params['use_preauth'] == 'Y' ? 'A' : 'S',
        'AMT'               => $pp_total,
        'INVNUM'            => $oid,
        'CURRENCY'          => $module_params['param03'],
        'CREATESECURETOKEN' => 'Y',
        'SECURETOKENID'     => $oid,
        'DISABLERECEIPT'    => 'TRUE',
        'RETURNURL'         => $current_location . '/payment/' . $ccprocessor,
        'CANCELURL'         => $current_location . '/payment/' . $ccprocessor,
        'ERRORURL'          => $current_location . '/payment/' . $ccprocessor,
        'URLMETHOD'         => 'POST',
        'TEMPLATE'          => 'MINLAYOUT',
        'ADDROVERRIDE'      => '1',

        'PAYFLOWCOLOR'      => (!empty($module_params['params']['payflowcolor'])) ? $module_params['params']['payflowcolor'] : '',
        'HDRIMG'            => (!empty($module_params['params']['hdrimg'])) ? $module_params['params']['hdrimg'] : '',

        'EMAIL' => $userinfo['email'],
    );

    $profile = func_paypal_get_userinfo_payflow($userinfo, array('B', 'S'), TRUE);

    if (!empty($profile)) {
        $post = array_merge($post, $profile);
    }

    $optional_params = array(
        'pagecollapsebgcolor',
        'pagecollapsetextcolor',
        'pagebuttonbgcolor',
        'pagebuttontextcolor',
        'buttontext',
        'pagestyle',
    );

    foreach ($optional_params as $optional_param) {
        if (!empty($module_params['params'][$optional_param])) {
            $post[strtoupper($optional_param)] = $module_params['params'][$optional_param];
        }
    }

    $line_items = func_paypal_get_line_items_payflow($cart, $pp_total, $pp_currency);

    if (!empty($line_items)) {

        $post = array_merge($post, $line_items);

    } else {
        // Or just use whole order as LineItem

        $post['L_NAME0'] = 'Order #' . join(', #', $secure_oid);
        $post['L_QTY0'] = 1;
        $post['L_COST0'] = $pp_total;
        $post['ITEMAMT'] = $pp_total;

    }

    $ret = func_payflow_call($post, $module_params);

    if ($ret['RESULT'] == '0') {

        if (!$duplicate)
            db_query("REPLACE INTO $sql_tbl[cc_pp3_data] (ref,sessid) VALUES ('" . addslashes($oid) . "','" . $XCARTSESSID . "')");

        $params = array(
            'SECURETOKEN' => $ret['SECURETOKEN'],
            'SECURETOKENID' => $ret['SECURETOKENID'],
            'MODE' => ($module_params['testmode'] == 'Y' ? 'TEST' : ''),

        );

        $iframe_src = 'https://payflowlink.paypal.com/?' . func_http_build_query($params);

        if (defined('PAYPAL_DEBUG')) {
            func_pp_debug_log('paypal_advanced', 'iframe', $iframe_src);
        }

        $smarty->assign('iframe_src', $iframe_src);
        $smarty->assign('cancel_url', $current_location .'/payment/' . $ccprocessor. '?mode=cancel&secureid=' . $ret['SECURETOKENID']);

        func_flush(func_display('payments/ps_paypal_advanced_iframe.tpl', $smarty, false));
    
        exit;

    } else {

        $bill_output['code'] = 2;
        $bill_output['billmes'] = '(' . $ret['RESULT'] . ') ' . $ret['RESPMSG'];
        $is_iframe = true;

        require $xcart_dir . '/payment/payment_ccend.php';

    }

}

?>
