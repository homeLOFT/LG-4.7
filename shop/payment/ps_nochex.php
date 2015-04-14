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
 * NOCHEX
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Payment interface
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    3968cba5ecdb78320d43cbe05a25fe35597bc800, v50 (xcart_4_7_0), 2015-02-17 13:29:01, ps_nochex.php, aim
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_GET['mode']) && $_GET['mode'] == 'responder' && $_POST && isset($_GET['orderids']) && $_GET['orderids']) {
    require __DIR__.'/auth.php';

    x_load('http');

    $bill_output['sessid'] = func_query_first_cell("SELECT sessid FROM $sql_tbl[cc_pp3_data] WHERE ref='".$orderids."'");

    // APC system responder
    foreach ($_POST as $k => $v) {
        $advinfo[] = "$k: $v";
    }

    $to_email = trim($to_email);

    if ($to_email != func_query_first_cell("SELECT param01 FROM $sql_tbl[ccprocessors] WHERE processor='ps_nochex.php'")) {
        $bill_output['code'] = 2;
        $bill_output['billmes'] = "Invalid NOCHEX account!";

    } else {

        // Request transaction result code
        $post = array();
        foreach ($_POST as $k => $v) {
            $post[] = "$k=$v";
        }

        list($a,$return) = func_https_request('POST', "https://www.nochex.com:443/nochex.dll/apc/apc", $post);
        $return = trim($return);
        if (preg_match('/AUTHORISED/', $return)) {
            $bill_output['code'] = 1;
            $bill_output['billmes'] = $return;

        } else {
            $bill_output['code'] = 2;
            $bill_output['billmes'] = "Reason: Rejected by NOCHEX server (APC system)!";
        }
    }

    if (isset($amount)) {
        $payment_return = array(
            'total' => $amount
        );
    }

    $skey = $_GET['orderids'];
    require $xcart_dir.'/payment/payment_ccmid.php';
    require $xcart_dir.'/payment/payment_ccwebset.php';

} elseif (isset($_GET['mode']) && $_GET['mode'] == 'complete' && isset($_GET['orderids'])) {

    // Handling for 'returnurl' field

    require __DIR__.'/auth.php';

    $weblink = 2;
    $skey = $_GET['orderids'];
    require($xcart_dir.'/payment/payment_ccview.php');

} elseif (isset($_GET['mode']) && $_GET['mode'] == 'cancel' && isset($_GET["orderids"])) {

    // Handling for 'cancelurl' field

    require __DIR__.'/auth.php';

    $bill_output['sessid'] = func_query_first_cell("SELECT sessid FROM $sql_tbl[cc_pp3_data] WHERE ref='".$_GET["orderids"]."'");
    $bill_output['code'] = 2;
    $bill_output['billmes'] = "Canceled by customer";

    $skey = $_GET['orderids'];
    require($xcart_dir.'/payment/payment_ccend.php');

} else {

    if (!defined('XCART_START')) { header("Location: ../"); die("Access denied"); }

    $_orderids = func_addslashes($module_params['param04'].join("-",$secure_oid));
    if (!$duplicate)
        db_query("REPLACE INTO $sql_tbl[cc_pp3_data] (ref,sessid,trstat) VALUES ('".$_orderids."','".$XCARTSESSID."','GO|".implode('|',$secure_oid)."')");

    $fields = array(
        'merchant_id' => trim($module_params['param01']),
        'amount' => $cart["total_cost"],
        'order_id' => $_orderids,
        'cancel_url' => $current_location . '/payment/ps_nochex.php?mode=cancel&orderids=' . $_orderids,
        'billing_fullname' => $bill_firstname . ' ' . $bill_lastname,
        'billing_address' => $userinfo["b_address"] . ", " . $userinfo["b_city"] . ", " . $userinfo["b_statename"],
        'billing_postcode' => $userinfo["b_zipcode"],
        'email_address' => $userinfo['email'],
        'callback_url' => $current_location . '/payment/ps_nochex.php?mode=responder&orderids=' . $_orderids,
        'customer_phone_number' => $userinfo["phone"]
    );

    if ($module_params['testmode'] == 'Y') {
        $fields['test_transaction'] = 100;
        $fields['test_success_url'] = $current_location . '/payment/ps_nochex.php?mode=complete&orderids=' . $_orderids;

    } else {
        $fields['success_url'] =  $current_location . '/payment/ps_nochex.php?mode=complete&orderids=' . $_orderids;
    }

    func_create_payment_form('https://secure.nochex.com/', $fields, 'NOCHEX');
}

exit;

?>
