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
 * eWay Web / eWAY - Stored payment (Shared)
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Payment interface
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    3968cba5ecdb78320d43cbe05a25fe35597bc800, v46 (xcart_4_7_0), 2015-02-17 13:29:01, cc_ewayweb.php, aim
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST["ewayTrxnStatus"])) {
    require __DIR__.'/auth.php';

    if (defined('EWAY_DEBUG')) {
        func_pp_debug_log('eway', 'C', $_POST);
    } 

    $bill_output['sessid'] = func_query_first_cell("SELECT sessid FROM $sql_tbl[cc_pp3_data] WHERE ref='".$_POST["eWAYoption1"]."'");

    $bill_output['code'] = (preg_match("/^true$/i",$_POST['ewayTrxnStatus']) ? 1 : 2);
    $bill_output['billmes'] = '';
    if(!empty($_POST['eWAYAuthCode']))    $bill_output['billmes'].= " (ewayAuthCode: ".$_POST['eWAYAuthCode'].") ";
    if(!empty($_POST['ewayTrxnError']))    $bill_output['billmes'].= " (ewayTrxnError: ".$_POST['ewayTrxnError'].") ";

    if (isset($ewayReturnAmount)) {
        $ewayReturnAmount = preg_replace("/[^\d\.]/", '', $ewayReturnAmount);
        $payment_return = array(
            'total' => (empty($ewayReturnAmount) ? 0 : $ewayReturnAmount / 100)
        );
    }

    require($xcart_dir.'/payment/payment_ccend.php');

} else {

    if (!defined('XCART_START')) { header("Location: ../"); die("Access denied"); }

    $_orderids = $module_params ['param03'] . join('-', $secure_oid);
    if (!$duplicate)
        db_query("REPLACE INTO $sql_tbl[cc_pp3_data] (ref,sessid) VALUES ('".addslashes($_orderids)."','".$XCARTSESSID."')");

    $fields = array(
        'ewayCustomerID' => $module_params["param01"],
        'ewayTotalAmount' => 100 * $cart["total_cost"],
        'ewayCustomerInvoiceRef' => func_htmlentities($_orderids),
        'ewayCustomerFirstName' => func_htmlentities($bill_firstname),
        'ewayCustomerLastName' => func_htmlentities($bill_lastname),
        'ewayCustomerEmail' => $userinfo["email"],
        'ewayCustomerAddress' => func_htmlentities($userinfo["b_address"]),
        'ewayCustomerPostcode' => $userinfo["b_zipcode"],
        'ewayOption1' => $_orderids,
        'ewayOption3' => $module_params['testmode'] == 'Y' ? 'TRUE' : 'FALSE',
        'ewayURL' => $current_location . '/payment/cc_ewayweb.php'
    );

    if (defined('EWAY_DEBUG')) {
        func_pp_debug_log('eway', 'I', $fields);
    } 

    func_create_payment_form('https://www.eway.com.au/gateway/storedpayment.asp', $fields, 'eWay');
}

exit;

?>
