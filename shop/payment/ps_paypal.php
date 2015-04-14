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
 * PayPal CC processing module
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Payment interface
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    3968cba5ecdb78320d43cbe05a25fe35597bc800, v82 (xcart_4_7_0), 2015-02-17 13:29:01, ps_paypal.php, aim
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

// $custom variable exists in data POSTed by PayPal:
// 1) callback (POST)
// 2) return from PayPal (GET)
// it contains order_secureid

/**
 * Successful return from PayPal
 */
if ((isset($_GET['mode']) && $_GET['mode'] == 'success') || (isset($_POST['mode']) && $_POST['mode'] == 'success')) {
    require_once __DIR__.'/auth.php';

    $skey = $_GET['secureid'];

    if (defined('PAYPAL_DEBUG')) {
        func_pp_debug_log('paypal_standard', 'B', print_r($_GET, true) . print_r($_POST, true));
    }

    require($xcart_dir.'/payment/payment_ccview.php');
}

if ((isset($_GET['mode']) && $_GET['mode'] == 'cancel') || (isset($_POST['mode']) && $_POST['mode'] == 'cancel')) {
    require_once __DIR__.'/auth.php';

    if (defined('PAYPAL_DEBUG')) {
        func_pp_debug_log('paypal_standard', 'B', print_r($_GET, true) . print_r($_POST, true));
    }

    $skey = $_GET['secureid'];
    $bill_output['code'] = 2;
    $bill_output['billmes'] = "Canceled by the user";

    require $xcart_dir.'/payment/payment_ccend.php';
}
/**
 * Checkout
 */
