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
 * Checkout by Amazon
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Modules
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v5 (xcart_4_7_0), 2015-02-17 23:56:28, func.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 *
 */

if ( !defined('XCART_SESSION_START') ) { header("Location: ../../"); die("Access denied"); }

function func_amazon_pa_debug($message, $xml = false) {

    if (!defined('AMAZON_PA_DEBUG') || empty($message))
        return true;
    
    if ($xml) {
        $message = func_xml_format($message, 2);
    }

    x_log_add('amazon_pa', $message);
    return true;
}

function func_amazon_pa_error($message) {

    x_log_add('amazon_pa', $message);
    return true;
}

function func_amazon_pa_init() {

    if (defined('ADMIN_MODULES_CONTROLLER')) {
        if (function_exists('func_add_event_listener')) {
            func_add_event_listener('module.ajax.toggle', 'func_amazon_pa_on_module_toggle');
        }
    }

    return;
}

function func_amazon_pa_on_module_toggle($module_name, $module_new_state) {

    global $sql_tbl, $active_modules;

    if (
        $module_name == 'Amazon_Payments_Advanced'
        && $module_new_state == true
        && !empty($active_modules['Amazon_Checkout'])
    ) {
        db_query("UPDATE $sql_tbl[modules] SET active = 'N' WHERE module_name = 'Amazon_Checkout'");
        return 'modules.php';
    }
}

function func_amazon_pa_request($action, $data) {
    global $config;

    if ($config['Amazon_Payments_Advanced']['amazon_pa_currency'] == 'USD') {
        $url_host = 'mws.amazonservices.com';
    } else {
        $url_host = 'mws-eu.amazonservices.com';
    }

    if ($config['Amazon_Payments_Advanced']['amazon_pa_mode'] == 'test') {
        $url_uri = '/OffAmazonPayments_Sandbox/2013-01-01';
    } else {
        $url_uri = '/OffAmazonPayments/2013-01-01';
    }

    $params = array(
        'AWSAccessKeyId=' . $config['Amazon_Payments_Advanced']['amazon_pa_access_key'],
        'Action=' . $action,
        'SellerId=' . $config['Amazon_Payments_Advanced']['amazon_pa_sid'],
        'SignatureMethod=HmacSHA256',
        'SignatureVersion=2',
        'Timestamp=' . urlencode(date('c')),
    );

    foreach ($data as $k => $v) {
        $params[] = "$k=$v";
    }
    sort($params);

    // sign request
    $concat_params = implode('&', $params);
    $str2sign = "POST\n$url_host\n$url_uri\n" . $concat_params;
    $signature = func_amazon_pa_sign($str2sign);
    $concat_params .= '&Signature=' . urlencode($signature);

    // send request
    x_load('http', 'xml');
    func_amazon_pa_debug("send request=$url_host$url_uri?$concat_params");
    list($headers, $reply) = func_https_request('POST', "https://$url_host$url_uri", $concat_params);
    func_amazon_pa_debug($reply, true);

    $parse_error = array();
    $res = func_xml_parse($reply, $parse_error);
    if (!$res) {
        func_amazon_pa_debug("can not parse XML reply: " . print_r($parse_error, true));
    }

    return $res;
}

function func_amazon_pa_sign($data) {
    global $config;
    return base64_encode(hash_hmac('sha256', $data, $config['Amazon_Payments_Advanced']['amazon_pa_secret_key'], true));
}

function func_amazon_pa_ipn_verify_singature($message) {

    $signature = base64_decode($message['Signature']);
    $certificatePath = $message['SigningCertURL'];

    $fields = array(
        "Timestamp" => true,
        "Message" => true,
        "MessageId" => true,
        "Subject" => false,
        "TopicArn" => true,
        "Type" => true
    );

    ksort($fields);

    $signatureFields = array();
    foreach ($fields as $fieldName => $mandatoryField) {
        $value = $message[$fieldName];
        if (!is_null($value)) {
            array_push($signatureFields, $fieldName);
            array_push($signatureFields, $value);
        }
    }

    // create the signature string - key / value in byte order
    // delimited by newline character + ending with a new line character
    $data = implode("\n", $signatureFields) . "\n";

    $cert = file_get_contents($certificatePath);
    if (empty($cert)) {
        return false;
    }

    $certKey = openssl_get_publickey($cert);

    if ($certKey === false) {
        return false;
    }

    $result = openssl_verify($data, $signature, $certKey, OPENSSL_ALGO_SHA1);
    return ($result > 0);
}


function func_ajax_block_amazon_pa_shipping() {
    global $smarty, $config, $sql_tbl, $active_modules, $xcart_dir;
    global $logged_userid, $login_type, $login, $cart, $userinfo, $is_anonymous, $user_account;
    global $xcart_catalogs, $xcart_catalogs_secure, $current_area;
    global $current_carrier, $shop_language;
    global $intershipper_rates, $intershipper_recalc, $dhl_ext_country_store, $checkout_module, $empty_other_carriers, $empty_ups_carrier, $amazon_enabled, $paymentid, $products;

    define('ALL_CARRIERS', 1);

    x_load('cart', 'shipping', 'product', 'user');

    x_session_register('cart');

    $userinfo = func_userinfo(0, $login_type, false, false, 'H');

    x_session_register('cart');
    x_session_register('intershipper_rates');
    x_session_register('intershipper_recalc');
    x_session_register('current_carrier','UPS');
    x_session_register('dhl_ext_country_store');
    XCAjaxSessions::getInstance()->requestForSessionSave(__FUNCTION__);

    $intershipper_recalc = 'Y';

    $products = func_products_in_cart($cart, @$userinfo['membershipid']);

    $checkout_module = '';
    include $xcart_dir . '/include/cart_calculate_totals.php';

    $check_smarty_vars = array('arb_account_used', 'checkout_module', 'is_other_carriers_empty', 'is_ups_carrier_empty', 'need_shipping', 'shipping_calc_error', 'shipping_calc_service', 'main', 'current_carrier', 'show_carriers_selector', 'dhl_ext_countries', 'has_active_arb_smethods', 'dhl_ext_country');
    func_assign_smarty_vars($check_smarty_vars);

    $smarty->assign('main', 'checkout');
    $smarty->assign('userinfo', $userinfo);

    return func_ajax_trim_div(func_display('modules/One_Page_Checkout/opc_shipping.tpl', $smarty, false));
}

