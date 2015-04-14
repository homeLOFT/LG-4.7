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
 * Module functions
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Modules
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v11 (xcart_4_7_0), 2015-02-17 23:56:28, func.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if ( !defined('XCART_START') ) { header("Location: ../"); die("Access denied"); }

/**
 * Return plan array by product id
 *
 * @param int $productid product id
 *
 * @return array
 * @see    ____func_see____
 * @since  1.0.0
 */
function func_xps_getPlanByProductId($productid)
{
    global $sql_tbl;

    $subscription = func_query_first("SELECT subscription_product, type, number, period, reverse, fee, rebill_periods FROM $sql_tbl[xps_products] WHERE productid = '$productid'");

    if (empty($subscription)) {

        return false;
    }

    $subscription['short_desc'] = func_xps_getShortDesc($subscription);
    $subscription['desc'] = func_xps_getDesc($subscription);

    $subscription['next_date'] = func_xps_getNextDate(XC_TIME, $subscription);

    return $subscription;
}

/**
 * Return subscription array by order id
 *
 * @param int $orderid orderd id
 *
 * @return array
 * @see    ____func_see____
 * @since  1.0.0
 */
function func_xps_getSubscriptionsByOrderId($orderid)
{
    global $sql_tbl;

    $subscriptions = func_query_hash("SELECT * FROM $sql_tbl[xps_subscriptions] WHERE orderid = '$orderid'", 'subscriptionid', false);

    if (empty($subscriptions)) {
        $subscription = func_query_first("SELECT $sql_tbl[xps_subscriptions].* FROM $sql_tbl[xps_subscriptions] JOIN $sql_tbl[xps_orders] ON $sql_tbl[xps_subscriptions].subscriptionid = $sql_tbl[xps_orders].subscriptionid WHERE $sql_tbl[xps_orders].orderid = '$orderid'");

        if (empty($subscription)) {

            return array();
        }

        $subscriptions = array($subscription['subscriptionid'] => $subscription);
    }

    return $subscriptions;
}

/**
 * Generate short description by plan or subscription array
 *
 * @param array $subscription subscription or plan
 *
 * @return string
 * @see    ____func_see____
 * @since  1.0.0
 */
function func_xps_getShortDesc($subscription)
{
    $desc = '';

    if ($subscription['type'] == 'E') {
        if ($subscription['period'] == 'W') {
            $desc = func_get_langvar_by_name('lbl_xps_weekly');

        } elseif ($subscription['period'] == 'M') {
            $desc = func_get_langvar_by_name('lbl_xps_monthly');

        } elseif ($subscription['period'] == 'Y') {
            $desc = func_get_langvar_by_name('lbl_xps_annualy');
        }

    } elseif ($subscription['type'] == 'D') {
        if ($subscription['period'] == 'D') {
            if ($subscription['number'] == 1) {
                $desc = func_get_langvar_by_name('lbl_xps_daily');

            } else {
                $desc = func_get_langvar_by_name('lbl_xps_every__days', array('number' => $subscription['number']));
            }

        } elseif ($subscription['period'] == 'W') {
            if ($subscription['number'] == 1) {
                $desc = func_get_langvar_by_name('lbl_xps_weekly');

            } else {
                $desc = func_get_langvar_by_name('lbl_xps_every__weeks', array('number' => $subscription['number']));
            }

        } elseif ($subscription['period'] == 'M') {
            if ($subscription['number'] == 1) {
                $desc = func_get_langvar_by_name('lbl_xps_monthly');

            } else {
                $desc = func_get_langvar_by_name('lbl_xps_every__months', array('number' => $subscription['number']));
            }

        } elseif ($subscription['period'] == 'Y') {
            if ($subscription['number'] == 1) {
                $desc = func_get_langvar_by_name('lbl_xps_annualy');

            } else {
                $desc = func_get_langvar_by_name('lbl_xps_every__years', array('number' => $subscription['number']));
            }
        }
    }

    return $desc;
}

