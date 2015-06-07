<?php
/* vim: set ts=4 sw=4 sts=4 et: */
/*****************************************************************************\
+-----------------------------------------------------------------------------+
| X-Cart Software license agreement                                           |
| Copyright (c) 2001-2013 Qualiteam software Ltd <info@x-cart.com>            |
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
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Modules
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2013 Qualiteam software Ltd <info@x-cart.com>. All rights reserved
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    $Id$
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if (!defined('XCART_START')) {
    header('Location: ../../');
    die('Access denied');
}

function func_abcr_save_abandoned_cart($user_id, $raw_cart) 
{
    global $sql_tbl;

    if (defined('IS_ADMIN_USER')) {
        return false;
    }

    // Gather userinfo
    $userinfo = func_userinfo($user_id, 'C');

    if (isset($raw_cart['userinfo'])) {
        $userinfo = func_array_merge($userinfo, $raw_cart['userinfo']);
    }

    // Prepare $userinfo and $cart
    $userinfo       = func_abcr_prepare_userinfo($userinfo);
    $abandoned_cart = func_abcr_prepare_cart_to_db($raw_cart);

    // If cart is empty or email is not specified, do not need to save cart 
    if (
        empty($abandoned_cart)
        || empty($userinfo['email'])
    ) {
        return false;
    }

    $cart_hash = func_query_first_cell('SELECT cart_hash FROM ' . $sql_tbl['abcr_abandoned_carts'] . ' WHERE email="' . $userinfo['email'] . '"');

    if (empty($cart_hash)) {
        $cart_hash = func_abcr_generate_cart_hash();
    }

    $data = array (
        'email'             => addslashes($userinfo['email']),
        'cart_hash'         => $cart_hash,
        'userid'            => $userinfo['id'],
        'customer_info'     => addslashes(serialize($userinfo)),
        'abandoned_cart'    => addslashes(serialize($abandoned_cart)),
        'time'              => time(),
        'coupon'            => '',
    );

    func_array2insert($sql_tbl['abcr_abandoned_carts'], $data, true);

    return true;
}

function func_abcr_get_abandoned_cart($email) 
{
    global $sql_tbl;

    $email = addslashes($email);
    
    $return = func_query_first('SELECT * FROM ' . $sql_tbl['abcr_abandoned_carts'] . ' WHERE email="' . $email . '"');

    if (!empty($return)) {
        $notification_stats = func_abcr_get_notifications_stats($email);
        $return = func_array_merge($return, $notification_stats);
        $return = func_abcr_postprocess_cart_from_db($return);
    } else {
        $return = false;
    }

    return $return;
}

function func_abcr_get_notifications_stats($email) {
    global $sql_tbl;

    $return = func_query_first('SELECT MAX(time) AS notification_time, COUNT(*) AS notification_count FROM ' . $sql_tbl['abcr_notifications'] . ' WHERE email="' . $email . '"');
    
    return $return;
}

function func_abcr_postprocess_cart_from_db($cart) 
{
    global $sql_tbl;

    $return = $cart;

    $return['abandoned_cart'] = unserialize(func_stripslashes($return['abandoned_cart']));
    $return['customer_info'] = unserialize(func_stripslashes($return['customer_info']));

    // Backward compatibility - check if provider fields are missing
    foreach ($return['abandoned_cart']['products'] as $k => $product) {
        if (empty($product['provider'])) {
            $return['abandoned_cart']['products'][$k]['provider'] = func_query_first_cell("SELECT provider FROM $sql_tbl[products] WHERE productid='$product[productid]'");
        }
    }

    if (!empty($return['coupon'])) {
        $return['coupon'] = func_abcr_get_coupon($return['coupon']);
    }

    $return['coupon_data'] = $return['coupon'];
    unset($return['coupon']);

    $return['return_link'] = func_abcr_generate_return_link($return);

    return $return;
}

function func_abcr_delete_abandoned_cart($email)
{
    global $sql_tbl;

    if (
        empty($email)
        || !is_string($email) 
    ) {
        return false;
    }

    $email = addslashes($email);

    $coupons = func_query_column('SELECT coupon FROM ' . $sql_tbl['abcr_notifications'] . ' WHERE email="' . $email . '"');

    db_query('DELETE FROM ' . $sql_tbl['abcr_abandoned_carts'] . ' WHERE email="' . $email . '"');
    db_query('DELETE FROM ' . $sql_tbl['abcr_notifications'] . ' WHERE email="' . $email . '"');
    db_query('DELETE FROM ' . $sql_tbl['discount_coupons'] . ' WHERE coupon IN ("' . implode('","', $coupons) . '")');

    return true;
}

function func_abcr_get_userinfo_schema($name = '')
{
    global $config;

    $is_compound = 'YES';

    $schemas = array (

        'generic' => array(
            'language' => $config['default_customer_language'],
            'email' => '',
            'login' => '',
            'id' => 0,
            'membershipid' => 0,
            'title' => '',
            'firstname' => '',
            'lastname' => '',
            'company' => '',
            'address' => '',
        ),

        'billing' => array(
            'title' => '',
            'firstname' => '',
            'lastname' => '',
            'address' => '',
            'city' => '',
            'county' => '',
            'state' => '',
            'country' => '',
            'zipcode' => '',
            'zip4' => '',
            'phone' => '',
            'fax' => '',
        ),

        'order' => array(
            'email' => '',
            'membershipid' => 0,
            'title' => '',
            'firstname' => '',
            'lastname' => '',
            'company' => '',
        ),
    );

    $schemas['shipping'] = $schemas['billing'];

    $schemas['complete_userinfo'] = $is_compound;
    $complete_userinfo_schema = array(
        array('name' => 'generic', 'prefix' => ''),
        array('name' => 'billing', 'prefix' => 'b_'),
        array('name' => 'shipping', 'prefix' => 's_'),
    );

    $schemas['complete_order'] = $is_compound;
    $complete_order_schema = array(
        array('name' => 'order', 'prefix' => ''),
        array('name' => 'billing', 'prefix' => 'b_'),
        array('name' => 'shipping', 'prefix' => 's_'),
    );

    $return = array();

    if (!empty($name)) {

        if (!isset($schemas[$name])) {

            $return = false;

        } elseif ($schemas[$name] === $is_compound) {

            $compound_schema = $name . '_schema';

            foreach ($$compound_schema as $s) {

                $child_schema = $schemas[$s['name']];

                foreach ($child_schema as $field => $default_value) {

                    $return[$s['prefix'] . $field] = $default_value;

                }

            }

        } elseif (is_array($schemas[$name])) {

            $return  = $schemas[$name];

        }

    } else {
        
        $return = $schemas; 

    }

    return $return;
}

function func_abcr_prepare_userinfo($userinfo) 
{
    $return = array();

    $userinfo_schema = func_abcr_get_userinfo_schema('complete_userinfo');

    $replacement_schema = array (
        'firstname' => array('b_firstname', 's_firstname'),
        'lastname' => array('b_lastname', 's_lastname'),
    );

    foreach ($userinfo_schema as $field => $default_value) {

        if (isset($userinfo[$field])) {
            $return[$field] = $userinfo[$field];
        } else {
            $return[$field] = $default_value;
        } 

    }

    foreach ($replacement_schema as $search => $replace) {

        if (empty($return[$search])) {

            foreach ($replace as $variant) {

                if (!empty($userinfo[$variant])) {
                    $return[$search] = $userinfo[$variant];
                    break 1;
                }

            }

        }
        
    }

    return $return;
}

// Prepare global $cart to be saved into the database
function func_abcr_prepare_cart_to_db($cart)
{
    $return = array();

    $products = (isset($cart['products'])) ? $cart['products'] : array();

    foreach ($products as $product) {
        $current_product = array (
            'productid' => $product['productid'],
            'amount' => $product['amount'],
            'provider' => $product['provider'],
        );

        if (
            isset($product['product_options'])
            && is_array($product['product_options'])
        ) {
            
            $current_options = array();

            foreach ($product['product_options'] as $option) {

                if ($option['is_modifier'] == 'T' || $option['is_modifier'] == 'A') {
                    $current_options[$option['classid']] = $option['option_name'];
                } else {
                    $current_options[$option['classid']] = $option['optionid'];
                }

            }

            $current_product['options'] = $current_options;
        }

        $return['products'][] = $current_product;
    }
 
    return $return;
}

function func_abcr_search_abandoned_carts($params = array(), $count_only = false)
{
    global $sql_tbl;

    $param_defaults = array(
        'start'         => 0,
        'end'           => PHP_INT_MAX,
        'pattern'       => '',
        'offset_params' => array(),
    );

    // kind of extract($params)
    foreach ($param_defaults as $name => $value) {

        if (isset($params[$name])) {
            $$name = $params[$name];
        } else {
            $$name = $value;
        }

    }

    $return = array();

    $fields       = array();
    $from_tbls    = array();
    $left_joins   = array();
    $where        = array();
    $groupbys     = array();
    $orderbys     = array();

    $from_tbls[] = $sql_tbl['abcr_abandoned_carts'] . ' AS abcr_carts';

    if ($count_only) {
        $fields[] = 'COUNT(*)';
    } else {
        $fields[] = 'abcr_carts.*';
        $fields[] = $sql_tbl['customers'] . '.login';
    }

    $left_joins[$sql_tbl['customers']] = array(
        'on' => $sql_tbl['customers'] . '.id = abcr_carts.userid',  
    );

    $where[] = 'abcr_carts.time <= ' . $end;
    $where[] = 'abcr_carts.time > ' . $start;

    $orderbys[] = 'abcr_carts.time DESC';

    if (!empty($pattern)) {
        $where[] = '(abcr_carts.email LIKE "%' . $pattern . '%" OR ' . $sql_tbl['customers'] . '.login LIKE "%' . $pattern . '%")';
    }

    // start building query

    $query =    'SELECT ' . implode(', ', $fields) . ' ' 
                . 'FROM ' . implode(', ', $from_tbls) . ' ';

    foreach ($left_joins as $db => $join_params) {

        $query .= 'LEFT JOIN ' . $db . ' ';

        if (isset($join_params['on'])) {
            $query .= 'ON ' . $join_params['on'] . ' ';
        }

    }

    $query .= 'WHERE ' . implode(' AND ', $where) . ' ';

    if (!empty($groupbys)) {
        $query .= 'GROUP BY ' . implode(', ', $groupbys) . ' ';
    }

    if (!empty($orderbys)) {
        $query .= 'ORDER BY ' . implode(', ', $orderbys) . ' ';
    }

    if (
        !empty($offset_params)
        && isset($offset_params['start'])
        && is_int($offset_params['start'])
        && isset($offset_params['how_many'])
        && is_int($offset_params['how_many'])
    ) {
        $query .= 'LIMIT ' . $offset_params['start'] . ', ' . $offset_params['how_many'];    
    }

    // query is created

    if ($count_only) {

        $return = func_query_first_cell($query);

    } else {

        $return = array();

        $db_result = db_query($query);

        if ($db_result) {

            while ($cart = db_fetch_array($db_result)) {

                #TODO: probably need to merge this call into MySQL query
                $notification_stats = func_abcr_get_notifications_stats($cart['email']);
                $cart = func_array_merge($cart, $notification_stats);

                $return[] = func_abcr_postprocess_cart_from_db($cart);

            }

        }

        db_free_result($db_result);
    }

    return $return;
}

function func_abcr_generate_return_link($cart)
{
    global $http_location;

    if (
        isset($cart['email'])
        && isset($cart['cart_hash'])
    ) {
        $cart_skey = $cart['email'] . '%' . $cart['cart_hash'] . '%';

        if (isset($cart['coupon']['coupon'])) {
            $cart_skey .= $cart['coupon']['coupon'];
        }

        return $http_location . DIR_CUSTOMER . '/abandoned_cart_in.php?cart_skey=' . urldecode(base64_encode($cart_skey));

    } else {
        return $http_location . DIR_CUSTOMER . '/home.php';
    }
}

function func_abcr_send_message_to_abandoned_cart($cart)
{
    global $mail_smarty, $config, $sql_tbl;

    $products = func_abcr_get_products($cart['abandoned_cart']['products'], $cart['customer_info']['membershipid'], true);

    if (empty($products)) {
        return false;
    }

    $notification_data = array(
        'email' => addslashes($cart['email']),
        'coupon' => '',
        'time' => time(),
    );

    x_load('order');

    $mail_smarty->assign('products', func_translate_products($products, $cart['customer_info']['language']));
    $mail_smarty->assign('customer', $cart['customer_info']);
    $mail_smarty->assign('return_link', $cart['return_link']);

    if (is_array($cart['coupon_data'])) {
        $notification_data['coupon'] = $cart['coupon_data']['coupon'];
        $mail_smarty->assign('coupon', $cart['coupon_data']);
    } else {
        $mail_smarty->clear_assign('coupon');
    }

    func_array2insert('abcr_notifications', $notification_data);

    x_load('mail');

    func_send_mail (
        $cart['email'],
        'mail/abandoned_cart_notification_subj.tpl',
        'mail/abandoned_cart_notification.tpl',
        $config['Company']['orders_department'], 
        false
    );

    return true;
}

// Returns first found provider in the cart
function func_abcr_detect_cart_provider($cart)
{

    global $sql_tbl;

    $return = 1; // Default provider/admin ID
    if (!empty($cart['abandoned_cart']['products'])) {
        $product = array_shift($cart['abandoned_cart']['products']);
        $return = $product['provider'];
    }
    return $return;
}

function func_abcr_create_coupon($email, $coupon_settings, $coupon_provider = 1)
{
    global $config, $sql_tbl;

    if (
        !is_array($coupon_settings)
        || ($coupon_settings['type'] != 'free_ship' && $coupon_settings['value'] <= 0)
    ) {
        return;
    }
    
    $coupon_name = func_abcr_generate_coupon_name();
    $expiration_time = $coupon_settings['expire'];

    $coupon_data = array (
        'coupon'              => $coupon_name,
        'discount'            => $coupon_settings['value'],
        'coupon_type'         => $coupon_settings['type'],
        'minimum'             => 0,
        'times'               => 1,
        'per_user'            => 'N',
        'expire'              => $coupon_settings['expire'],
        'status'              => 'A',
        'provider'            => $coupon_provider,
        'productid'           => 0,
        'categoryid'          => 0,
        'recursive'           => 'N',
        'apply_category_once' => 'N',
        'apply_product_once'  => 'N',
    );

    func_array2insert('discount_coupons', $coupon_data);

    db_query('UPDATE ' . $sql_tbl['abcr_abandoned_carts'] . ' SET coupon="' . $coupon_name . '" WHERE email="' . $email . '"');

    return $coupon_data;
}

function func_abcr_generate_random_name($length)
{
    $length = intval($length);

    $alphabet = array_merge(
                    range('a', 'z'),
                    range('A', 'Z'),
                    range('0', '9')
                );

    $alphabet_length = count($alphabet);

    $return = array();

    for ($i = 0; $i < $length; $i++) {
        $n = rand(0, $alphabet_length - 1);
        $return[$i] = $alphabet[$n];
    }

    $return = implode($return);
    
    return $return;
}

function func_abcr_generate_coupon_name()
{
    global $sql_tbl;

    $is_exist = true;

    while ($is_exist) {
        $coupon = func_abcr_generate_random_name(8);
        $result = func_query('SELECT * FROM ' . $sql_tbl['discount_coupons'] . ' WHERE coupon="' . $coupon . '"');

        if (empty($result)) {
            $is_exist = false;
        }
    }

    return $coupon;
}

function func_abcr_generate_cart_hash()
{
    global $sql_tbl;

    $is_exist = true;

    while ($is_exist) {
        $cart_hash = func_abcr_generate_random_name(24);
        $result = func_query('SELECT * FROM ' . $sql_tbl['abcr_abandoned_carts'] . ' WHERE cart_hash="' . $cart_hash . '"');

        if (empty($result)) {
            $is_exist = false;
        }
    }

    return $cart_hash;    
}

function func_abcr_get_coupon($coupon)
{
    global $sql_tbl;

    if (empty($coupon)) {
        return false;
    }

    $return = func_query_first('SELECT * FROM ' . $sql_tbl['discount_coupons'] . ' WHERE coupon="' . $coupon . '"');

    if (empty($return)) {
        $return = false;
    }

    return $return;
}

function func_abcr_get_products($products, $membershipid, $return_only_available = false)
{
    global $active_modules;
    global $config, $http_location;
    global $single_mode, $login_type, $logged_userid;

    x_load('product');

    $return = array();

    if (is_array($products)) {

        foreach ($products as $product) {

            if (
                !$single_mode
                && !empty($logged_userid)
                && $login_type == 'P'
                && $product['provider'] != $logged_userid
            ) {
                // Product from another provider
                $product2return = array (
                    'product'       => func_get_langvar_by_name('lbl_abcr_not_owned_product', NULL, false, true),
                    'productid'     => $product['productid'],
                    'provider'      => $product['provider'],
                    'price'         => 0,
                    'amount'        => 1,
                    'options'       => array(),
                    'options_short' => array(),
                    'url'           => '',
                    'weight'        => 0,
                    'avail'         => 1,
                    'forsale'       => 'Y', 
                    'productcode'   => '', 
                    'product_type'  => '',
                    'images'        => array(),
                    'not_owned'     => true,
                );

                $return[] = $product2return;

                continue;
            }

            $product_info = func_select_product($product['productid'], $membershipid, false);
            $price = $product_info['price'];

            $options = $options_short = array();

            if (
                !empty($active_modules['Product_Options'])
                && isset($product['options'])
                && is_array($product['options'])
            ) {

                list($variant, $options) = func_get_product_options_data($product['productid'], $product['options'], $membershipid);

                // Variant handling
                if (
                    isset($variant)
                    && is_array($variant)
                ) {
                    $price = $variant['price'];

                    $product_info['avail']          = $variant['avail'];
                    $product_info['productcode']    = $variant['productcode'];
                    $product_info['weight']         = $variant['weight'];
                }

                // Options handling
                if (
                    isset($options)
                    && is_array($options)
                ) {

                    foreach ($options as $option) {

                        if ('Y' == $option['is_modifier']) { 
                            if ('$' == $option['modifier_type']) {
                                $price += $option['price_modifier'];
                            } elseif ('%' == $option['modifier_type']) {
                                $price += $option['price_modifier'] * $price / 100;
                            }
                        }

                    }

                } // end Options handling

                $options_short = $product['options'];
            }

            if (
                !$return_only_available
                || (
                    $product_info['forsale'] == 'Y'
                    && (
                        $config['General']['unlimited_products'] == 'Y'
                        || $product_info['avail'] >= $product['amount']
                    )
                )
            ) {            

                if ($config['SEO']['clean_urls_enabled'] == 'Y') {
                    x_load('clean_urls');
                    $url = func_clean_url_get('P', $product['productid']);
                } else {
                    $url = $http_location . DIR_CUSTOMER . '/product.php?productid=' . $product['productid'];
                }

                $product2return = array (
                    'price'         => $price,
                    'amount'        => $product['amount'],
                    'options'       => $options,
                    'options_short' => $options_short,
                    'url'           => $url,
                );

                $product_info_fields_to_return = array (
                    'weight', 
                    'provider', 
                    'product', 
                    'avail',
                    'forsale', 
                    'productcode', 
                    'product_type',
                    'productid',
                    'images',
                );

                foreach ($product_info_fields_to_return as $field) {

                    $product2return[$field] = $product_info[$field];

                }

                $return[] = $product2return;

            }

        } // end foreach ($products as $product)

    }

    return $return;
}

function func_abcr_is_daily_task()
{
    global $sql_tbl;
    global $config;

    $return = false;
    $now = time();

    if ($config['abcr_daily_task_time'] < ($now - 3 * ABCR_SECONDS_PER_HOUR)) {
        $return = true;
        db_query('UPDATE ' . $sql_tbl['config'] . ' SET value="' . $now . '" WHERE name="abcr_daily_task_time"');
    }

    return $return;
}

function func_abcr_is_valid_request($request)
{
    $parsed_request = func_abcr_parse_cart_request($request);

    if (false == $parsed_request) {
        return false;
    }

    $check_cart = func_abcr_get_abandoned_cart($parsed_request['email']);

    if (
        false !== $check_cart 
        && $check_cart['cart_hash'] == $parsed_request['cart_hash']
    ) {
        return true;
    } else {
        return false;
    }
}

function func_abcr_parse_cart_request($request)
{
    $request = base64_decode($request);

    if (false == $request) {
        return false;
    }

    list($email, $cart_hash, $coupon) = explode('%', $request);

    return array('email' => $email, 'cart_hash' => $cart_hash, 'coupon' => $coupon);
}

function func_abcr_populate_cart($products) { //{{{

    x_load('cart');

    global $cart;

    foreach ($products as $p) {

        $product = array(
            'productid' => $p['productid'],
            'amount' => $p['amount'],
            'product_options' => isset($p['options']) ? $p['options'] : array(),
            'price' => null,
        );

        $result = func_add_to_cart($cart, $product);

    }

    list($cart, $products) = func_generate_products_n_recalculate_cart();

    return $cart;

} //}}}

function func_abcr_apply_coupon($cart, $coupon_name)
{
    $cart['discount_coupon'] = $coupon_name;
    return $cart;
}

function func_abcr_create_coupon_settings($type = null, $value = null, $expire = null)
{
    global $config;

    $return = null;

    $allowable_coupon_types = array (
        'absolute', 
        'percent',
        'free_ship',
    );

    if (
        is_null($type)
        && is_null($value)
        && is_null($expire)
        && in_array($config['Abandoned_Cart_Reminder']['abcr_coupon_type'], $allowable_coupon_types) 
    ) {

        /*
            If all input params are ommitted AND coupon type in Global settings is correct,
            then return default params of coupon specified in Global settings.
            In this case, we assume that function was called to get default params of coupon
        */

        $return = array (
            'type'      => $config['Abandoned_Cart_Reminder']['abcr_coupon_type'],
            'value'     => (float) $config['Abandoned_Cart_Reminder']['abcr_coupon_value'],
            'expire'    => time() + $config['Abandoned_Cart_Reminder']['abcr_expire_after'] * SECONDS_PER_DAY,
        );

    } elseif (
        in_array($type, $allowable_coupon_types)
        && is_numeric($value)
        && ($type == 'free_ship' || $value > 0)
        && is_numeric($expire)
    ) {
        
        // If all input params are correct then wrap it into array and return
        
        $return = array (
            'type'      => $type,
            'value'     => (float) $value,
            'expire'    => (int) $expire,
        );

    }

    if ($return['type'] == 'free_ship') {
        $return['value'] = 0;
    }

    return $return;
}

