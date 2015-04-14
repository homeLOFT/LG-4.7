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
 * Functions for PayPal Advanced and Payflow Link payment methods
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Lib
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v24 (xcart_4_7_0), 2015-02-17 23:56:28, func.ps_paypal_advanced.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if (!defined('XCART_START')) { //{{{
    header('Location: ../../');
    die('Access denied');
} //}}}

x_load('http', 'payment');

/**
 * Returns array with variables from name-value pairs string
 */
function func_payflow_parse_str($str) { //{{{

    $workstr = $str;
    $out = array();

    while(strlen($workstr) > 0) {
        $loc = strpos($workstr, '=');
        if($loc === FALSE) {
            // Truncate the rest of the string, it's not valid
            $workstr = "";
            continue;
        }

        $substr = substr($workstr, 0, $loc);
        $workstr = substr($workstr, $loc + 1); // "+1" because we need to get rid of the "="

        if(preg_match('/^(\w+)\[(\d+)]$/', $substr, $matches)) {
            // This one has a length tag with it.  Read the number of characters

            // specified by $matches[2].
            $count = intval($matches[2]);

            $out[$matches[1]] = substr($workstr, 0, $count);
            $workstr = substr($workstr, $count + 1); // "+1" because we need to get rid of the "&"
        } else {
            // Read up to the next "&"
            $count = strpos($workstr, '&');
            if($count === FALSE) { // No more "&"'s, read up to the end of the string
                $out[$substr] = $workstr;
                $workstr = "";
            } else {
                $out[$substr] = substr($workstr, 0, $count);
                $workstr = substr($workstr, $count + 1); // "+1" because we need to get rid of the "&"
            }
        }
    }

    return $out;
} //}}}

function func_payflow_call($post, $module_params) { //{{{

    $post['VENDOR']       = $module_params['param01'];
    $post['PARTNER']      = $module_params['param02'];
    $post['USER']         = $module_params['param04'];
    $post['PWD']          = $module_params['param05'];
    $post['BUTTONSOURCE'] = 'Qualiteam_Cart_XCart_PHS';

    return func_payflow_call_raw($post, $module_params['testmode']);

} //}}}

function func_payflow_call_raw($post, $testmode) { //{{{

    $_post = array();

    if (!empty($post)){
        foreach($post as $index => $value) {
            $_post[] = $index . '[' . strlen($value) . ']=' . $value;
        }
    }

    $_post = implode('&', $_post);

    $url = 'https://' . ($testmode == 'Y' ? 'pilot-payflowpro.paypal.com' : 'payflowpro.paypal.com');

    list($a, $return) = func_https_request('POST', $url, $_post);

    $ret = func_payflow_parse_str($return);

    if (defined('PAYPAL_DEBUG')) {
        func_pp_debug_log('paypal_advanced', 'request', print_r($post, true) . "\n Testmode: $testmode");
        func_pp_debug_log('paypal_advanced', 'response', print_r($ret, true));
    }

    return $ret;
} //}}}

/**
 * Common function to perform different types of Advanced/PayFlow Link transactions
 */
 function func_ps_paypal_advanced_do($order, $transaction_type, $amount = 0) { //{{{

    global $xcart_dir, $module_params;

    if ($order['order']['paypal']['method'] == 'AD') {
        $module_params = func_get_pm_params('ps_paypal_advanced.php');
    } elseif ($order['order']['paypal']['method'] == 'PF') {
        $module_params = func_get_pm_params('ps_paypal_payflowlink.php');
    }

    $post = array(
        'TRXTYPE' => $transaction_type,
        'ORIGID' => $order['order']['extra']['pnref'],
    );

    if ($amount) {
        $post['AMT'] = $amount;
    }

    $res = func_payflow_call($post, $module_params);

    if ($res['RESULT'] == '0') {
        $status = true;
        $err_msg = 'TRXTYPE:' . $transaction_type . '; RESULT: ' . $res['RESULT'] . '; RESPMSG: ' . $res['RESPMSG'] . '; PNREF: ' . $res['PNREF'];
    } else {
        $status = false;
        $err_msg = 'TRXTYPE:' . $transaction_type . 'RESULT: ' . $res['RESULT'] . '; RESPMSG: ' . $res['RESPMSG'] . '; PNREF: ' . $res['PNREF'];
    }

    $extra = array(
        'name' => 'pnref',
        'value' => $res['PNREF'],
    );

    return array($status, $err_msg, $extra);

} //}}}

function func_ps_paypal_advanced_do_capture($order) { //{{{

    $return = func_ps_paypal_advanced_do($order, 'D');
    if ($return[0] == true && !empty($return[2])) {

        if (!empty($return[2])) {
            func_array2insert(
                'order_extras',
                array(
                    'orderid' => $order['order']['orderid'],
                    'khash' => $return[2]['name'],
                    'value' => $return[2]['value']
                ),
                true
            );
        }
    }

    return $return;

} //}}}

function func_ps_paypal_advanced_do_void($order) { //{{{

    return func_ps_paypal_advanced_do($order, 'V');

} //}}}

function func_ps_paypal_advanced_do_refund($order, $amount = 0) { //{{{

    $return = func_ps_paypal_advanced_do($order, 'C', $amount);
    if ($return[0] == true) {
        if (empty($order['order']['extra']['paypal'])) {
            $order['order']['extra']['paypal'] = array();
            $order['order']['extra']['paypal']['subtrans'] = array();
        }


        $order['order']['extra']['paypal']['subtrans'][$return[2]['value']] = array(
            'type' => 'Refunded',
            'amount' => $amount,
            'date' => XC_TIME,
            'note' => $return[1],
        );

        func_array2update(
            'orders',
            array(
                'extra' => addslashes(serialize($order['order']['extra'])),
            ),
            "orderid = '" . $order['order']['orderid'] . "'"
        );
    }

    return $return;

} //}}}

function func_ps_paypal_advanced_get_refund_mode($paymentid, $orderid) { //{{{

    return 'P';

} //}}}

?>