/**
 * Generate full description by plan or subscription array
 *
 * @param array $subscription subscription or plan
 *
 * @return string
 * @see    ____func_see____
 * @since  1.0.0
 */
function func_xps_getDesc($subscription)
{
    $desc = '';

    if ($subscription['type'] == 'E') {
        if ($subscription['period'] == 'W') {
            $week_days = array(
                func_get_langvar_by_name('lbl_xps_monday'),
                func_get_langvar_by_name('lbl_xps_tuesday'),
                func_get_langvar_by_name('lbl_xps_wednesday'),
                func_get_langvar_by_name('lbl_xps_thursday'),
                func_get_langvar_by_name('lbl_xps_friday'),
                func_get_langvar_by_name('lbl_xps_saturday'),
                func_get_langvar_by_name('lbl_xps_sunday'),
            );

            $week_day = $subscription['reverse'] == 'Y' ? $week_days[7 - $subscription['number']] : $week_days[$subscription['number'] - 1];
            $desc = func_get_langvar_by_name('lbl_xps_each_W', array('number' => $week_day));

        } elseif ($subscription['period'] == 'M') {
            $langvar = 'lbl_xps_each_M' . ($subscription['reverse'] == 'Y' ? '_R' : '');
            $desc = func_get_langvar_by_name($langvar, array('number' => $subscription['number']));

        } elseif ($subscription['period'] == 'Y') {
            $langvar = 'lbl_xps_each_Y' . ($subscription['reverse'] == 'Y' ? '_R' : '');
            $desc = func_get_langvar_by_name($langvar, array('number' => $subscription['number']));
        }

    } elseif ($subscription['type'] == 'D') {
        $desc = func_xps_getShortDesc($subscription);
    }

    return $desc;
}

/**
 * Return day start timestamp of given date
 *
 * @param int $time unix timestamp
 *
 * @return int
 * @see    ____func_see____
 * @since  1.0.0
 */
function func_xps_getDayStart($time)
{
    return mktime(0, 0, 0, date('n', $time), date('j', $time), date('Y', $time));
}

/**
 * Generate next bill date fot plan or subscription
 *
 * @param int      $checkDate    current timestamp of some date in futute to get next planned date after it
 * @param array    $subscription subscription or plan
 * @param boolean  $status       status of last bill
 *
 * @return int
 * @see    ____func_see____
 * @since  1.0.0
 */
function func_xps_getNextDate($checkDate, $subscription, $status = true)
{
    global $config;

    if (false === $status) {

        return func_xps_getDayStart($checkDate + ($config['XPayments_Subscriptions']['xps_rebill_attempt_period'] * SECONDS_PER_DAY));
    }

    $week_days = array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday');

    $_checkDate = $checkDate;
    $result = 0;

    if ('E' == $subscription['type']) {
        if ('W' == $subscription['period']) {
            $day_of_week = $subscription['reverse'] == 'Y' ? 8 - $subscription['number'] : $subscription['number'];
            $result = strtotime('next ' . $week_days[$day_of_week - 1], $checkDate);

        } elseif ('M' == $subscription['period']) {
            if ($subscription['number'] >= 1 || $subscription['number'] <= 31) {

                do {

                    $month_days = date('t', $checkDate);
                    $day_of_month = $subscription['reverse'] == 'Y' ? ($month_days + 1) - $subscription['number'] : $subscription['number'];

                    if ($day_of_month <= $month_days && $day_of_month >= 1) {
                        $result = mktime(0, 0, 0, date('n', $checkDate), $day_of_month, date('Y', $checkDate));

                        if ($result <= $_checkDate) {
                            $result = 0;
                        }
                    }

                    $checkDate += date('t', mktime(0, 0, 0, date('n', $checkDate), $month_days + 1, date('Y', $checkDate))) * SECONDS_PER_DAY;

                } while (!$result);
            }

        } elseif ('Y' == $subscription['period']) {
            if ($subscription['number'] >= 1 || $subscription['number'] <= 366) {

                do {

                    $year_days = 365 + date('L', $checkDate);
                    $day_of_year = $subscription['reverse'] == 'Y' ? ($year_days + 1) - $subscription['number'] : $subscription['number'];

                    if ($day_of_year <= $year_days && $day_of_year >= 1) {
                        $result = mktime(0, 0, 0, 1, 1, date('Y', $checkDate)) + ($day_of_year - 1) * SECONDS_PER_DAY;

                        if ($result <= $_checkDate) {
                            $result = 0;
                        }
                    }

                    $checkDate += $year_days * SECONDS_PER_DAY;

                } while (!$result);
            }
        }

    } elseif ('D' == $subscription['type']) {
        if ('D' == $subscription['period']) {
            $result = strtotime('+' . $subscription['number'] . ' days', $checkDate);

        } elseif ('W' == $subscription['period']) {
            $result = strtotime('+' . $subscription['number'] . ' weeks', $checkDate);

        } elseif ('M' == $subscription['period']) {
            $result = strtotime('+' . $subscription['number'] . ' months', $checkDate);

        } elseif ('Y' == $subscription['period']) {
            $result = strtotime('+' . $subscription['number'] . ' years', $checkDate);
        }
    }

    if ($result) {

        return func_xps_getDayStart($result);
    }

    return false;
}