function func_abcr_create_order($abcr_cart)
{
    global $active_modules;
    global $sql_tbl;

    if (empty($active_modules['Advanced_Order_Management'])) {
        return null;
    }

    x_load('order');

    if (
        intval($abcr_cart['userid']) > 0
    ) {
        $userid = intval($abcr_cart['userid']);
    } else {
        $userid = false;
    }

    $orderid = func_aom_create_new_order($userid);

    // Populating xcart_orders
    $cart_products = func_abcr_get_products($abcr_cart['abandoned_cart']['products'], $abcr_cart['customer_info']['membershipid']);
    $order_data = array();

    $order_data['subtotal'] = $order_data['total'] = func_abcr_get_products_subtotal($cart_products);

    if (false == $userid) {

        $order_data_schema = func_abcr_get_userinfo_schema('complete_order');
        unset($order_data_schema['address']);
        $allowable_order_data = array_keys($order_data_schema);

        $userid = func_query_first_cell('SELECT userid FROM ' . $sql_tbl['orders'] . ' WHERE orderid="' . $orderid . '"');

        $user_data_schema = func_abcr_get_userinfo_schema('generic');
        unset($user_data_schema['address']);
        $allowable_user_data = array_keys($user_data_schema);

        foreach ($abcr_cart['customer_info'] as $name => $value) {

            if (
                !empty($value)
                && in_array($name, $allowable_order_data)
            ) {
                $order_data[$name] = $value;
            }

            if (
                !empty($value)
                && in_array($name, $allowable_user_data)
            ) {
                $user_data[$name] = $value;
            }

        }

        func_array2update (
            'orders',
            $order_data,
            'orderid="' . $orderid . '"'
        );

        func_array2update (
            'customers',
            $user_data,
            'id="' . $userid . '"'
        );        
    }

    func_array2update(
        'orders',
        $order_data,
        'orderid="' . $orderid . '"'
    );

    // Populating xcart_order_details
    foreach ($cart_products as $p) {

        $extra = array(
            'product_options'   => '',
            'taxes'             => '',
            'display'           => array(
                'price'             => $p['price'],
                'discounted_price'  => $p['price'],
                'subtotal'          => $p['price'],
                ), 
            'subtotal'          => $p['price'],
            'weight'            => $p['weight'],
        );

        if (
            !empty($active_modules['Product_Options'])
            && isset($p['options_short'])
        ) { 
            $extra['product_options'] = $p['options_short'];
            $product_options = func_serialize_options($p['options']);
        } else {
            $product_options = '';
        }

        $extra['display'] = array(
            'price'             => $p['price'],
            'discounted_price'  => $p['price'],
            'subtotal'          => $p['price'],
        );

        func_array2insert(
            'order_details',
            array (
                'orderid'         => $orderid,
                'productid'       => $p['productid'],
                'product'         => addslashes($p['product']),
                'product_options' => addslashes($p['product_options']),
                'amount'          => $p['amount'],
                'price'           => $p['price'],
                'provider'        => addslashes($p['provider']),
                'extra_data'      => addslashes(serialize($extra)),
                'productcode'     => addslashes($p['productcode']),
            )
        );

        $p['options'] = $p['options_short'];

        func_update_quantity(array($p), false);

    } // end foreach ($abcr_cart['abandoned_cart']['products'])

    return $orderid;
}

