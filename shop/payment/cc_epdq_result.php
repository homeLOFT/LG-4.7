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
 * "ePDQ" payment module (credit card processor)
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Payment interface
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    3968cba5ecdb78320d43cbe05a25fe35597bc800, v41 (xcart_4_7_0), 2015-02-17 13:29:01, cc_epdq_result.php, aim
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

require __DIR__.'/auth.php';

if (!func_is_active_payment('cc_epdq.php'))
    exit;

if ($REQUEST_METHOD == 'POST' && !empty($oid)) {
    $bill_output['sessid'] = func_query_first_cell("SELECT sessid FROM $sql_tbl[cc_pp3_data] WHERE ref = '".$oid."'");
    if (empty($bill_output['sessid']))
        exit;

    $eciresult_codes = array(
        0 => "Transaction type does not support Authentication.",
        1 => "An attempted authentication transaction. Either the issuer or the cardholder are not enrolled in Internet Authentication.",
        2 => "A fully authenticated transaction. The cardholder successfully authenticated themselves.",
        3 => "Unsuccessful Internet Authentication transaction. The CPI will decline the transaction for Visa transactions.",
        4 => "A non authenticated transaction.",
        5 => "BIN range is not enrolled.",
        6 => "Attempts transaction. Either the issuer or cardholder are not enrolled."
    );

    $bill_output['code'] = (stristr($transactionstatus, 'Success') ? 1 : 2);
    $bill_output['billmes'] = $transactionstatus;
    if (isset($eciresult) && !zerolen($eciresult) && in_array($eciresult, array_keys($eciresult_codes))) {
        $bill_output['billmes'] .= '; Authorisation result: ' . $eciresult_codes[$eciresult] .' (eciresult: ' . $eciresult . ')';
    }

    if (isset($cardprefix) && !zerolen($cardprefix)) {
        $bill_output['billmes'] .= '; Card Prefix(First digit of the supplied card number): ' . $cardprefix;
    }

    if (isset($total)) {
        $payment_return = array(
            'total' => $total
        );
    }

    $skey = $oid;
    require($xcart_dir.'/payment/payment_ccmid.php');
    require($xcart_dir.'/payment/payment_ccwebset.php');
}
exit;
?>
