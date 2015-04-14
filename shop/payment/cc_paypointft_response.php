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
 * PayPoint Fast Track 
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Payment interface
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    3968cba5ecdb78320d43cbe05a25fe35597bc800, v8 (xcart_4_7_0), 2015-02-17 13:29:01, cc_paypointft_response.php, aim
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if ($_SERVER['REQUEST_METHOD'] == "POST" && @$_GET['mode'] != 'test') {

    // Callback
    require __DIR__.'/auth.php';

    if (!func_is_active_payment('cc_paypointft.php'))
        exit;

    if (defined('PAYPOINTFT_DEBUG')) 
        func_pp_debug_log('paypointft', 'C', print_r($_POST, true) . print_r($_GET, true));

    $module_params = func_get_pm_params('cc_paypointft.php');

    $skey = $strCartID;
    $bill_output['sessid'] = func_query_first_cell("SELECT sessid FROM $sql_tbl[cc_pp3_data] WHERE ref = '" . $skey . "'");
    $bill_output['code'] = $intStatus == 1 ? 1 : 2;
    $bill_output['billmes'] = '';

    if (!empty($strMessage))
        $bill_output['billmes'] = $strMessage;

    if (!empty($intTransID))
        $bill_output['billmes'] .= " (TransId: " . $intTransID . ")";

    if (isset($fltAmount)) {
        $payment_return = array(
            'total' => $fltAmount
        );

        if (isset($strCurrency)) {
            $payment_return['currency'] = $strCurrency;
            $payment_return['_currency'] = func_query_first_cell("SELECT param02 FROM $sql_tbl[ccprocessors] WHERE processor='cc_paypointft.php'");
        }
    }

    if (!empty($strTransID) && $bill_output['code'] == 1) {
        $extra_order_data = array(
            'xnid' => $strTransID,
            'capture_status' => $intAuthMode == 2 ? 'A' : ''
        );
    }

    require $xcart_dir.'/payment/payment_ccmid.php';
    require $xcart_dir.'/payment/payment_ccwebset.php';

}
exit;

?>
