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
 * Order details interface
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Admin interface
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    3968cba5ecdb78320d43cbe05a25fe35597bc800, v113 (xcart_4_7_0), 2015-02-17 13:29:01, order.php, aim
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

require __DIR__.'/auth.php';

require $xcart_dir . '/include/security.php';

x_load(
    'mail',
    'order'
);

$redirectLocation = false;

if ($mode == 'update') {

    // Update orders info (status)

    define('STATUS_CHANGE_REF', 1);

    if (
        is_array($order_status)
        && is_array($order_status_old)
    ) {
        foreach ($order_status as $orderid => $status) {

            if (
                is_numeric($orderid)
                && $status != $order_status_old[$orderid]
            ) {
                func_change_order_status($orderid, $status);
            }

        }

        func_header_location('orders.php' . (empty($qrystring) ? '' : "?$qrystring"));

    }

} elseif (
    $mode == 'prolong_ttl'
    && $orderid
    && !empty($active_modules['Egoods'])
) {

    // Prolong TTL

    $itemids = func_query("SELECT $sql_tbl[order_details].itemid FROM $sql_tbl[order_details], $sql_tbl[download_keys] WHERE $sql_tbl[order_details].orderid = '$orderid' AND $sql_tbl[order_details].itemid = $sql_tbl[download_keys].itemid");

    if ($itemids) {
        foreach ($itemids as $v) {
            db_query("UPDATE $sql_tbl[download_keys] SET expires = '" . (XC_TIME + $config["Egoods"]["download_key_ttl"] * 3600) . "' WHERE itemid = '$v[itemid]'");
        }
    }

    $pids = func_query("SELECT $sql_tbl[order_details].itemid, $sql_tbl[order_details].productid, $sql_tbl[products].distribution FROM $sql_tbl[order_details], $sql_tbl[products] WHERE $sql_tbl[order_details].orderid = '$orderid' AND $sql_tbl[order_details].productid = $sql_tbl[products].productid AND $sql_tbl[products].distribution != ''");

    if ($pids) {

        $keys = array();

        foreach ($pids as $v) {

            if (func_query_first_cell("SELECT COUNT(*) FROM $sql_tbl[download_keys] WHERE itemid = '$v[itemid]'"))
                continue;

            $keys[$v['itemid']]['download_key'] = keygen($v["productid"], $config["Egoods"]["download_key_ttl"], $v['itemid']);
            $keys[$v['itemid']]['distribution_filename'] = basename($v['distribution']);

        }

        if (!empty($keys)) {

            $order = func_order_data($orderid);

            if (!empty($order)) {

                foreach ($order['products'] as $k => $v) {
                    if (isset($keys[$v['itemid']])) {
                        $order['products'][$k] = func_array_merge($v,$keys[$v['itemid']]);
                    }
                }

                $mail_smarty->assign('products', $order['products']);
                $mail_smarty->assign('order',    $order['order']);
                $mail_smarty->assign('userinfo', $order['userinfo']);

                func_send_mail(
                    $order['userinfo']["email"],
                    'mail/egoods_download_keys_subj.tpl',
                    'mail/egoods_download_keys.tpl',
                    $config['Company']['orders_department'],
                    false
                );

            } // if (!empty($order))

        } // if (!empty($keys))

    } // if ($pids)

    $redirectLocation = true;

} elseif (
    $mode == 'send_ip'
    && $orderid
) {

    // Send customer IP address to Anti Fraud server

    list($a, $result) = func_send_ip_to_af($orderid, $reason);

    if ($result == '1') {

        $top_message['content'] = func_get_langvar_by_name('msg_antifraud_ip_added');
        $top_message['type'] = 'I';

    } else {

        $top_message['content'] = func_get_langvar_by_name('txt_antifraud_service_generror');
        $top_message['type'] = 'E';

    }

    $redirectLocation = true;

} elseif ($mode == 'update_paypal') {

    // Update PayPal transaction information
    x_load('paypal');

    func_paypal_update_order($orderid);

    $top_message = array(
        'content' => func_get_langvar_by_name('txt_paypal_main_trans_is_updated')
    );

    $redirectLocation = true;

} elseif ($mode == 'paypal_accept_pending') {

    // Accept PayPal transaction in Pending status
    x_load('paypal');

    list($status, $msg) = funct_ps_paypal_pro_fmf($orderid, true);

    $top_message = array(
        'type'         => $status
            ? 'I'
            : 'E',
        'content'     => $msg
            ? $msg
            : func_get_langvar_by_name('txt_paypal_pending_transaction_is_approved'),
    );

    $redirectLocation = true;

} elseif ($mode == 'paypal_decline_pending') {

    // Decline PayPal transaction in Pending status
    x_load('paypal');

    list($status, $msg) = funct_ps_paypal_pro_fmf($orderid, false);

    $top_message = array(
        'type'         => $status
            ? 'I'
            : 'E',
        'content'     => $msg
            ? $msg
            : func_get_langvar_by_name('txt_paypal_pending_transaction_is_declined'),
    );

    $redirectLocation = true;

} elseif (
    $mode == 'create_refund'
    && $REQUEST_METHOD == 'POST'
) {

    // Create refund transaction in PayPal
    x_load('paypal');

    $res = func_paypal_create_refund($orderid, $amount, $note);

    if (
        is_array($res)
        || $res === false
    ) {

        $txt = func_get_langvar_by_name('lbl_unknown');

        if (!empty($res)) {

            $txt = array();

            foreach($res as $r) {
                $txt[] = "&nbsp;&nbsp;&nbsp;" . $r['desc'];
            }

            $txt = implode("<br />\n", $txt);
        }

        $top_message = array(
            'content'     => func_get_langvar_by_name('txt_paypal_refund_isnt_created', array('errors' => $txt)),
            'type'         => 'E',
        );

    } else {

        $top_message = array(
            'content' => func_get_langvar_by_name('txt_paypal_refund_is_created'),
        );

    }

    $redirectLocation = true;

}

