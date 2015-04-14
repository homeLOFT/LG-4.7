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
 * "DeltaPAY" payment module (credit card processor)
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Payment interface
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    3968cba5ecdb78320d43cbe05a25fe35597bc800, v37 (xcart_4_7_0), 2015-02-17 13:29:01, cc_delta.php, aim
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['Result']) && !empty($_POST['Param1'])) {

    // Return

    require __DIR__.'/auth.php';

    $bill_output['sessid'] = func_query_first_cell("SELECT sessid FROM $sql_tbl[cc_pp3_data] WHERE ref='".$Param1."'");
    $bill_output['code'] = ($Result == '1') ? 1 : 2;

    if ($Result == 2) {
        $bill_output['billmes'] = 'Error';

    } elseif ($Result == 3) {
         $bill_output['billmes'] = 'Cancelled';
    }

    if ($ErrorMessage)
        $bill_output['billmes'] .= ": " . $ErrorMessage;

    if ($DeltaPayId)
        $bill_output['billmes'] .= " (DeltaPayID: " . $DeltaPayId . ")";

    require($xcart_dir.'/payment/payment_ccend.php');

} else {

    if (!defined('XCART_START')) { header("Location: ../"); die("Access denied"); }

    $amount = strtr($cart['total_cost'], '.', ",");

    $ordr = $module_params['param04'] . join("-", $secure_oid);
    if (!$duplicate) {
        db_query("REPLACE INTO $sql_tbl[cc_pp3_data] (ref,sessid) VALUES ('".addslashes($ordr)."','".$XCARTSESSID."')");
    }

    $fields = array(
        'merchantCode' => $module_params["param01"],
        'param1' => $ordr,
        'charge' => $amount,
        'currencycode' => $module_params["param03"],
        'transactiontype' => '1',
        'installments' => '0',
        'cardholderemail' => $userinfo["email"]
    );

    func_create_payment_form('https://www.deltapay.gr/entry.asp', $fields, 'DeltaPAY');

}

exit;

?>
