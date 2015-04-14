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
 * PayPal Website Payments Pro
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Payment interface
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    3968cba5ecdb78320d43cbe05a25fe35597bc800, v63 (xcart_4_7_0), 2015-02-17 13:29:01, ps_paypal_pro.php, aim
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if (!defined('XCART_START')) {
    require_once __DIR__.'/auth.php';
}

x_load('paypal', 'payment');

$paymentid = isset($paymentid) ? $paymentid : 0;

if (
    $config['paypal_solution'] == 'advanced'
    || $config['paypal_solution'] == 'payflowlink'
) {

    $module_params = func_get_pm_params(($config['paypal_solution'] == 'advanced') ? 'ps_paypal_advanced.php' : 'ps_paypal_payflowlink.php');

} else if ($config['paypal_solution'] == 'pro_hosted') {

    $module_params = func_get_pm_params('ps_paypal_pro_hosted.php');

    // Convert Pro Hosted parameters to match Express Checkout ones

    $module_params['param01'] = $module_params['params']['api_username'];
    $module_params['param02'] = $module_params['params']['api_password'];
    $module_params['param04'] = $module_params['params']['api_certificate'];
    $module_params['param05'] = $module_params['params']['api_signature'];
    $module_params['param03'] = $module_params['params']['currency'];
    $module_params['param06'] = $module_params['params']['order_prefix'];
    $module_params['param07'] = $module_params['params']['api_method'];
    $module_params['param08'] = $module_params['params']['payflowcolor'];
    $module_params['param09'] = $module_params['params']['hdrimg'];

} else {

    $module_params = func_get_pm_params('ps_paypal_pro.php');

}

x_session_register('cart');

$pp_locale_codes = array('AU','DE','FR','GB','IT','JP','US');
$pp_supported_charsets = array (
    'Big5', "EUC-JP", "EUC-KR", "EUC-TW", 'gb2312', 'gbk', "HZ-GB-2312",
    "ibm-862", "ISO-2022-CN", "ISO-2022-JP", "ISO-2022-KR", "ISO-8859-1",
    "ISO-8859-2", "ISO-8859-3", "ISO-8859-4", "ISO-8859-5", "ISO-8859-6",
    "ISO-8859-7", "ISO-8859-8", "ISO-8859-9", "ISO-8859-13", "ISO-8859-15",
    "KOI8-R", 'Shift_JIS', "UTF-7", "UTF-8", "UTF-16", "UTF-16BE",
    "UTF-16LE", "UTF-32", "UTF-32BE", "UTF-32LE", "US-ASCII",
    "windows-1250", "windows-1251", "windows-1252", "windows-1253",
    "windows-1254", "windows-1255", "windows-1256", "windows-1257",
    "windows-1258", "windows-874", "windows-949", "x-mac-greek",
    "x-mac-turkish", "x-maccentraleurroman", "x-mac-cyrillic",
    "ebcdic-cp-us", "ibm-1047"
);

$pp_charset = in_array($all_languages[$shop_language]['charset'], $pp_supported_charsets) ? $all_languages[$shop_language]['charset'] : 'UTF-8';

$pp_test = $module_params['testmode'];

$_pp_dp_id = func_query_first_cell("SELECT $sql_tbl[payment_methods].paymentid FROM $sql_tbl[payment_methods], $sql_tbl[ccprocessors] WHERE $sql_tbl[payment_methods].paymentid='$paymentid' AND $sql_tbl[payment_methods].processor_file='ps_paypal_pro.php' AND $sql_tbl[payment_methods].processor_file=$sql_tbl[ccprocessors].processor AND $sql_tbl[payment_methods].paymentid<>$sql_tbl[ccprocessors].paymentid AND $sql_tbl[payment_methods].active='Y'");
$_pp_dp_allowed = !empty($_pp_dp_id);

$pp_total = func_paypal_convert_to_BasicAmountType($cart["total_cost"]);

$pp_final_action = ($module_params['use_preauth'] == 'Y' || func_is_preauth_force_enabled($secure_oid)) ? 'Authorization' : 'Sale';

$use_xpc = false;
if ($_pp_dp_allowed && !empty($active_modules['XPayments_Connector'])) {
    func_xpay_func_load();

    $proc = xpc_get_paypal_dp_processor($config['paypal_solution']);
    $use_xpc = $proc['use_xpc'] && $proc['use'] == 'xpc';
}

if ($use_xpc) {
    define('XPC_USE_DP_EMULATION', true);
    require_once $xcart_dir.'/payment/cc_xpc.php';

} elseif ($config['paypal_solution'] == 'uk') {
    require_once $xcart_dir.'/payment/ps_paypal_pro_uk.php';

} elseif ($config['paypal_solution'] == 'advanced' || $config['paypal_solution'] == 'payflowlink') {
    require_once $xcart_dir.'/payment/ps_paypal_pro_uk.php';

} else {
    require_once $xcart_dir.'/payment/ps_paypal_pro_us.php';
}
?>
