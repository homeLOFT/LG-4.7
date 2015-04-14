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
 * "Allied Wallet" payment module
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Payment interface
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    3968cba5ecdb78320d43cbe05a25fe35597bc800, v6 (xcart_4_7_0), 2015-02-17 13:29:01, cc_awallet.php, aim
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

/**
 * Allied Wallet method
 */

// Uncomment the below line to enable the debug log
// define('AWALLET_DEBUG', 1);

if (
    !empty($_GET['MerchantReference'])
    && !empty($_GET['result']) 
) {

    // User returns to store

    require __DIR__.'/auth.php';

    if (!func_is_active_payment('cc_awallet.php'))
        exit;

    $skey = $MerchantReference;

    if (defined('AWALLET_DEBUG')) {
        func_pp_debug_log('awallet', 'R', $_GET);
    }
    
    if ($_GET['result'] == 'return') {
        require ( $xcart_dir . '/payment/payment_ccview.php' );
    } else {
        $_sessid = func_query_first_cell("SELECT sessid FROM $sql_tbl[cc_pp3_data] WHERE ref='$skey'");
        $bill_output['sessid'] = $_sessid;
        $bill_output['code'] = 2;
        $bill_output['billmes'] = 'Declined';

        require ($xcart_dir.'/payment/payment_ccend.php');
    }
            
} elseif (
    $_SERVER['REQUEST_METHOD'] == 'POST' 
    && !empty($_POST['MerchantReference'])
) {

    // Process the callback

    require __DIR__.'/auth.php';

    if (!func_is_active_payment('cc_awallet.php'))
        exit;

    if (defined('AWALLET_DEBUG')) {
        func_pp_debug_log('awallet', 'C', $_POST);
    }

    $skey = $_POST['MerchantReference'];
    $bill_output['sessid'] = func_query_first_cell("SELECT sessid FROM $sql_tbl[cc_pp3_data] WHERE ref='" . $skey . "'");

    $module_params = func_get_pm_params('cc_awallet.php');

    if (
        $Amount > 0
        && !empty($PayReferenceID)
    ) {
        $bill_output['code'] = 1;
        $bill_message = 'Accepted';
        $payment_return = array(
            'cost'      => $Amount
        );

    } else {
        $bill_output['code'] = 3;
        $bill_message = 'Declined (processor error)';
    }

    require $xcart_dir . '/payment/payment_ccmid.php';
    require $xcart_dir . '/payment/payment_ccwebset.php';

}
else {

    if (!defined('XCART_START')) { header('Location: ../'); die('Access denied'); }

    list($post, $MerchantReference) = func_cc_awallet_prepare_post($module_params, $secure_oid, $cart);

    if (!$duplicate)
        db_query("REPLACE INTO $sql_tbl[cc_pp3_data] (ref,sessid,trstat) VALUES ('".addslashes($MerchantReference)."','".$XCARTSESSID."','GO|".implode('|',$secure_oid)."')");

    func_create_payment_form("https://sale.alliedwallet.com/quickpay.aspx", $post, 'AWALLET');
}


exit;

?>