function func_abcr_cron_notify_carts()
{
    global $config;
    global $single_mode;

    $now = time();

    /*
        (start_time - end_time) is the largest period between 
        (time when the cart is considered abandoned) and 
        (time when no notifications will be sent about it)

        start_time = (now)
        - (how many days between placing cart and sending 1st notification)
        - (how many notifications - 1) * (how often they need to be sent) days 
        + 1 day (just for reservation)
    */

    $params = array(
        'end'   => $now - $config['Abandoned_Cart_Reminder']['abcr_notify_after'] * ABCR_SECONDS_PER_HOUR,
        'start' => $now - ($config['Abandoned_Cart_Reminder']['abcr_notify_after'] + ($config['Abandoned_Cart_Reminder']['abcr_notification_count']) * $config['Abandoned_Cart_Reminder']['abcr_notification_delay'] ) * ABCR_SECONDS_PER_HOUR,
    );

    $abandoned_carts = func_abcr_search_abandoned_carts($params);

    $notified_carts = array();

    if (!empty($abandoned_carts)) {

        $log = 'The notifications to the following emails were sent: ' . PHP_EOL . "\t";

        foreach ($abandoned_carts as $cart) {

            if (
                $cart['notification_count'] <= $config['Abandoned_Cart_Reminder']['abcr_notification_count']
                && ($now - $cart['notification_time']) >= $config['Abandoned_Cart_Reminder']['abcr_notification_delay'] * ABCR_SECONDS_PER_HOUR
            ) {

                if (empty($cart['coupon_data']) && $single_mode) {
                    // get default settings for coupon
                    $provider = func_abcr_detect_cart_provider($cart);
                    $settings = func_abcr_create_coupon_settings();
                    $cart['coupon_data'] = func_abcr_create_coupon($cart['email'], $settings, $provider);
                }

                $notified_carts[] = $cart['email'];
    
                func_abcr_send_message_to_abandoned_cart($cart);
            }
        
        }

    }

    if (!empty($notified_carts)) {

        $log = 'The notifications to the following emails were sent: ' . PHP_EOL . "\t";
        $log .= implode(PHP_EOL . "\t", $notified_carts);

    } else {
    
        $log = 'No emails were sent';

    }

    return $log;
}

