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
 * PSiGate Interac Online
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Payment interface
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    3968cba5ecdb78320d43cbe05a25fe35597bc800, v48 (xcart_4_7_0), 2015-02-17 13:29:01, cc_psigate.php, aim
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if ($_SERVER['REQUEST_METHOD'] == "GET" && isset($_GET["OrderID"])) {
    require __DIR__.'/auth.php';

    $bill_output['sessid'] = func_query_first_cell("SELECT sessid FROM $sql_tbl[cc_pp3_data] WHERE ref='".$OrderID."'");

    if ($Approved == 'APPROVED') {
        $bill_output['code'] = 1;
        $bill_output['billmes'] = "Approved: ".$Approved." (Approval Code: ".$ReturnCode.")";

    } else {
        $bill_output['code'] = 2;
        $bill_output['billmes'] = $_GET['ErrMsg'];
    }

    if (isset($FullTotal)) {
        $payment_return = array(
            'total' => $FullTotal
        );
    }

    require($xcart_dir.'/payment/payment_ccend.php');

} else {

    if (!defined('XCART_START')) { header("Location: ../"); die("Access denied"); }

    $ordr = $module_params ['param02'] . join('-', $secure_oid);
    $url = $current_location.'/payment/cc_psigate.php';

    if (!$duplicate)
        db_query("REPLACE INTO $sql_tbl[cc_pp3_data] (ref,sessid) VALUES ('".addslashes($ordr)."','".$XCARTSESSID."')");

    $fields = array(
        'Email' => $userinfo["email"],
        'PaymentType' => 'DB',
        'Bcity' => $userinfo["b_city"],
        'Bcountry' => $userinfo["b_country"],
        'Bname' => $bill_name,
        'Bpostalcode' => $userinfo["b_zipcode"],
        'Bprovince' => $userinfo["b_state"],
        'Baddress1' => $userinfo["b_address"],
        'Phone' => $userinfo["phone"],
        'StoreKey' => $module_params ["param01"],
        'OrderID' => $ordr,
        'UserID' => $cart["login"],
        'CustomerIP' => func_get_valid_ip($REMOTE_ADDR),
        'SubTotal' => $cart["total_cost"],
        'ChargeType' => '1',
        'ThanksURL' => $url,
        'NoThanksURL' => $url,
    );

    func_create_payment_form('https://checkout.psigate.com/HTMLPost/HTMLMessenger', $fields, 'PSiGate');
}

exit;

?>