/**
 * create subscriptions for given orderid and product
 *
 * @param int   $orderid initial order id for subscriptions
 * @param array $product product array from cart
 *
 * @return array products
 * @see    ____func_see____
 * @since  1.0.0
 */
function func_xps_createSubscriptions($orderid, $product)
{
    if (empty($product['subscription'])) {

        return $product;
    }

    $next_date = func_xps_getNextDate(XC_TIME, $product['subscription']);
    $subscription = array(
        'orderid' => $orderid,
        'productid' => $product['productid'],
        'fee' => $product['subscription']['fee'],
        'status' => 'N', // N - new, not listed in customer area
        'attempts' => 0,
        'rebill_periods' => $product['subscription']['rebill_periods'],
        'success_attempts' => 0,
        'next_date' => $next_date,
        'real_next_date' => $next_date,
        'type' => $product['subscription']['type'],
        'number' => $product['subscription']['number'],
        'period' => $product['subscription']['period'],
        'reverse' => $product['subscription']['reverse']
    );

    $ids = array();

    $amount = $product['amount'];
    while ($amount--) {
        $ids[] = $subscriptionid = func_array2insert('xps_subscriptions', $subscription);
        func_array2insert('xps_orders', array(
            'subscriptionid' => $subscriptionid,
            'orderid' => $orderid
        ));
    }

    $product['extra_data']['subscription'] = $product['subscription'];
    $product['extra_data']['subscription']['subscriptionids'] = $ids;

    return $product;
}

/**
 * start subscription by order
 *
 * @param array $order_data order data returned by func_order_data (include/func/func.order.php)
 *
 * @return viod
 * @see    ____func_see____
 * @since  1.0.0
 */
function func_xps_startOrderSubscriptions($order_data)
{
    $order = $order_data['order'];
    $products = $order_data['products'];

    if (!isset($order['extra']['xpc_txnid']) || empty($order['extra']['xpc_txnid'])) {

        return;
    }

    foreach ($products as $product) {
        if (isset($product['extra_data']['subscription']['subscriptionids']) && $product['extra_data']['subscription']['subscriptionids']) {
            $subscriptionids = $product['extra_data']['subscription']['subscriptionids'];

            func_array2update('xps_subscriptions', array(
                'status' => 'A' // A - active
            ), "subscriptionid IN ('" . implode("', '", $subscriptionids) . "')");
        }
    }
}

/**
 * return subscriptions which can be processed on given date
 *
 * @param int $time unix timestamp
 *
 * @return array
 * @see    ____func_see____
 * @since  1.0.0
 */
