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
 * X-Payments iframe handling script
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Modules
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    7935adb949cac5546ce2160f50d39aac3b49a0ff, v20 (xcart_4_7_0), 2015-02-19 17:47:45, cc_xpc_iframe.php, random
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */


if (isset($_GET['xpc_action']) || isset($_POST['xpc_action'])) {

    require_once '../top.inc.php';
    define('SKIP_CHECK_REQUIREMENTS.PHP', true);
    define('QUICK_START', true);
    define('SKIP_ALL_MODULES', true);
    define('AREA_TYPE', 'C');
    if (!empty($_GET['xpc_action']) && $_GET['xpc_action'] == 'xpc_popup_show_message') {
        define('DO_NOT_START_SESSION', 1);
    }
    require_once '../init.php';
}

if (!isset($_GET['xpc_action']) && !isset($_POST['xpc_action'])) {
    $xpc_action = '';
}

if ($xpc_action == 'xpc_end') {
    // Initialize X-Payments Connector module 
    $include_func = true;
    require_once '../modules/XPayments_Connector/config.php';
    func_xpay_func_load();

    func_xpc_set_allow_save_cards(@$_POST['allow_save_cards']);

	x_session_register('xpc_order_ids');
	x_session_register('return_url');

    if (
        func_xpc_get_allow_save_cards()
        && func_xpc_use_recharges($paymentid) 
        && false !== strpos($return_url, 'order_message')
    ) {
        foreach($xpc_order_ids as $oid) {
            func_array2insert(
                'order_extras',
                array(
                    'orderid' => $oid,
                    'khash'   => 'xpc_use_recharges',
                    'value'   => 'Y',
                ),
                true
            );
        }
    }

	func_header_location($return_url);

} elseif ($xpc_action == 'xpc_popup') {

    if (!func_is_ajax_request()) {
        exit;
    }
    
    // Initialize X-Payments Connector module 
    $include_func = true;
    require_once '../modules/XPayments_Connector/config.php';
    func_xpay_func_load();

    if (
        !empty($paymentid)
        && (
            $type == XPC_IFRAME_CLEAR_INIT_DATA
            || $type == XPC_IFRAME_CHANGE_METHOD
        )
    ) {
        xpc_clear_initiated_payment_in_session($paymentid);
    }

    if ($type == XPC_IFRAME_CHANGE_METHOD && empty($save_cc)) {
        x_load('cart');
        x_session_register('cart');
        $cart = func_cart_set_paymentid($cart, 0);
    }

    // Set popup to reload page on close and add OK button
    $lbl_ok = func_get_langvar_by_name('lbl_ok', NULL, FALSE, TRUE);
    $close_action = ($type != XPC_IFRAME_ALERT) ? 'window.location.reload();' : '$'+"(o.element).dialog('destroy').remove();";

    $jscode = <<<JS
var buttons = {};
buttons['$lbl_ok'] = function() {
    o.close();
}

$(o.element).dialog(
    {
        title: '$payment_method',
        maxWidth: '350px',
        close: function() {
            $close_action
        },
        buttons: buttons
    }
);
JS;

    func_register_ajax_message(
        'popupDialogCall',
        array(
            'action' => 'jsCall',
            'toEval' => $jscode
        )
    );

    // Show error text
    func_register_ajax_message(
        'popupDialogCall',
        array(
            'action' => 'load',
            'src'    => 'payment/cc_xpc_iframe.php?xpc_action=xpc_popup_show_message&type=' . intval($type) . '&message=' . urlencode(stripslashes($message)),
        )
    );


    func_ajax_finalize();

} elseif ($xpc_action == 'xpc_popup_show_message') {

    $smarty->assign('type', $type);
    $smarty->assign('message', stripslashes($message));
    
    func_flush(func_display('modules/XPayments_Connector/xpc_popup.tpl', $smarty, false));

} elseif ($xpc_action == 'xpc_before_place_order') {

    // Initialize X-Payments Connector module
    $include_func = true;
    require_once '../modules/XPayments_Connector/config.php';
    
    func_xpay_func_load();

    func_xpc_set_allow_save_cards(!empty($allow_save_cards) && 'Y' == $allow_save_cards);

    if (defined('XPC_API_1_3_COMPATIBLE')) {
        // For API 1.3 save in session to use on check_cart callback
        $extras = array(
            'customer_notes' => !empty($Customer_Notes) ? $Customer_Notes : '',
            'ip' => $CLIENT_IP,
            'proxy_ip' => $PROXY_IP,
        );
        xpc_set_customer_extras_in_session($extras);
    } elseif (!empty($Customer_Notes)) {
        // For older API update order directly
        x_session_register('secure_oid');

        $Customer_Notes = addslashes($Customer_Notes);
        $orderids = '\'' . implode('\',\'', $secure_oid) . '\'';

        db_query("UPDATE $sql_tbl[orders] SET customer_notes = '$Customer_Notes' WHERE orderid IN ($orderids) AND status = 'X'");
    }

    if (!empty($partner_id)) {
        include $xcart_dir . '/include/partner_info.php';
    }

} elseif (empty($xpc_action) && !empty($_GET['paymentid'])) {

    require_once __DIR__.'/auth.php';

    func_xpay_func_load();
    
    if (defined('XPC_API_1_3_COMPATIBLE')) {

        $xpc_payment = xpc_get_initiated_payment_from_session($paymentid, (empty($save_cc) ? 'checkout' : 'save_cc'));
     
        if ($xpc_payment) {

            // Payment was already initiated - use existing token and redirect to XP directly

            $redirect_form = xpc_get_initiated_payment_redirect_form($xpc_payment);

            $smarty->assign('action', $redirect_form['url']);
            $smarty->assign('fields', $redirect_form['fields']);

            func_display('modules/XPayments_Connector/xpc_iframe_content.tpl', $smarty);

        } else {

            // Should inititate new payment

            x_load('cart', 'user');

            if (empty($save_cc)) {
                x_session_register('cart');
                $united_cart = $cart;

                if (empty($united_cart['products'])) {
                    // For backwards compatibility
                    x_session_register('products');
                    if (!empty($products)) {
                        $united_cart['products'] = $products;
                    }
                }
            } else {
                $united_cart = array();
                $united_cart['total_cost'] = $config['XPayments_Connector']['xpc_save_cc_amount'];
                $united_cart['shipping_cost'] = $united_cart['tax_cost'] = $united_cart['discount'] = 0;
                $united_cart['products'] = array(
                    array(
                        'productcode' => 'SAVE_CARD_AUTH',
                        'product' => 'Credit card authorization (to obtain payment token)',
                        'price' => $united_cart['total_cost'],
                        'amount' => 1,
                    )
                );
            }

            $united_cart['userinfo'] = func_userinfo($logged_userid);

            $ref_id = md5($logged_userid . $paymentid . XC_TIME);

            func_array2insert(
                'cc_pp3_data',
                array(
                    'ref'    => 'XPC' . $ref_id,
                    'sessid' => $XCARTSESSID,
                    'param1' => 'TEMPORARY',
                    'param2' => $logged_userid,
                    'param3' => (empty($save_cc) ? '' : 'SAVE_CC'),
                ),
                true
            );

            list($status, $response) = xpc_request_payment_init(
                intval($paymentid),
                $ref_id,
                $united_cart,
                !empty($save_cc), // forces Auth when in save_cc mode
                empty($save_cc) ? 'temporary' : 'save_cc'
            );

            if ($status) {

                xpc_save_initiated_payment_in_session($paymentid, $response['fields']['token'], (empty($save_cc) ? 'checkout' : 'save_cc'));

                $smarty->assign('action', $response['url']);
                $smarty->assign('fields', $response['fields']);

                func_display('modules/XPayments_Connector/xpc_iframe_content.tpl', $smarty);

            } else {

                // Post message to parent window which will show popup with default error

                $message_type = func_constant('XPC_IFRAME_CHANGE_METHOD');

                $page = <<<HTML
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head></head>
<body>
<script type="text/javascript">
//<![CDATA[
function postMessageToParent(msg) {
  if (window.parent !== window && window.JSON) {
    window.parent.postMessage(JSON.stringify(msg), '*');
  }
}
postMessageToParent({
    message: 'paymentFormSubmitError',
    params: {
        height: '0',
        error: '',
        type: '$message_type'
    }
});
//]]>
</script>
</body>
</html>
HTML;

                func_flush($page);
                func_exit();

            }

        }
    } else {

        if (!empty($save_cc)) {
            die('This feature is not supported in the current X-Payments version.');
        }

        /*Default order placing routine for backwards compatibility*/

        $payment_method = func_query_first_cell("SELECT payment_method FROM $sql_tbl[payment_methods] WHERE paymentid = '$paymentid'");

        $fields = array(
            'action' 		=> 'place_order',
            'paymentid' 	=> $paymentid,
            'accept_terms'	=> 'Y',
            'xpc_iframe'	=> 'Y',
            'Customer_Notes' => '',
            'payment_method' => $payment_method,
        );

        $smarty->assign('fields', $fields);
        $smarty->assign('action', 'payment_cc.php');

        func_display('modules/XPayments_Connector/xpc_iframe_content.tpl', $smarty);

    }

}

?>