function func_abcr_cron_delete_expired_carts()
{
    global $sql_tbl, $config;

    $now = time();

    $cart_time      = $now - $config['Abandoned_Cart_Reminder']['abcr_expire_after'] * SECONDS_PER_DAY;
    $coupon_time    = $now + $config['Abandoned_Cart_Reminder']['abcr_notify_after'] * ABCR_SECONDS_PER_HOUR - $config['Abandoned_Cart_Reminder']['abcr_expire_after'] * SECONDS_PER_DAY;

    $coupons    = func_query_column('SELECT coupon FROM ' . $sql_tbl['abcr_notifications'] . ' WHERE time < ' . $cart_time);
    $carts      = func_query_column('SELECT email FROM ' . $sql_tbl['abcr_abandoned_carts'] . ' WHERE time < ' . $cart_time);

    $log = '';

    if (!empty($carts)) {
        $log .= 'The abandoned carts for following users were deleted: ' . PHP_EOL . "\t" . implode(PHP_EOL . "\t", $carts) . PHP_EOL;
    }

    if (!empty($coupons)) {
        $log .= 'The following coupons were deleted: ' . PHP_EOL . "\t" . implode(PHP_EOL . "\t", $coupons) . PHP_EOL;
    }

    db_query('DELETE FROM ' . $sql_tbl['abcr_abandoned_carts'] . ' WHERE time < ' . $cart_time);
    db_query('DELETE FROM ' . $sql_tbl['discount_coupons'] . ' WHERE coupon in ("' . implode('","', $coupons) . '")');
    db_query('DELETE FROM ' . $sql_tbl['abcr_notifications'] . ' WHERE time < ' . $coupon_time);

    if (empty($log)) {
        $log = 'No expired abandoned carts were found';
    }

    return $log;
}