function func_xps_getActiveSubscriptions($time)
{
    global $sql_tbl;

    $time = func_xps_getDayStart($time);

    $subscriptions = func_query("SELECT * FROM $sql_tbl[xps_subscriptions] WHERE real_next_date = '$time' AND status = 'A' AND (rebill_periods = 0 OR rebill_periods > success_attempts)");

    return $subscriptions;
}

/**
 * clone initial order and store it with only one product
 *
 * @param array $subscription subscription
 *
 * @return array
 * @see    ____func_see____
 * @since  1.0.0
 */
function func_xps_placeOrder($subscription)
{
    global $sql_tbl, $active_modules;

    x_load('order');

    $order_data = func_order_data($subscription['orderid']);

    if (!isset($order_data['order']['extra']['xpc_txnid'])) {

        return array();
    }

    $xpc_txnid = $order_data['order']['extra']['xpc_txnid'];

    $products = $order_data['products'];
    $product = array();
    foreach ($products as $_product) {
        if (in_array($subscription['subscriptionid'], $_product['extra_data']['subscription']['subscriptionids'])) {
            $product = $_product;

            break;
        }
    }

    if (empty($product)) {

        return array();
    }

    $order = func_query_first("SELECT * FROM $sql_tbl[orders] WHERE orderid = '$subscription[orderid]'");

    unset($order['orderid']);
    $order['giftcert_discount'] = 0;
    $order['giftcert_ids'] = '';
    $order['coupon'] = '';
    $order['coupon_discount'] = 0;
    $order['shippingid'] = 0;
    $order['shipping'] = '';
    $order['shipping_cost'] = 0;
    $order['tax'] = 0;
    $order['taxes_applied'] = serialize(array());
    $order['tax_number'] = '';
    $order['tax_exempt'] = 'N';
    $order['payment_surcharge'] = 0;

    // ugly: it can throw error if there is product varians or options (if there is several product in one order with same productid)
    $order_details = func_query_first("SELECT * FROM $sql_tbl[order_details] WHERE orderid = '$subscription[orderid]' AND productid = '$subscription[productid]'");

    if (empty($order_details)) {

        return array();
    }

    unset($order_details['itemid']);
    $price = $subscription['fee'];
    $order_details['price'] = $price;

    $order['subtotal'] = $price;
    $order['total'] = $price;

    $order['date'] = XC_TIME;
    $order['status'] = 'I';

    $orderid = func_array2insert('orders', $order);

    $order_details['amount'] = 1;
    $order_details['orderid'] = $orderid;
    func_array2insert('order_details', $order_details);

    func_array2insert('xps_orders', array(
        'subscriptionid' => $subscription['subscriptionid'],
        'orderid' => $orderid
    ));

    if (!empty($active_modules['Advanced_Order_Management'])) {
        $details = array(
            'old_status' => '',
            'new_status' => 'I',
        );

        func_aom_save_history($orderid, 'X', $details);
    }

    return array(
        'orderid' => $orderid,
        'xpc_txnid' => $xpc_txnid
    );
}

/**
 * send transaction to XPayments
 *
 * @param int    $transaction_id initial transaction id
 * @param float  $amount         total amount of transaction
 * @param string $description    some description for new transaction
 *
 * @return array (status, response)
 * @see    ____func_see____
 * @since  1.0.0
 */
function func_xps_transact($transaction_id, $amount, $description)
{
    $data = array(
        'txnId' => $transaction_id,
        'amount' => $amount,
        'description' => $description
    );

    list ($status, $response) = xpc_api_request('payment', 'recharge', $data);

    return array($status, $response);
}

/**
 * return timeline for subscription (order dates and expected bill dates for future year)
 *
 * @param int    $transaction_id initial transaction id
 * @param float  $amount         total amount of transaction
 * @param string $description    some description for new transaction
 *
 * @return array
 * @see    ____func_see____
 * @since  1.0.0
 */
