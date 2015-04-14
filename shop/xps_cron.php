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
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Modules
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    3968cba5ecdb78320d43cbe05a25fe35597bc800, v4 (xcart_4_7_0), 2015-02-17 13:29:01, xps_cron.php, aim
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

define('X_CRON', true);

if (!defined('X_INTERNAL_CRON')) {

    $_SERVER['REQUEST_METHOD'] = $REQUEST_METHOD = 'GET';

    if (php_sapi_name() != 'cli') {
        header("Location: ./");
        die("Access denied");
    }

    chdir(__DIR__);
    require_once __DIR__.'/auth.php';

    $argv = $_SERVER['argv'];
    array_shift($argv);

    // Get options
    $options = array();

    foreach ($argv as $a) {
        if (preg_match("/--([\w\d_]+)\s*=\s*(['\"]?)(.+)['\"]?$/Ss", trim($a), $match)) {
            $options[strtolower($match[1])] = $match[2] ? stripslashes($match[3]) : $match[3];
        }
    }

    // Check key
    if (!isset($options['key']) || !preg_match("/^[a-zA-Z0-9_]{6,}$/Ss", $options['key']) || $config['General']['cron_key'] != $options['key'])
        exit(1);

    // $sowner = get_current_user();
    // $processuser = posix_getpwuid(posix_geteuid());
    // $processuser = $processuser['name'];

} elseif (!defined('XCART_START')) {

    header("Location: ./");
    die("Access denied");

}

if (empty($active_modules['XPayments_Subscriptions'])) {
    if (!defined('X_INTERNAL_CRON'))
        exit(0);

    func_403();
}

x_load('order');


$notification_days = $config['XPayments_Subscriptions']['xps_notification_days'];
$subscriptions = func_xps_getActiveSubscriptions(XC_TIME + $notification_days * 24 * 60 * 60);

if ($subscriptions) {
    foreach ($subscriptions as $subscription) {
        $order_data = func_order_data($subscription['orderid']);

        $userinfo = $order_data['userinfo'];

        $mail_smarty->assign('userinfo', $userinfo);
        $mail_smarty->assign('subscription', $subscription);

        foreach ($order_data['products'] as $product) {
            if ($product['productid'] == $subscription['productid']) {
                $mail_smarty->assign('product', $product);

                break;
            }
        }

        func_send_mail(
            $userinfo['email'],
            'mail/xps_upcoming_rebill_subj.tpl',
            'mail/xps_upcoming_rebill.tpl',
            $config['Company']['users_department'],
            false
        );
    }
}

$subscriptions = func_xps_getActiveSubscriptions(XC_TIME);

if ($subscriptions) {
    foreach ($subscriptions as $subscription) {
        $result = func_xps_placeOrder($subscription);

        if ($result == false) {
            // notify admin

            continue;
        }

        $new_orderid = $result['orderid'];

        list($status, $response) = func_xps_transact($result['xpc_txnid'], $subscription['fee'], 'Recharge');
        $orderid = $subscription['orderid'];

        if ($status && empty($response['error']) && empty($response['error_message'])) {

            func_change_order_status($new_orderid, 'P');

            $extra = func_query_first_cell("SELECT extra FROM $sql_tbl[orders] WHERE orderid = '$new_orderid'");

            $extra = empty($extra) ? array() : unserialize($extra);
            $extra = empty($extra) ? array() : $extra;

            $extra = func_array_merge($extra, array(
                'xpc_txnid' => $response['transaction_id'],
            ));

            func_array2update(
                'orders',
                array(
                    'extra' => addslashes(serialize($extra)),
                ),
                "orderid = '$new_orderid'"
            );

            $next_date = func_xps_getNextDate($subscription['next_date'], $subscription);
            $success_attempts = $subscription['success_attempts'] + 1;
            $status = ($subscription['rebill_periods'] > 0 && $success_attempts >= $subscription['rebill_periods']) ? 'F' : 'A'; // Finished, Active

            $data = array(
                'status' => $status,
                'attempts' => 0,
                'success_attempts' => $success_attempts,
                'next_date' => $next_date,
                'real_next_date' => $next_date,
            );

            func_array2update('xps_subscriptions', $data, "subscriptionid = '$subscription[subscriptionid]'");
            $subscription = func_array_merge($subscription, $data);

            if ($status == 'F') {

                $order_data = func_order_data($subscription['orderid']);

                $mail_smarty->assign('userinfo',  $userinfo);
                $mail_smarty->assign('subscription', $subscription);

                foreach ($order_data['products'] as $product) {
                    if ($product['productid'] == $subscription['productid']) {
                        $mail_smarty->assign('product', $product);

                        break;
                    }
                }

                func_send_mail(
                    $userinfo['email'],
                    'mail/xps_subscription_status_subj.tpl',
                    'mail/xps_subscription_status_customer.tpl',
                    $config['Company']['orders_department'],
                    false
                );

                func_send_mail(
                    $config['Company']['orders_department'],
                    'mail/xps_subscription_status_subj.tpl',
                    'mail/xps_subscription_status.tpl',
                    $config['Company']['orders_department'],
                    true
                );
            }

        } else {

            $advinfo = array();
            $advinfo[] = 'Reason: ' . $response['error'];

            func_change_order_status($new_orderid, 'F', $advinfo);

            $extra = func_query_first_cell("SELECT extra FROM $sql_tbl[orders] WHERE orderid = '$new_orderid'");

            $extra = empty($extra) ? array() : unserialize($extra);
            $extra = empty($extra) ? array() : $extra;

            $extra = func_array_merge($extra, array(
                'xpc_txnid' => '',
            ));

            func_array2update(
                'orders',
                array(
                    'extra' => addslashes(serialize($extra)),
                ),
                "orderid = '$new_orderid'"
            );

            $next_date = func_xps_getNextDate($subscription['real_next_date'], $subscription, false);
            $attempts = $subscription['attempts'] + 1;

            $data =  array(
                'attempts' => $attempts,
                'real_next_date' => $next_date,
            );

            if ($attempts >= $config['XPayments_Subscriptions']['xps_rebill_attempts']) {

                $order_data = func_order_data($subscription['orderid']);

                $data['status'] = 'S';
                $subscription = func_array_merge($subscription, $data);

                $mail_smarty->assign('userinfo',  $userinfo);
                $mail_smarty->assign('subscription', $subscription);

                foreach ($order_data['products'] as $product) {
                    if ($product['productid'] == $subscription['productid']) {
                        $mail_smarty->assign('product', $product);

                        break;
                    }
                }

                func_send_mail(
                    $userinfo['email'],
                    'mail/xps_subscription_status_subj.tpl',
                    'mail/xps_subscription_status_customer.tpl',
                    $config['Company']['orders_department'],
                    false
                );

                func_send_mail(
                    $config['Company']['orders_department'],
                    'mail/xps_subscription_status_subj.tpl',
                    'mail/xps_subscription_status.tpl',
                    $config['Company']['orders_department'],
                    true
                );
            }

            func_array2update('xps_subscriptions', $data, "subscriptionid = '$subscription[subscriptionid]'");
        }
    }
}

if (!defined('X_INTERNAL_CRON'))
    exit(0);

?>
