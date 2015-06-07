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
 * X-Payments Connector addon 
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Payment interface
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    3ffa496e8098a538e8366aef4504a1e4398b9381, v60 (xcart_4_7_1), 2015-03-27 16:31:10, cc_xpc.php, random
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

$cur_auth_path = dirname(__FILE__) . '/auth.php';
$old_auth_path = dirname(__FILE__) . '/../auth.php';
$auth_path = is_file($cur_auth_path) && is_readable($cur_auth_path) ? $cur_auth_path : $old_auth_path;

if (
    $_SERVER['REQUEST_METHOD'] == 'POST' 
    && !empty($_POST['action'])
    && $_POST['action'] == 'return' 
    && !empty($_POST['refId']) 
    && !empty($_POST['txnId'])
) {

    // Return

    require $auth_path;

    func_xpay_func_load();

    $key = 'XPC' . $_POST['refId'];
    $cc_pp3_data = func_query_first("SELECT sessid, param3 FROM $sql_tbl[cc_pp3_data] WHERE ref = '$key'");
    $bill_output['sessid'] = $cc_pp3_data['sessid'];

    list($status, $response) = xpc_request_get_payment_info($_POST['txnId']);

    $is_save_card_request = ($cc_pp3_data['param3'] == 'SAVE_CC');
    $card_saved = false;

    $extra_order_data = array(
        'xpc_txnid' => $_POST['txnId'],
    );

    if ($status) {

        $bill_output['code'] = 2;

        if (isset($_POST['last_4_cc_num'])) {
            $last_4_cc_num = $_POST['last_4_cc_num'];
        } elseif (isset($_GET['last_4_cc_num'])) {
            $last_4_cc_num = $_GET['last_4_cc_num'];
        } else {
            $last_4_cc_num = 'n/a';
        }

        if (isset($_POST['card_type'])) {
            $card_type = $_POST['card_type'];
        } elseif (isset($_GET['card_type'])) {
            $card_type = $_GET['card_type'];
        } else {
            $card_type = 'n/a';
        }

        if (
            'n/a' !== $last_4_cc_num 
            && 'n/a' !== $card_type
        ) { 
            $extra_order_data['xpc_saved_card_num'] = str_repeat('*', 12) . $last_4_cc_num;
            $extra_order_data['xpc_saved_card_type'] = $card_type;
        }

        if (
            !$is_save_card_request
            && func_xpc_get_allow_save_cards()
            && func_xpc_use_recharges($cart['paymentid'])
        ) {
            $extra_order_data['xpc_use_recharges'] = 'Y';
        }

        if ($response['status'] == PAYMENT_AUTH_STATUS || $response['status'] == PAYMENT_CHARGED_STATUS) {

            $bill_output['code'] = 1;

            if (!defined('XPC_API_1_3_COMPATIBLE')) {
                x_load('order');
                // quantities wasn't decreased when "X" order is initially placed
                x_session_register('secure_oid');
                foreach ($secure_oid as $_orderid) {
                    $order_data = func_order_data($_orderid);
                    $products = $order_data['products'];
                    func_decrease_quantity($products);
                }
            }

            if (
                $logged_userid
                && ($is_save_card_request || !empty($extra_order_data['xpc_use_recharges']))
            ) {
                x_session_register('secure_oid');
                $orig_orderid = !empty($secure_oid[0]) ? $secure_oid[0] : 0;

                $card_saved = func_xpc_store_saved_card(
                    $logged_userid,
                    !empty($extra_order_data['xpc_saved_card_num']) ? $extra_order_data['xpc_saved_card_num'] : '',
                    !empty($extra_order_data['xpc_saved_card_type']) ? $extra_order_data['xpc_saved_card_type'] : '',
                    $_POST['txnId'],
                    $orig_orderid,
                    (!$is_save_card_request) ? $cart['paymentid'] : $config['XPayments_Connector']['xpc_save_cc_paymentid']
                );

                $extra_order_data['xpc_saved_card_id'] = $card_saved;
                    
            }

        } elseif ($response['transactionInProgress']) {

            $bill_output['code'] = 3;

        }

        $bill_output['billmes'] = ($bill_output['code'] == 1)
            ? $response['message']
                . "\n"
                . '(last 4 card numbers: '
                . $last_4_cc_num
                . ');'
                . "\n"
                . '(card type: '
                . $card_type
                . ');'
            : $response['lastMessage'];

        if (
            $response['status'] == PAYMENT_AUTH_STATUS
            || (
                $response['authorizeInProgress'] > 0 
                && $bill_output['code'] == 3
            )
        ) {

            $extra_order_data['capture_status'] = 'A';

            $bill_output['is_preauth'] = true;

        } else {

            $extra_order_data['capture_status'] = '';

        }

        if (
            $bill_output['code'] == 1 
            && $response['isFraudStatus']
        ) {

            $extra_order_data['fmf_blocked'] = 'Y';

        }

        $payment_return = array(
            'total'     => $response['amount'],
            'currency'  => $response['currency'],
            '_currency' => xpc_get_currency($_POST['refId']),
        );

        $xpc_order_status = xpc_get_order_status_by_action($response['status']);

    } else {

        $bill_output['code'] = 2;
        $bill_output['billmes'] = 'Internal error';

    }

    if ($is_save_card_request) {
        db_query("DELETE FROM $sql_tbl[cc_pp3_data] WHERE ref = '$key'");
    
        if ($card_saved) {
            $top_message = array(
                'type' => 'I',
                'content' => func_get_langvar_by_name('msg_xpc_new_card_saved'),
            );
        } else {
            $top_message = array(
                'type' => 'E',
                'content' => $response['lastMessage'],
            );
        }

        func_iframe_redirect($current_location . DIR_CUSTOMER . '/saved_cards.php');
        exit;
    }

    $weblink = false;

    if ($config['XPayments_Connector']['xpc_use_iframe'] == 'Y') {
        $is_iframe = true;
        $use_xpc_iframe_redirect = true;
    }

    require($xcart_dir . '/payment/payment_ccend.php');

    exit;

} elseif (
    $_SERVER['REQUEST_METHOD'] == 'GET' 
    && !empty($_GET['action'])
    && (
        $_GET['action'] == 'cancel'
        || $_GET['action'] == 'abort' 
    ) && !empty($_GET['refId']) 
    && !empty($_GET['txnId'])
) {

    // Cancel

    require $auth_path;

    func_xpay_func_load();

    $key = 'XPC' . $_GET['refId'];

    $bill_output['sessid'] = func_query_first_cell("SELECT sessid FROM $sql_tbl[cc_pp3_data] WHERE ref = '" . $key . "'");

    $bill_output['code'] = 2;

    $bill_output['billmes'] = 'cancel' == $_GET['action']
        ? 'Cancelled by customer'
        : 'Aborted due to errors during transaction processing';

    $weblink = false;

    $paymentid = intval($cart['paymentid']);

    if ($config['XPayments_Connector']['xpc_use_iframe'] == 'Y') {
        $is_iframe = true;
        $use_xpc_iframe_redirect = true;
    }

    require($xcart_dir . '/payment/payment_ccend.php');

    exit;

} elseif (
    $_SERVER['REQUEST_METHOD'] == 'POST'
    && !empty($_POST['txnId'])
    && !empty($_POST['action'])
    && (
        ($_POST['action'] == 'callback' && !empty($_POST['updateData']))
        || ($_POST['action'] == 'check_cart' && !empty($_POST['refId']))
    )
) {

    // Callback or check cart

    require $auth_path;

    // Since output should be clean for callbacks,
    // we need to disable errors and notices display.
    // Check logs (if enabled) for them.
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    if (defined('DEVELOPMENT_MODE')) {
        function func_xpc_assert_handler($file, $line, $code) {
            x_log_add('Assertion', debug_backtrace(!DEBUG_BACKTRACE_PROVIDE_OBJECT));
            if (strpos($code, '(EE)') !== FALSE) {
                die;
            }
        }
        assert_options(ASSERT_CALLBACK, 'func_xpc_assert_handler');
    }

    // Check module
    if (empty($active_modules['XPayments_Connector'])) {

        if (function_exists('x_log_add')) {
            x_log_add('xpay_connector', 'X-Payments Connector callback script is called', true);
        } else {
            error_log('xpay_connector: X-Payments Connector callback script is called', 0);
        }

        exit;

    }

    // Check callback IP addresses
    $ips = preg_grep('/^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/Ss', array_map('trim', explode(',', $config['XPayments_Connector']['xpc_allowed_ip_addresses'])));

    $found = false;

    foreach ($ips as $ip) {
        if ($_SERVER['REMOTE_ADDR'] == $ip) {
            $found = true;
            break;
        }
    }

    if (
        $ips 
        && !$found
    ) {

        if (function_exists('x_log_add')) {
            x_log_add('xpay_connector', 'X-Payments Connector callback script is called from wrong IP address: \'' . $_SERVER['REMOTE_ADDR'] . '\'', true);
        } else {
            error_log('xpay_connector: X-Payments Connector callback script is called from wrong IP address: \'' . $_SERVER['REMOTE_ADDR'] . '\'', 0);
        }

        exit;

    }

    func_xpay_func_load();

    if ($action == 'callback') {

        list($responseStatus, $response) = xpc_decrypt_xml($updateData);

        if (!$responseStatus) {

            xpc_api_error('Callback request is not decrypted (Error: ' . $response . ')');

            exit;
        }

        // Convert XML to array
        $response = xpc_xml2hash($response);

        if (!is_array($response)) {

            xpc_api_error('Unable to convert callback request into XML');

            exit;
        }

        // The 'Data' tag must be set in response
        if (!isset($response[XPC_TAG_ROOT])) {

            xpc_api_error('Callback request does not contain any data');

            exit;
        }

        $response = $response[XPC_TAG_ROOT];

        // Process data
        if (!xpc_api_process_error($response)) {

            xpc_update_payment($txnId, $response);

        }

    } else {

        $ref_key = 'XPC' . $_POST['refId'];
        $cc_pp3_data = func_query_first("SELECT sessid, param1, param2, param3 FROM $sql_tbl[cc_pp3_data] WHERE ref = '" . $ref_key . "'");

        x_session_id($cc_pp3_data['sessid']);
        x_session_register('secure_oid');
        include $xcart_dir . '/include/partner_info.php';

        $data = array(
            'status' => 'cart-not-changed',
        );

        xpc_clear_initiated_payment_in_session();

        if (
            !defined('XPC_API_1_3_COMPATIBLE')
            || $cc_pp3_data['param1'] != 'TEMPORARY'
        ) {
            // Use backward compatiblity mode
            if (!$secure_oid) {
                // Order id was lost, need to create new order - throw error
                $data['status'] = 'cart-changed';
            }

        } elseif ($cc_pp3_data['param3'] == 'SAVE_CC') {
            // This is a save card request, need to save card :)
            $data['saveCard'] = 'Y';

        } else {
            // Send actual cart and saveCard values
            $data['status'] = 'cart-changed';

            x_session_register('cart');

            x_load(
                'user',
                'crypt',
                'order',
                'payment',
                'tests'
            );

            $paymentid = intval($cart['paymentid']);

            x_session_register('logged_paymentid');
            $logged_paymentid = $paymentid;

            x_session_register('secure_oid_cost');
            x_session_register('initial_state_orders', array());
            x_session_register('initial_state_show_notif', 'Y');

            $module_params = func_get_pm_params($paymentid);
            $in_testmode = get_cc_in_testmode($module_params);

            $extra = array();
            if ($in_testmode) {
                $extra['in_testmode'] = $in_testmode;
            }

            $payment_method_text = func_xpc_compose_payment_method_text($paymentid);

            $united_cart = $cart;
            $united_cart['userinfo'] = func_userinfo($cc_pp3_data['param2']);

            if (empty($united_cart['products'])) {
                // For backwards compatibility
                x_session_register('products');
                if (!empty($products)) {
                    $united_cart['products'] = $products;
                }
            }

            $customer_extras = xpc_pop_customer_extras_from_session();

            $customer_notes = $customer_extras['customer_notes'];

            // Restore real customer IP and other saved data
            $CLIENT_IP = $customer_extras['ip'];
            $PROXY_IP = $customer_extras['proxy_ip'];

            if (!empty($active_modules['XMultiCurrency'])) {
                $store_currency = $customer_extras['store_currency'];
            }

            $orderids = func_place_order(
                $payment_method_text,
                'I', // X status is not used since API 1.3
                '',
                $customer_notes,
                $extra
            );

            if (
                !empty($orderids)
                && !in_array($orderids, XCPlaceOrderErrors::getAllCodes())
            ) {

                $secure_oid      = $orderids;
                $secure_oid_cost = $cart['total_cost'];
                $initial_state_orders     = func_array_merge($initial_state_orders, $orderids);
                $initial_state_show_notif = 'Y';

                foreach ($secure_oid as $oid) {
                    func_array2insert(
                        'order_extras',
                        array(
                            'orderid' => $oid,
                            'khash'   => 'xpc_txnid',
                            'value'   => $_POST['txnId'],
                        ),
                        true
                    );
                }

                $data['ref_id'] = implode('-', $secure_oid);

                db_query("DELETE FROM $sql_tbl[cc_pp3_data] WHERE ref = '" . $ref_key . "'");

                func_array2insert(
                    'cc_pp3_data',
                    array(
                        'ref'    => 'XPC' . $data['ref_id'],
                        'sessid' => $cc_pp3_data['sessid'],
                    ),
                    true
                );

                $data['cart'] = xpc_prepare_cart(
                    $united_cart,
                    $data['ref_id'],
                    function_exists('func_is_preauth_force_enabled') ? func_is_preauth_force_enabled($secure_oid) : false
                );

                if (!$data['cart']) {
                    // Remove cart from output so that X-Payments will give error
                    unset($data['cart']);
                }

                $data['saveCard'] = (func_xpc_get_allow_save_cards() && func_xpc_use_recharges($paymentid)) ? 'Y' : 'N';

            }

        }

        $xml = xpc_hash2xml($data);

        if (!$xml) {
            die(xpc_api_error('Data is not valid'));
        }

        // Encrypt
        $xml = xpc_encrypt_xml($xml);

        if (!$xml) {
            die(xpc_api_error('Data is not encrypted'));
        }

        echo $xml;

    }

    exit;

} else {

    // For disabled iframe or API 1.2 only
    // Initialize transaction & redirect to X-Payments

    if (!defined('XCART_START')) { header('Location: ../'); die('Access denied'); }

    func_xpay_func_load();

    func_xpc_set_allow_save_cards('Y' == @$allow_save_cards);

    $refId = implode('-', $secure_oid);

    if (!$duplicate) {
        func_array2insert(
            'cc_pp3_data',
            array(
                'ref'       => 'XPC' . $refId, 
                'sessid' => $XCARTSESSID,
            ), 
            true
        );
    }

    $united_cart = $cart;

    $united_cart['userinfo'] = $userinfo;
    $united_cart['products'] = $products;

    list($status, $response) = xpc_request_payment_init(
        intval($paymentid),
        $refId,
        $united_cart,
        function_exists("func_is_preauth_force_enabled") ? func_is_preauth_force_enabled($secure_oid) : false
    );

    if ($status) {

        foreach ($secure_oid as $oid) {
            func_array2insert(
                'order_extras',
                array(
                    'orderid' => $oid,
                    'khash'   => 'xpc_txnid',
                    'value'   => $response['txnId'],
                ),
                true
            );
        }

        $smarty->assign('action', $response['url']);
        $smarty->assign('fields', $response['fields']);

        func_display('modules/XPayments_Connector/xpc_iframe_content.tpl', $smarty);

        exit;

    } else {

        $bill_output['code'] = 2;
        $bill_output['billmes'] = 'Internal error';

        if (
            isset($response['detailed_error_message'])
            && !empty($response['detailed_error_message'])
        ) {

            $bill_output['billmes'] .= ' (' . $response['detailed_error_message'] . ')';

        }

        $weblink = false;

        if ($config['XPayments_Connector']['xpc_use_iframe'] == 'Y') {
            $is_iframe = true;
            $use_xpc_iframe_redirect = true;
        }


    }

}

?>