function func_xps_getSubscriptionDates($subscription)
{
    global $sql_tbl;

    $dates = array();

    $orders = func_query("SELECT $sql_tbl[orders].* FROM $sql_tbl[orders] JOIN $sql_tbl[xps_orders] ON $sql_tbl[orders].orderid = $sql_tbl[xps_orders].orderid WHERE $sql_tbl[xps_orders].subscriptionid = '$subscription[subscriptionid]'");

    if ($orders) {
        $tooltip_meassage = func_get_langvar_by_name('lbl_xps_click_to_view_related_order', NULL, false, true);
        foreach ($orders as $order) {
            if (in_array($order['status'], array('P', 'C'))) {
                $dates[date('Y-m-d', $order['date'])] = array('subscription-done', $tooltip_meassage, $order['orderid']);
            } else {
                $dates[date('Y-m-d', $order['date'])] = array('subscription-failed', $tooltip_meassage, $order['orderid']);
            }
        }
    }

    $checkDate = $subscription['next_date'];
    $dates[date('Y-m-d', $checkDate)] = array('subscription-pending');

    $oneYearAfter = strtotime('+1 year', $checkDate);
    while ($checkDate < $oneYearAfter) {
        $checkDate = func_xps_getNextDate($checkDate, $subscription);
        $dates[date('Y-m-d', $checkDate)] = array('subscription-pending');
    }

    return $dates;
}

/**
 * check if cart has subscription products
 *
 * @param array $cart cart
 *
 * @return boolean
 * @see    ____func_see____
 * @since  1.0.0
 */
function func_xps_hasCartSubscriptionProducts($cart)
{
    if (!empty($cart) && !empty($cart['products'])) {
        foreach ($cart['products'] as $product) {
            if (isset($product['subscription']) && $product['subscription']) {

                return true;
            }
        }
    }

    return false;
}

/**
 * filter payment methods
 *
 * @param array $payment_methods payment_methods
 *
 * @return array
 * @see    ____func_see____
 * @since  1.0.0
 */
function func_xps_filterPaymentMethods($payment_methods)
{
    global $config;

    $filtered = array();

    func_xpay_func_load();
    $pp_processor = xpc_get_paypal_dp_processor($config['paypal_solution']);

    // PayPal working through XPayments
    $pp_xp = (
        false != $pp_processor['use_xpc']
        && !empty($pp_processor['processor'])
        && is_array($pp_processor['processor'])
    );

    foreach ($payment_methods as $method) {
        if (
            (
                ($method['processor'] == 'cc_xpc.php' || ($method['processor_file'] == 'ps_paypal_pro.php' && $pp_xp))
                && (!isset($method['use_recharges']) || $method['use_recharges'] == 'Y')
            )
            || $method['payment_script'] == 'payment_xpc_recharge.php'
        ) {
            $filtered[] = $method;
        }
    }

    return $filtered;
}

/**
 * calculatet fees for products in cart (calculate_single)
 *
 * @param array $products cart products
 *
 * @return array products
 * @see    ____func_see____
 * @since  1.0.1
 */
function func_xps_calculateProducts($products)
{
    foreach ($products as $k => $v) {
        if (isset($v['subscription'])) {

            if ($v['subscription']['subscription_product'] != 'Y') {
                unset($products[$k]['subscription']);

                continue;
            }

            $products[$k]['price'] += $v['subscription']['fee'];
            $products[$k]['display_price'] += $v['subscription']['fee'];

            $fee = $v['subscription']['fee'] * $v['amount'];

            $products[$k]['discounted_price'] += $fee;
            $products[$k]['display_discounted_price'] += $fee;
            $products[$k]['subtotal'] += $fee;
            $products[$k]['display_subtotal'] += $fee;
        }
    }

    return $products;
}

/**
 * attach subscription plan info to product array (internal function)
 *
 * @param array $product cart product
 *
 * @return array (product)
 * @see    ____func_see____
 * @since  1.0.1
 */