else {

    if ( !defined('XCART_START') ) { header("Location: ../"); die("Access denied"); }

    if ($config['paypal_solution'] == 'uk')
        exit;

    x_load('paypal', 'payment');

    $module_params = func_get_pm_params('ps_paypal.php');

    $pp_supported_charsets = array (
        'Big5', 'EUC-JP', 'EUC-KR', 'EUC-TW', 'gb2312', 'gbk', 'HZ-GB-2312', 'ibm-862', 'ISO-2022-CN', 'ISO-2022-JP', 'ISO-2022-KR', 'ISO-8859-1', 'ISO-8859-2', 'ISO-8859-3', 'ISO-8859-4', 'ISO-8859-5', 'ISO-8859-6', 'ISO-8859-7', 'ISO-8859-8', 'ISO-8859-9', 'ISO-8859-13', 'ISO-8859-15', 'KOI8-R', 'Shift_JIS', 'UTF-7', 'UTF-8', 'UTF-16', 'UTF-16BE', 'UTF-16LE', 'UTF16_PlatformEndian', 'UTF16_OppositeEndian', 'UTF-32', 'UTF-32BE', 'UTF-32LE', 'UTF32_PlatformEndian', 'UTF32_OppositeEndian', 'US-ASCII', 'windows-1250', 'windows-1251', 'windows-1252', 'windows-1253', 'windows-1254', 'windows-1255', 'windows-1256', 'windows-1257', 'windows-1258', 'windows-874', 'windows-949', 'x-mac-greek', 'x-mac-turkish', 'x-maccentraleurroman', 'x-mac-cyrillic', 'ebcdic-cp-us', 'ibm-1047'
    );
    foreach ($pp_supported_charsets as $k=>$v) {
        $pp_supported_charsets[$k] = strtolower($v);
    }

    $pp_charset = strtolower($all_languages[$shop_language]['charset']);
    if (!in_array($pp_charset, $pp_supported_charsets)) {
        $pp_charset = "UTF-8";
    }

    $pp_acc = $module_params['param08'];
    $pp_for = $module_params['param09'];
    $pp_curr = func_paypal_get_currency($module_params);
    $pp_prefix = preg_replace("/[ '\"]+/","",$module_params['param06']);
    $pp_ordr = $pp_prefix.join("-",$secure_oid);

    $pp_total = func_paypal_convert_to_BasicAmountType($cart["total_cost"], $pp_curr);

    $pp_host = ($module_params['testmode'] == 'N' ? "www.paypal.com" : "www.sandbox.paypal.com");

    db_query("REPLACE INTO $sql_tbl[cc_pp3_data] (ref,sessid,trstat) VALUES ('".addslashes($order_secureid)."','".$XCARTSESSID."','GO|".implode('|',$secure_oid)."')");

    $_location = func_get_securable_current_location();

    $fields = array(
        'charset' => $pp_charset,
        'cmd' => '_ext-enter',
        'custom' => $order_secureid,
        'invoice' => $pp_ordr,
        'redirect_cmd' => '_xclick',
        'item_name' => $pp_for . ' (Order #' . $pp_ordr . ')',
        'mrb' => "R-2JR83330TB370181P",
        'pal' => 'RDGQCFJTT6Y6A',
        'rm' => '2',
        'email' => $userinfo['email'],
        'first_name' => $bill_firstname,
        'last_name' => $bill_lastname,
        'business' => $pp_acc,
        'amount' => $pp_total,
        'tax_cart' => 0,
        'shipping' => 0,
        'handling' => 0,
        'weight_cart' => 0,
        'currency_code' => $pp_curr,
        'return' => $_location . "/payment/ps_paypal.php?mode=success&secureid=$order_secureid",
        'cancel_return' => $_location . DIR_CUSTOMER . '/cart.php?mode=checkout',
        'shopping_url' => $_location . "/payment/ps_paypal.php?mode=cancel&secureid=$order_secureid",
        'notify_url' => $_location . '/payment/ps_paypal_ipn.php',
        'upload' => 1,
        'bn' => "x-cart"
    );

    // Get line items if possible
    $pp_line_items_totals = func_paypal_is_line_items_allowed($cart, $pp_total);

    if (!empty($pp_line_items_totals)) {
        // Line items are allowed
        $pp_line_items = func_paypal_get_payment_details_items_rest($cart);

        if (!empty($pp_line_items)) {
            // Line items are available
            $fields['cmd'] = '_cart'; // change command type to support line items
            $fields = array_merge($fields, $pp_line_items);
            // Update taxes, handling and discounts
            $fields['tax_cart'] = $pp_line_items_totals['TaxTotal'];
            $fields['handling'] = $pp_line_items_totals['HandlingTotal'];
            $fields['discount_amount_cart'] = $pp_line_items_totals['DiscountTotal'];
        }
    }

    if ($config['paypal_address_override'] == 'Y') {
        $fields['address_override'] = 1;
    }

    $u_phone = preg_replace('![^\d]+!', '', $userinfo["phone"]);
    if (!empty($u_phone)) {
        if ($userinfo['b_country'] == 'US') {
            $fields['night_phone_a'] = substr($u_phone, -10, -7);
            $fields['night_phone_b'] = substr($u_phone, -7, -4);
            $fields['night_phone_c'] = substr($u_phone, -4);
        } else {
            $fields['night_phone_b'] = substr($u_phone, -10);
        }
    }

    if ($module_params['use_preauth'] == 'Y')
        $fields['paymentaction'] = 'authorization';

    if (!empty($active_modules['Bill_Me_Later']) && !empty($bml_enabled)) {
        $fields['userselectedfundingsource'] = 'BML';
    }

    x_load('user');
    $areas = func_get_profile_areas(empty($login) ? 'H' : 'C');

    if ($areas['B']) {
        $fields['country'] = $userinfo['b_country'];
        $fields['state'] = $userinfo['b_state'];

        if (!empty($userinfo['b_address']))
            $fields['address1'] = $userinfo["b_address"];
        if (!empty($userinfo['b_address_2']))
            $fields['address2'] = $userinfo["b_address_2"];
        if (!empty($userinfo['b_city']))
            $fields['city'] = $userinfo["b_city"];
        if (!empty($userinfo['b_zipcode']))
            $fields['zip'] = $userinfo["b_zipcode"];
    }

    if (!$areas['S'] && !$areas['B']) {
        $fields['no_shipping'] = 1;
    }

    $address_fields = array('address1', 'address2', 'city', 'zip');
    foreach($address_fields as $k) {
        if (empty($fields[$k]))
            $fields[$k] = '';
    }

    if (defined('PAYPAL_DEBUG')) {
        func_pp_debug_log('paypal_standard', 'I', $fields);
    }

    func_create_payment_form("https://$pp_host/cgi-bin/webscr", $fields, 'PayPal');
}
exit;

?>
