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
 * "ePDQ basic" payment integration module (credit card processor)
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Payment interface
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    3968cba5ecdb78320d43cbe05a25fe35597bc800, v3 (xcart_4_7_0), 2015-02-17 13:29:01, cc_epdq_basic_result.php, aim
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if (!empty($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'GET') {

    // Process callback request

    require __DIR__.'/auth.php';

    if (defined('EPDQ_BASIC_DEBUG')) {
        func_pp_debug_log('epdq_basic', 'C', print_r($_GET, true) . print_r($_POST, true));
    }

    // Load payment method params

    $module_params = func_get_pm_params('cc_epdq_basic.php');

    // Load payment method functions

    func_pm_load('cc_epdq_basic.php');

    // Make all keys in GET uppercase to avoid any case issues

    $epdq_response = array_change_key_case($_GET, CASE_UPPER);

    if (!empty($epdq_response['ORDERID'])) {
        $epdq_basic_sdata = func_query_first("SELECT sessid, trstat, is_callback FROM $sql_tbl[cc_pp3_data] WHERE ref='" . addslashes($epdq_response['ORDERID']) . "'");
    }

    if (!empty($_GET['mode'])) {

        // Cancel / Exception

        if (
            !empty($epdq_basic_sdata)
            && empty($epdq_basic_sdata['is_callback'])
            && preg_match('/GO\|/s', $epdq_basic_sdata['trstat'])
        ) {
            // User cancels the transaction
            $bill_output['sessid'] = $epdq_basic_sdata['sessid'];
            $bill_output['billmes'] = 'Cancelled by user';
            $bill_output['code'] = 2;

            require $xcart_dir.'/payment/payment_ccend.php';

        } else {

            require $xcart_dir.'/payment/payment_ccview.php';
        }

    } else {
        // Accept / Decline
        $trusted_request = func_cc_epdq_basic_check_signature($epdq_response, $module_params['param03']);

        if ($trusted_request && !empty($epdq_basic_sdata)) {

            list($epdq_trx_code, $epdq_trx_message) = func_cc_epdq_basic_process_status($epdq_response['STATUS']);

            $bill_output['sessid']   = $epdq_basic_sdata['sessid'];
            $bill_output['code']     = $epdq_trx_code;
            $bill_output['billmes']  = $epdq_trx_message;

            if (!empty($epdq_response['ORDERID'])) {
                $bill_output['billmes'].= " (Order number: " . $epdq_response['ORDERID'] . ")";
            }

            if (!empty($epdq_response['PAYID'])) {
                $bill_output['billmes'] .= ' (TransId: ' . strval($epdq_response['PAYID']) . ')';
            }

            if (!empty($epdq_response['TRXDATE'])) {
                $bill_output['billmes'] .= ' (TransDate: ' . strval($epdq_response['TRXDATE']) . ')';
            }

            if (!empty($epdq_response['AMOUNT'])) {
                $payment_return = array(
                    'total' => $epdq_response['AMOUNT']
                );
            }

            if (in_array($epdq_response['STATUS'], array(5, 51))) {
                $bill_output['is_preauth'] = true;
            }

            $weblink = 2;

            require($xcart_dir.'/payment/payment_ccend.php');
        }
    }
}

exit;

?>