function func_xps__attachPlanToProduct($product, $calculate_price = false, $remove_plan = false)
{
    $subscription = func_xps_getPlanByProductId($product['productid']);
    if ($subscription && $subscription['subscription_product'] == 'Y') {
        $product['subscription'] = $subscription;

        if ($calculate_price) {
            $product['price'] = $product['price'] + $subscription['fee'];
            $product['taxed_price'] = $product['taxed_price'] + $subscription['fee'];
        }

    } elseif ($remove_plan && isset($product['subscription'])) {
        unset($product['subscription']);
    }

    return $product;
}

/**
 * attach subscription plan info to product array (products_from_scratch)
 *
 * @param array $product cart product
 *
 * @return array (product)
 * @see    ____func_see____
 * @since  1.0.1
 */
function func_xps_attachPlanToCartProduct($product)
{
    return func_xps__attachPlanToProduct($product, false, true);
}

/**
 * attach subscription plan info to product array (search_products)
 *
 * @param array $product product
 *
 * @return array (product)
 * @see    ____func_see____
 * @since  1.0.1
 */
function func_xps_attachPlanToProduct($product)
{
    global $current_area;

    return func_xps__attachPlanToProduct($product, in_array($current_area, array('C', 'B')));
}

/**
 * attach subscription plan info to product array (wishlist)
 *
 * @param array $product product
 *
 * @return array (product)
 * @see    ____func_see____
 * @since  1.0.1
 */
function func_xps_attachPlanToWishlistProduct($product)
{
    return func_xps__attachPlanToProduct($product);
}

/**
 * prepare query for orders search
 *
 * @param array  $data             search request data
 * @param string $search_from      FROM part of query
 * @param string $search_condition WHERE part of query (also with GROUP)
 *
 * @return array (search_from, search_condition)
 * @see    ____func_see____
 * @since  1.0.1
 */
function func_xps_prepareSearchOrdersQueryParts($data, $search_from, $search_condition)
{
    global $sql_tbl;

    if (!empty($data['subscriptions_orders']) && $data['subscriptions_orders'] != 'A') {
        if ($data['subscriptions_orders'] == 'S') {
            $search_from .= " INNER JOIN $sql_tbl[xps_orders] ON $sql_tbl[orders].orderid = $sql_tbl[xps_orders].orderid ";

        } elseif ($data['subscriptions_orders'] == 'N') {
            $search_condition = " AND $sql_tbl[xps_orders].subscriptionid IS NULL " . $search_condition;
            $search_from .= " LEFT JOIN $sql_tbl[xps_orders] ON $sql_tbl[orders].orderid = $sql_tbl[xps_orders].orderid ";
        }

    } elseif (!empty($data['subscriptionid'])) {
        $search_from .= "INNER JOIN $sql_tbl[xps_orders] ON $sql_tbl[orders].orderid = $sql_tbl[xps_orders].orderid AND $sql_tbl[xps_orders].subscriptionid = '$data[subscriptionid]'";
    }

    return array($search_from, $search_condition);
}

/**
 * prepare query for orders search
 *
 * @param integer $orderid         order id
 * @param integer $subscriptionid  subscription id
 * @param string  $status          new status (A for active or S for stopped)
 *
 * @return boolean
 * @see    ____func_see____
 * @since  1.0.1
 */
function func_xps_updateSubscriptionStatus($orderid, $subscriptionid, $status)
{
    $subscriptions = func_xps_getSubscriptionsByOrderId($orderid);
    $subscription = isset($subscriptions[$subscriptionid]) ? $subscriptions[$subscriptionid] : array();

    if (!empty($subscription) && $subscription['status'] != $status) {
        if ($status == 'S') {
            $data = array(
                'status' => 'S',
            );

        } elseif ($status == 'A') {
            $current_date = func_xps_getDayStart(XC_TIME);
            $check_date = $subscription['next_date'];

            while ($check_date <= $current_date) {
                $check_date = func_xps_getNextDate($check_date, $subscription);
            }

            $data = array(
                'status' => 'A',
                'attempts' => 0,
                'next_date' => $check_date,
                'real_next_date' => $check_date,
            );
        }

        if (isset($data)) {
            func_array2update('xps_subscriptions', $data, "subscriptionid = '$subscriptionid'");

            $subscription = func_array_merge($subscription, $data);

            global $config, $mail_smarty;

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

            $mail_smarty->assign('by_admin', 'Y');

            func_send_mail(
                $userinfo['email'],
                'mail/xps_subscription_status_subj.tpl',
                'mail/xps_subscription_status_customer.tpl',
                $config['Company']['users_department'],
                false
            );

            return true;
        }
    }

    return false;
}

