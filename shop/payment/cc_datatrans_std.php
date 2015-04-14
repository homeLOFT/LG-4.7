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
 * "Datatrans eCom - Universal Payment Page" payment module (credit card processor)
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Payment interface
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    3968cba5ecdb78320d43cbe05a25fe35597bc800, v24 (xcart_4_7_0), 2015-02-17 13:29:01, cc_datatrans_std.php, aim
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if (!isset($REQUEST_METHOD))
    $REQUEST_METHOD = $_SERVER['REQUEST_METHOD'];

if ($REQUEST_METHOD == 'POST' && !empty($_GET['xref'])) {
    // FROM DATATRANS
    require __DIR__.'/auth.php';

    $bill_output['sessid'] = func_query_first_cell("SELECT sessid FROM $sql_tbl[cc_pp3_data] WHERE ref='".$xref."'");

    if (!isset($_POST['errorCode']) && isset($_POST['authorizationCode'])) {
        $bill_output['code'] = 1;
        $bill_output['billmes'] = $responseMessage;
    } else {
        $bill_output['code'] = 2;
        $bill_output['billmes'] = $errorMessage;
    }

    #Security Checking
    if (!empty($secret) && $secret != md5($_POST['merchantId'] . $_POST['refno'] . $bill_output["sessid"])) {
        $bill_output['code'] = 2;
        $bill_output['billmes'] .= "Security tests were not passed for callback";
    }

    $_save_fields = array();
    foreach (array('status', 'pmethod', 'errorCode', 'uppTransactionId',
        'authorizationCode') as $_field) {
        if (isset($_POST[$_field])) {
            $_save_fields[] = $_field.': '.$_POST[$_field];
        }
    }

    if ($status == 'cancel')
        $bill_output['billmes'] .= "Canceled by customer";

    if (!empty($_save_fields))
        $bill_output['billmes'] .= " (".implode(', ', $_save_fields).")";

    include $xcart_dir.'/payment/payment_ccend.php';
} else {
    // FROM CHECKOUT
    if (!defined('XCART_START')) { header("Location: ../"); die("Access denied"); }

    $pp_refno = str_replace(" ", '', $module_params['param04']) . join("-", $secure_oid);
    if (!$duplicate)
        db_query("REPLACE INTO $sql_tbl[cc_pp3_data] (ref,sessid) VALUES ('".addslashes($order_secureid)."','".$XCARTSESSID."')");

    $post['merchantId'] = $module_params['param01'];
    $post['refno'] = $pp_refno;
    $post['amount'] = 100*$cart['total_cost']; // total amount in cents
    $post['currency'] = $module_params['param03'];
    $post['successUrl'] = $post['errorUrl'] = $post['cancelUrl'] = $current_location.'/payment/cc_datatrans_std.php?xref='.$order_secureid;
    $post['reqtype'] = 'CAA'; // authorisation with immediate settlement, if the transaction is authorised
    // For security checking
    $post['secret'] = md5($post['merchantId'] . $post['refno'] . $XCARTSESSID);
    if (in_array(strtolower($shop_language),array('de','en','fr')))
        $post['language'] = strtolower($shop_language);
    else
        $post['language'] = 'en';

    $pp_subdomain = $post['merchantId'] == '1000011011' ? 'pilot' : 'payment';
    $pp_url = 'https://' . $pp_subdomain . '.datatrans.biz/upp/jsp/upStartIso.jsp';

    func_create_payment_form($pp_url, $post, 'DataTrans');
    exit();
}

?>