if (!empty($active_modules['XPayments_Subscriptions'])) {
    if (
        $REQUEST_METHOD == 'POST'
        && $mode == 'update_subscription_status'
    ) {
        func_xps_updateSubscriptionStatus($orderid, $subscriptionid, $status);

        $redirectLocation = true;
    }
}

if (true === $redirectLocation) {
    func_header_location("order.php?orderid=" . $orderid);
}

$order_ids = explode(",", $orderid);

if (!is_array($order_ids)) $order_ids[] = $orderid;

foreach ($order_ids as $oid) {
    if (!is_numeric($oid))
        func_403(8);
}

$smarty->assign('show_order_details', 'Y');

/**
 * Collect infos about ordered products
 */
require $xcart_dir . '/include/history_order.php';

$order     = $order_data['order'];
$userinfo  = $order_data['userinfo'];
$products  = $order_data['products'];
$giftcerts = $order_data['giftcerts'];

$smarty->assign('orderid', $orderid);

if (!empty($active_modules['Klarna_Payments'])) {
    $order_payment_processor = func_query_first_cell("SELECT processor_file FROM $sql_tbl[payment_methods] WHERE paymentid = $order[paymentid]");
    $smarty->assign('is_klarna_payment', (strpos($order_payment_processor, 'cc_klarna') !== false) ? 'Y' : 'N');
}

if ($mode == 'status_change') {

    // Update order

    $query_data = array (
        'tracking' => $tracking,
        'notes'    => $notes,
    );

    if (isset($customer_notes)) {
        $query_data['customer_notes'] = $customer_notes;
    }

    if (isset($_POST['details'])) {
        $query_data['details'] = func_crypt_order_details($details);
    }

    func_array2update('orders', $query_data, "orderid = '$orderid'");

    if (!empty($active_modules['AfterShip'])) {

        func_aftership_set_tracking($tracking);
    }

    if (!empty($active_modules['Advanced_Order_Management'])) {

        $query_data['details'] = $details;

        $diff = func_aom_prepare_diff('X', $query_data, $order);

        if (
            !empty($diff)
            || !empty($history_comment)
            || $status != $order['status']
        ) {

            if ($status != $order['status'])
                define('STATUS_CHANGE_REF', 1);

            $details = array(
                'old_status' => $order['status'],
                'new_status' => $status,
                'diff'       => $diff,
                'comment'    => stripslashes($history_comment),
                'is_public'  => $history_is_public,
            );

            func_aom_save_history($orderid, 'X', $details);

        }

        define('ORDER_HISTORY_SAVED', true);
    }

    func_change_order_status($orderid, $status);

    $top_message = array(
        'content' => func_get_langvar_by_name('txt_order_has_been_changed')
    );

    func_header_location("order.php?orderid=" . $orderid);

} elseif ($mode == 'cnote') {

    // Update customer notes
    func_array2update(
        'orders',
        array(
            'customer_notes' => $customer_notes,
        ),
        "orderid = '$orderid'"
    );

    if (!empty($active_modules['Advanced_Order_Management'])) {

        $diff = func_aom_prepare_diff(
            'X',
            array(
                'customer_notes' => $customer_notes,
            ),
            $order
        );

        if (!empty($diff)) {

            $details = array(
                'new_status' => $order['status'],
                'diff'       => $diff,
            );

            func_aom_save_history($orderid, 'X', $details);

        }

    }

    exit;
} elseif (
    $mode == 'xpc_charge'
    && !empty($saved_card_id)
    && !empty($active_modules['XPayments_Connector'])
) {

    func_xpay_func_load();

    if (
        !isset($recharge_total)
        || !is_numeric($recharge_total)    
    ) {

        $top_message = array(
            'type'    => 'E',  
            'content' => func_get_langvar_by_name('lbl_recharge_wrong_amount')
        );

        func_header_location("order.php?orderid=" . $orderid);
    
    }

    list($xpc_status, $xpc_result) = func_xpc_process_recharge($saved_card_id, $recharge_total, $orderid, $order['userid']);

    if ($xpc_status) {

        $saved_cards = func_xpc_get_saved_cards($order['userid']);

        $recharge_orderid = !empty($saved_cards[$saved_card_id]['orderid']) ? $saved_cards[$saved_card_id]['orderid'] : 0;

        if (!empty($recharge_orderid)) {
            $orig_payment_method = func_query_first_cell("SELECT payment_method FROM $sql_tbl[orders] WHERE orderid = '$recharge_orderid'");
        } else {
           $orig_paymentid = func_xpc_get_orig_paymentid_for_recharge($orderid);
           $orig_payment_method = func_xpc_compose_payment_method_text($orig_paymentid);
        }

        func_array2update(
           'orders',
           array(
               'init_total' => $recharge_total,
               'payment_method' => $orig_payment_method,
           ),
           "orderid = '" . $orderid . "'"
        );

        $top_message = array(
            'content' => func_get_langvar_by_name('txt_order_has_been_changed')
        );

    } else {
        $top_message = array(
            'type'    => 'E',  
            'content' => func_get_langvar_by_name('lbl_recharge_failed')
        );
    }

    func_header_location("order.php?orderid=" . $orderid);

}