function func_ajax_block_amazon_pa_totals() {
    global $smarty, $config, $sql_tbl, $active_modules, $xcart_dir;
    global $logged_userid, $login_type, $login, $cart, $userinfo, $is_anonymous, $user_account;
    global $xcart_catalogs, $xcart_catalogs_secure;
    global $current_carrier, $shop_language, $current_area, $checkout_module;
    global $intershipper_rates, $intershipper_recalc, $dhl_ext_country_store, $products;

    define('ALL_CARRIERS', 1);

    x_load('cart', 'shipping', 'product', 'user');

    x_session_register('cart');
    x_session_register('intershipper_rates');
    x_session_register('intershipper_recalc');
    x_session_register('current_carrier','UPS');
    x_session_register('dhl_ext_country_store');
    XCAjaxSessions::getInstance()->requestForSessionSave(__FUNCTION__);

    $userinfo = func_userinfo(0, $login_type, false, false, 'H');

    $products = func_products_in_cart($cart, @$userinfo['membershipid']);

    $intershipper_recalc = 'Y';

    $checkout_module = '';
    include $xcart_dir . '/include/cart_calculate_totals.php';

    $check_smarty_vars = array('zero', 'transaction_query', 'shipping_cost', 'reg_error', 'paid_amount', 'need_shipping', 'minicart_total_items', 'force_change_address', 'paymentid', 'need_alt_currency');
    func_assign_smarty_vars($check_smarty_vars);
    $smarty->assign('main', 'checkout');

    $smarty->assign('userinfo',    $userinfo);
    $smarty->assign('products',    $products);
    $smarty->assign('cart_totals_standalone', true);

    return func_ajax_trim_div(func_display('modules/One_Page_Checkout/summary/cart_totals.tpl', $smarty, false));
}

function func_amazon_pa_get_payment_tab() {
    global $sql_tbl;

    // get config vars
    global $smarty;
    $configuration = func_query("SELECT * FROM $sql_tbl[config] WHERE category = 'Amazon_Payments_Advanced' ORDER BY orderby");
    foreach ($configuration as $k => $v) {
        if (in_array($v['type'], array('selector', 'multiselector'))) {
            $vars = func_parse_str(trim($v['variants']), "\n", ":");
            $vars = func_array_map('trim', $vars);
            $configuration[$k]['variants'] = array();

            foreach ($vars as $vk => $vv) {
                if (!empty($vv) && strpos($vv, "_") !== false && strpos($vv, " ") === false) {
                    $name = func_get_langvar_by_name(addslashes($vv), null, false, true);
                    if (!empty($name)) {
                        $vv = $name;
                    }
                }
                $configuration[$k]['variants'][$vk] = array("name" => $vv);
            }

            foreach ($configuration[$k]['variants'] as $vk => $vv) {
                $configuration[$k]['variants'][$vk]['selected'] = $configuration[$k]['type'] == "selector"
                    ? $configuration[$k]['value'] == $vk
                    : in_array($vk, $configuration[$k]['value']);

            }
        }
    }
    $smarty->assign('amazon_pa_configuration', $configuration);

    return  array(
        'title' => func_get_langvar_by_name('lbl_amazon_pa_amazon_advanced'),
        'tpl' => 'modules/Amazon_Payments_Advanced/payment_tab.tpl',
        'anchor' => 'payment-amazon-pa',
    );
}

function func_amazon_pa_save_order_extra($orderids, $key, $val) {
    global $sql_tbl;

    if (!is_array($orderids)) {
        $orderids = array($orderids);
    }

    foreach ($orderids as $orderid) {
        func_array2insert(
            'order_extras',
            array(
                'orderid' => $orderid,
                'khash' => $key,
                'value' => $val
            ),
            true
        );
    }
}

function func_amazon_pa_on_change_order_status($order_data, $status) {
    global $sql_tbl, $config;

    $order = $order_data['order'];

    if ($status == $order['status']) {
        return;
    }

    if (empty($order['extra']['AmazonOrderReferenceId'])) {
        return; // not amazon order
    }

    if ($status == 'D') {
        // cancel ORO if declined
        func_amazon_pa_request('CancelOrderReference', array(
            'AmazonOrderReferenceId' => $order['extra']['AmazonOrderReferenceId']
        ));
    }

    if ($status == 'P') {
        // close ORO when captured
        func_amazon_pa_request('CloseOrderReference', array(
            'AmazonOrderReferenceId' => $order['extra']['AmazonOrderReferenceId']
        ));
    }
}

?>