function func_abcr_cron_task() 
{
    global $config;

    $log = '';

    if (
        $config['Abandoned_Cart_Reminder']['abcr_work_mode'] == 'auto'
        && !defined('XCART_INSTALL')
    ) {
        $log  = func_abcr_cron_notify_carts();
        $log .= PHP_EOL . "\t";
        $log .= func_abcr_cron_delete_expired_carts();
    }

    return $log;
}

function func_abcr_get_products_subtotal($products_info) 
{
    $return = 0;

    if (is_array($products_info)) {

        foreach ($products_info as $product) {

            $return += $product['amount'] * $product['price'];       

        }

    }

    return $return;
}

function func_abcr_place_order_handler($orderid, $raw_userinfo)
{
    global $abcr_session_flags, $sql_tbl;

    x_session_register('abcr_session_flags');

    if (isset($abcr_session_flags['returned_customer'])) {
        $data = array('orderid' => $orderid);
        func_array2insert($sql_tbl['abcr_order_statistic'], $data);
    }
}

function func_abcr_place_order_finalize($orderid, $raw_userinfo)
{
    global $abcr_session_flags;
    global $sql_tbl;

    x_session_register('abcr_session_flags');

    if (isset($abcr_session_flags['returned_customer'])) {
        unset($abcr_session_flags['returned_customer']);
    }

    $userinfo = func_abcr_prepare_userinfo($raw_userinfo);
    func_abcr_delete_abandoned_cart($userinfo['email']);
}

