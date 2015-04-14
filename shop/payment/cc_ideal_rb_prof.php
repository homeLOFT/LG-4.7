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
 * iDEAL: Rabobank Professional payment gateway
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Payment interface
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    3968cba5ecdb78320d43cbe05a25fe35597bc800, v32 (xcart_4_7_0), 2015-02-17 13:29:01, cc_ideal_rb_prof.php, aim
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

function ideal_rb_pro_test()
{
    return
        function_exists('openssl_x509_read') &&
        function_exists('openssl_x509_export') &&
        function_exists('openssl_get_privatekey') &&
        function_exists('openssl_sign');
}

define('XC_IDEAL_RB_PRO', 'cc_ideal_rb_prof.php');

if (isset($_GET['ec']) && isset($_GET['trxid'])) {

    // Return from gateway
    require_once __DIR__.'/auth.php';

    if (!func_is_active_payment(XC_IDEAL_RB_PRO)) {
        exit;
    }

    if (!ideal_rb_pro_test()) {
        include $xcart_dir.'/payment/payment_ccend.php';
        exit;
    }

    x_load('http');

    x_session_register('cart');
    x_session_register('secure_oid');

    func_pm_load(XC_IDEAL_RB_PRO);

    $module_params = func_get_pm_params(XC_IDEAL_RB_PRO);

    $bill_output['sessid'] = func_query_first_cell("SELECT sessid FROM $sql_tbl[cc_pp3_data] WHERE ref = '" . $_GET['ec'] . "'");

    $sTransactionId = $_GET['trxid'];
    $sTransactionCode = $_GET['ec'];

    if (defined('XC_IDEAL_RB_PRO_DEBUG')) {

        $response = array(
            'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'],
            'GET' => $_GET
        );

        func_pp_debug_log('ideal_rb_prof', 'R', $response);
    }

    $statusRequest = new IdealProStatusRequest();

    $statusRequest->setTransactionId($sTransactionId);

    $transactionStatus = $statusRequest->doRequest();
    
    if (defined('XC_IDEAL_RB_PRO_DEBUG')) {

        func_pp_debug_log('ideal_rb_prof', 'Status', $statusRequest->getErrors());
    }

    if ($statusRequest->hasErrors()) {
        $bill_output['code'] = 2;
        $bill_output['billmes'] = '';

        foreach($statusRequest->getErrors() as $errorEntry) {
            $bill_output['billmes'] .= $errorEntry['desc'] . "\n";
        }

    } elseif (!empty($transactionStatus)) {

        $bill_output['code'] = strcmp($transactionStatus, 'SUCCESS') === 0 ? 1 : 3;
        $bill_output['billmes'] = 'Account name:' . $statusRequest->getAccountName()
            . "\nAccount number:" . $statusRequest->getAccountNumber()
            . "\nStatus:" . $transactionStatus;

        if ($bill_output['code'] != 1) {
            $bill_error = '';

            foreach($statusRequest->getErrors() as $errorEntry) {
                $bill_error .= $errorEntry['desc'] . "\n";
            }
        }
    }

    include($xcart_dir.'/payment/payment_ccend.php');

    exit;

} elseif (isset($_POST['iid'])) {

    // Issuer is selected: redirect to gateway
    require_once __DIR__.'/auth.php';

    if (!func_is_active_payment(XC_IDEAL_RB_PRO)) {
        exit;
    }

    if (!ideal_rb_pro_test()) {
        include $xcart_dir.'/payment/payment_ccend.php';
        exit;
    }

    x_load('http');

    x_session_register('cart');
    x_session_register('secure_oid');

    func_pm_load(XC_IDEAL_RB_PRO);

    $module_params = func_get_pm_params(XC_IDEAL_RB_PRO);

    $sIssuerId = $_POST['iid'];

    $sOrderId = $module_params['param09'].join('-',$secure_oid);

    $fOrderAmount = $cart['total_cost'];
    $sOrderDescription = 'Order'.(count($secure_oid) > 1 ? 's' : '').' '.join(', ',$secure_oid);

    $transRequest = new IdealProTransactionRequest();

    $transRequest->setIssuerId($sIssuerId);

    $transRequest->setOrderId($sOrderId);
    $transRequest->setOrderAmount($fOrderAmount);
    $transRequest->setOrderDescription($sOrderDescription);
    $transRequest->setEntranceCode($sOrderId);

    $transactionID = $transRequest->doRequest();
    
    if (defined('XC_IDEAL_RB_PRO_DEBUG')) {

        func_pp_debug_log('ideal_rb_prof', 'I', $transRequest->getErrors());
    }

    if ($transRequest->hasErrors()) {
        $bill_output['code'] = 2;
        $bill_output['billmes'] = '';

        foreach($transRequest->getErrors() as $errorEntry) {
            $bill_output['billmes'] .= $errorEntry['desc'] . "\n";
        }

        include $xcart_dir.'/payment/payment_ccend.php';

    } elseif (!empty($transactionID)) {

        db_query("REPLACE INTO $sql_tbl[cc_pp3_data] (ref,sessid) VALUES ('" . $sOrderId . "','" . $XCARTSESSID . "')");
        $transRequest->doTransaction();
    }

    exit;

} else {

    // Get issuers list and select the issuer
    if (!defined('XCART_START')) { header('Location: ../'); die('Access denied'); }

    x_load('http');

    func_set_time_limit(100);

    if (!ideal_rb_pro_test()) {
        $bill_output['code'] = 2;
        $bill_output['billmes'] = func_get_langvar_by_name('lbl_cc_ideal_openssl_not_found', array(), false, true);
        return;
    }

    $issueRequest = new IdealProIssuerRequest();

    $issuers = $issueRequest->doRequest();
    
    if (defined('XC_IDEAL_RB_PRO_DEBUG')) {

        func_pp_debug_log('ideal_rb_prof', 'Issuers', $issueRequest->getErrors());
    }

    if ($issueRequest->hasErrors()) {
        $bill_output['code'] = 2;
        $bill_output['billmes'] = '';

        foreach($issueRequest->getErrors() as $errorEntry) {
            $bill_output['billmes'] .= $errorEntry['desc'] . "\n";
        }

        return;
    }

    if (!empty($issuers)) {

        $formHTML = <<<IDEALFORM
<form action="cc_ideal_rb_prof.php" method="post" name="iidgo">

<div style="width: 100%; text-align: center;">
<table style="display: inline-block;">
<tr>
    <td>iDEAL issuers list:</td>
    <td align="center" valign="middle">
        <select name="iid" onchange="javascript: if (document.iidgo.iid.value) document.iidgo.submit();">
            <option value="">{{{first_option}}}</option>
            {{{options_list}}}
        </select>
    </td>
    <td><noscript><input type="submit" /></noscript></td>
</tr>
</table>
</div>

</form>
IDEALFORM;

        $first_option = func_get_langvar_by_name('lbl_select', false, false, true);

        $options_list = '';

        foreach($issuers as $key => $value) {
            $options_list .= '<option value="' . $key . '">' . $value . '</option>';
        }

        $formHTML = str_replace('{{{first_option}}}', $first_option, $formHTML);
        $formHTML = str_replace('{{{options_list}}}', $options_list, $formHTML);

        echo $formHTML;

        exit;
    }
}

?>