/**
 * modifu product subsctiption data
 *
 * @param integer $productid    product id
 * @param integer $subscription subscription info
 * @param string  $gid          optional
 * @param string  $xps_firlds   optionsl
 *
 * @return void
 * @see    ____func_see____
 * @since  1.0.1
 */
function func_xps_productModify($productid, $subscription, $geid = false, $xps_fields = array())
{
    $is_subscription = isset($subscription['subscription_product']) ? 'Y' : 'N';

    $query_data = array(
        'subscription_product' => $is_subscription,
        'type' => ($is_subscription == 'Y' && in_array($subscription['type'], array('E', 'D'))) ? $subscription['type'] : 'E',
        'number' => $is_subscription == 'Y' ? intval($subscription['number']) : 1,
        'period' => ($is_subscription == 'Y' && in_array($subscription['period'], array('D', 'W', 'M', 'Y'))) ? $subscription['period'] : 'W',
        'reverse' => ($is_subscription == 'Y' && isset($subscription['reverse'])) ? 'Y' : 'N',
        'fee' => $is_subscription == 'Y' ? floatval($subscription['fee']) : 0,
        'rebill_periods' => $is_subscription == 'Y' ? intval($subscription['rebill_periods']) : 0
    );

    if (!func_xps_getPlanByProductId($productid)) {
        func_array2insert('xps_products', array_merge($query_data, array('productid' => $productid)));

    } else {
        func_array2update('xps_products', $query_data, "productid = '$productid'");
    }

    if ($geid && !empty($xps_fields['subscription'])) {
        $query_data = array();
        $update_all_selected = false;

        if (isset($xps_fields['subscription']['billing_period'])) {
            $query_data['type'] = in_array($subscription['type'], array('E', 'D')) ? $subscription['type'] : 'E';
            $query_data['number'] = $subscription['number'];
            $query_data['period'] = in_array($subscription['period'], array('D', 'W', 'M', 'Y')) ? $subscription['period'] : 'W';
            $query_data['reverse'] = isset($subscription['reverse']) ? 'Y' : 'N';
        }

        if (isset($xps_fields['subscription']['fee'])) {
            $query_data['fee'] = $subscription['fee'];
        }

        if (isset($xps_fields['subscription']['rebill_periods'])) {
            $query_data['rebill_periods'] = $subscription['rebill_periods'];
        }

        if (isset($xps_fields['subscription']['subscription_product'])) {
            $query_data['subscription_product'] = $is_subscription;
            $update_all_selected = true;

            if ($is_subscription != 'Y') {
                $query_data['type'] = 'E';
                $query_data['number'] = 0;
                $query_data['period'] = 'W';
                $query_data['reverse'] = 'N';
                $query_data['fee'] = 0;
                $query_data['rebill_periods'] = 0;
            }
        }

        while ($pid = func_ge_each($geid, 1, $productid)) {
            if ($update_all_selected) {
                if (!func_xps_getPlanByProductId($pid)) {
                    func_array2insert('xps_products', array_merge($query_data, array('productid' => $pid)));

                } else {
                    func_array2update('xps_products', $query_data, "productid = '$pid'");
                }

            } else {
                func_array2update('xps_products', $query_data, "productid = '$pid' AND subscription_product = 'Y'");
            }
        }
    }
}