function func_abcr_get_order_statistic($date = array(), $provider = 0)
{
    global $sql_tbl;

    $time_condition = '';

    if (isset($date['start'])) {
        $time_condition .= ' AND orders.date >= ' . $date['start']; 
    }

    if (isset($date['end'])) {
        $time_condition .= ' AND orders.date <= ' . $date['end'];
    }
    
    $query = 'SELECT orders.orderid, orders.total, orders.date FROM ' . $sql_tbl['orders'] . ' AS orders LEFT JOIN ' . $sql_tbl['abcr_order_statistic'] . ' AS stat ON stat.orderid = orders.orderid WHERE stat.orderid is NOT NULL' . $time_condition . ' ORDER BY orders.date DESC';

    $orders = func_query($query);

    $stats = array();

    $total = array(
        'total' => 0,
        'orders' => 0,
    );

    if (is_array($orders)) {

        foreach ($orders as $stat) {

            if (!empty($provider)) {
                $is_order_belongs_to_provider = func_query_first_cell("SELECT COUNT(*) FROM $sql_tbl[order_details] WHERE orderid = '$stat[orderid]' AND provider = '$provider'");
                if (!$is_order_belongs_to_provider) {
                    continue;
                }
            }

            $total['orders']++;

            $date = date('F\'y', $stat['date']);

            $stats[$date]['orders'][$stat['orderid']] = $stat['total'];

            if (!isset($stats[$date]['total'])) {
                $stats[$date]['total'] = 0;
            }

            $stats[$date]['total'] += $stat['total'];
            $total['total'] += $stat['total'];

        }
    }

    return array($stats, $total);
}

function func_abcr_redefine_configuration(&$configuration_entry) { //{{{

    if ($configuration_entry['name'] != 'abcr_coupon_type') {
        return false;
    }

    global $config;

    $cf_currency = $config['General']['currency_symbol'];

    foreach ($configuration_entry['variants'] as $kk => $var) {
        $configuration_entry['variants'][$kk]['name'] = str_replace(array('$'), array($cf_currency), $var['name']);
    }

} //}}}