/**
 * Delete order
 */
if ($mode == 'delete') {

    func_delete_order($orderid);

    func_header_location("orders.php?".$query_string);

}

$smarty->assign('main', 'history_order');

if (
    !empty($active_modules['Advanced_Order_Management'])
    && $mode == 'edit'
) {

    include $xcart_dir . '/modules/Advanced_Order_Management/order_edit.php';

} elseif (
    !empty($active_modules['Anti_Fraud'])
    && $mode == 'anti_fraud'
) {

    if ($order['extra']) {

        $userinfo             = $order_data['userinfo'];
        $extra                 = $order['extra'];
        $extras['ip']         = $extra['ip'];
        $extras['proxy_ip'] = $extra['proxy_ip'];

        include $xcart_dir . '/modules/Anti_Fraud/anti_fraud.php';

        func_array2update(
            'orders',
            array(
                'extra' => addslashes(serialize($extra)),
            ),
            'orderid=\'' . $orderid . '\''
        );
    }

    func_header_location("order.php?orderid=".$orderid);

} elseif (
    !empty($active_modules['Stop_List'])
    && $mode == 'block_ip'
) {

    func_add_ip_to_slist(
        $order['extra']['proxy_ip']
            ? $order['extra']['proxy_ip']
            : $order['extra']['ip']
    );

    $top_message['content'] = func_get_langvar_by_name('msg_stoplist_ip_added');
    $top_message['type']     = 'I';

    func_header_location("order.php?orderid=".$orderid);
}

if (
    'edit' !== $mode
    || 'preview' === $show
) {

    $smarty->assign('gmap_enabled', 'Y');

}

if (!empty($active_modules['XPayments_Connector'])) {

    func_xpay_func_load();

    $recharge_paymentid = func_xpc_use_recharges()
        ? func_query_first_cell("SELECT paymentid FROM $sql_tbl[payment_methods] WHERE payment_script = 'payment_xpc_recharge.php'")
        : false;

    if (
        !empty($active_modules['Advanced_Order_Management'])
        && !empty($order['extra']['created_by_admin'])
        && 'Y' == $order['extra']['created_by_admin']
        && $recharge_paymentid = $order['paymentid']
        && !in_array($order['status'], array('A', 'P', 'C'))
    ) {

        $saved_cards = func_xpc_get_saved_cards($order['userid']);
        $default_card_id = func_xpc_get_default_card($order['userid']);
        $smarty->assign('saved_cards', $saved_cards);
        $smarty->assign('default_card_id', $default_card_id);

    }

}


$smarty->assign('advinfo', $order['extra']['advinfo']);

// Assign the current location line
$smarty->assign('location', $location);

// Assign the section navigation data
$dialog_tools_data = array('help' => true);
$smarty->assign('dialog_tools_data', $dialog_tools_data);

if (is_readable($xcart_dir . '/modules/gold_display.php')) {
    include $xcart_dir . '/modules/gold_display.php';
}

func_display('admin/home.tpl', $smarty);
?>
