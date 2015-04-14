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
 * Functions related to the Advanced Order Management module
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Modules
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    f7753843bded08de90c93873b4a2fb1ed4ccb574, v59 (xcart_4_7_0), 2015-03-04 09:39:41, func.php, mixon
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if ( !defined('XCART_START') ) { header("Location: ../../"); die("Access denied"); }

/**
 * Prepare the data containing the modified fields
 */

function func_aom_prepare_diff($type, $new_data, $old_data, $extra = false)
{ // {{{
    if ($type=="A") {
        // Get products changes
        if (!empty($extra['products'])) {
            $diff['P'] = func_aom_get_products_diff($extra['products'], $old_data['products']);
        }

        // Get GC changes
        if (!empty($extra['giftcerts'])) {
            $diff['G'] = func_aom_get_gc_diff($extra['giftcerts'], $old_data['giftcerts']);
        }

        // Get totals changes
        $totals_fields = array(
            'total',
            'subtotal',
            'discount',
            'shipping_cost',
            'tax',
            'payment_method',
            'shipping',
            'coupon_discount',
            'coupon',
        );

        foreach ($totals_fields as $field) {
            $data_t[$field] = $new_data['order'][$field];
        }

        $diff['T'] = array_diff_assoc($data_t, $old_data['order']);

        unset ($data_t);

        // Get customer information changes
        $profile_fields = array_keys(func_get_default_fields('C'));
        $profile_fields[] = 'membershipid';

        $data_u = array();

        foreach($profile_fields as $field) {
            if (isset($new_data['userinfo'][$field])) {
                $data_u[$field] = $new_data['userinfo'][$field];
            }
        }

        $diff['U'] = array_diff_assoc($data_u, $old_data['userinfo']);

        unset ($data_u);

    } else {

        $diff[$type] = array_diff_assoc($new_data, $old_data);

    }

    // Unset empty sections
    foreach (array_keys($diff) as $section) {
        if (empty($diff[$section])) {
            func_unset($diff, $section);
        }
    }

    return $diff;
} // }}}

/**
 * Common function that writes order changes to the history
 */
// type: relation to the module / processor
//    X - status and/or common details changed
//    A - order details changed in X-AOM
//    R - order details changed in X-RMA
function func_aom_save_history($orderid, $type, $details)
{ // {{{
    global $logged_userid;

    $details['type'] = $type;

    if (
        $type == 'X'
        && defined('STATUS_CHANGE_REF')
    ) {
        $details['reference'] = constant('STATUS_CHANGE_REF');
    }

    $insert_data = array (
        'orderid'     =>     $orderid,
        'userid'     =>     $logged_userid,
        'date_time' =>     XC_TIME,
        'details'     =>     addslashes(serialize($details))
    );

    return func_array2insert('order_status_history', $insert_data);
} // }}}

/**
 * Function gets information about order changes
 */
function func_aom_get_history($orderid)
{ // {{{
    global $config, $sql_tbl;

    $history = array();

    $records = func_query("SELECT osh.*, c.login FROM $sql_tbl[order_status_history] as osh LEFT JOIN $sql_tbl[customers] as c ON osh.userid = c.id WHERE orderid='$orderid' ORDER BY date_time DESC");

    if (!empty($records)) {

        foreach($records as $k => $rec) {

            $rec["date_time"] += $config["Appearance"]["timezone_offset"];

            $tmp = $rec['details'] = unserialize($rec['details']);

            if (isset($tmp['reference'])) {
                $rec['status_note'] = func_get_langvar_by_name('lbl_aom_order_status_note_' . $tmp['reference']);
            }

            if (
                $tmp['type'] == 'X'
                && $tmp['old_status'] != $tmp['new_status']
            ) {
                $rec['event_header'] = empty($tmp['old_status'])
                    ? func_get_langvar_by_name('lbl_aom_order_placement_' . $tmp['new_status'])
                    : func_get_langvar_by_name(
                        'lbl_aom_order_status_changed_from_to',
                        array(
                            'old' => func_aom_get_order_status($tmp['old_status']),
                            'new' => func_aom_get_order_status($tmp['new_status'])
                        )
                    );
            }

            $history[$k] =  $rec;
        }

    }

    return $history;
} // }}}

/**
 * Function compares old and new products
 */
function func_aom_get_products_diff($new_products, $old_products)
{ // {{{
    $diff = array();

    foreach ($new_products as $k => $v) {

        // For new products $old_products will contain not set values, define them
        $old_products[$k]['price'] = (!empty($old_products[$k]['price'])) ? $old_products[$k]['price'] : 0;
        $old_products[$k]['amount'] = (!empty($old_products[$k]['amount'])) ? $old_products[$k]['amount'] : 0;

        $changed = (
            !empty($v['deleted'])
            || !empty($v['new'])
            || $v['price'] != $old_products[$k]['price']
            || $v['amount'] != $old_products[$k]['amount']
        );

        if ($changed) {
            $diff[] = array(
                'deleted'         => (!empty($v['deleted'])) ? 'Y' : 'N',
                'new'             => (!empty($v['new'])) ? 'Y' : 'N',
                'old_price'     => price_format($old_products[$k]['price']),
                'price'         => price_format($v['price']),
                'old_amount'     => $old_products[$k]['amount'],
                'amount'         => $v['amount'],
                'productcode'     => $v['productcode'],
                'product'         => $v['product'],
            );
        }

    }

    return $diff;
} // }}}

/**
 * Function compares old and new Gift certificates
 */
function func_aom_get_gc_diff($new_gc, $old_gc)
{ // {{{
    $diff = array();

    foreach ($new_gc as $k => $v) {

        $changed = (
            !empty($v['deleted'])
            || $v['amount'] != $old_gc[$k]['amount']
        );

        if ($changed) {
            $diff[] = array(
                'deleted'         => (!empty($v['deleted'])) ? 'Y' : 'N',
                'old_amount'     => price_format($old_gc[$k]['amount']),
                'amount'         => price_format($v['amount']),
                'gcid'             => $v['gcid'],
            );
        }

    }

    return $diff;
} // }}}

/**
 * Get default field's name label
 */
function func_aom_get_field_name($name)
{ // {{{
    $add = '';
    $prefix = substr($name, 0, 2);

    if (
        $prefix == 's_'
        || $prefix == 'b_'
    ) {
        $add = " (" . func_get_langvar_by_name('lbl_aom_' . $prefix . 'prefix') . ")";

        $name = substr($name, 2);
    }

    if (!in_array($name, array('customer_notes'))) {
        $name = str_replace(
            array(
                'firstname',
                'lastname',
                'zipcode',
                'membershipid',
                'notes',
                'tracking',
            ),
            array(
                'first_name',
                'last_name',
                'zip_code',
                'membership',
                'order_notes',
                'tracking_number',
            ),
            $name
        );
    }

    return func_get_langvar_by_name('lbl_' . $name) . $add;
} // }}}

/**
 * With no parameter returns a hash array with order statuses definitions,
 * otherwise returns a status definition
 */
function func_aom_get_order_status($status = false)
{ // {{{
    global $active_modules;

    if (!empty($active_modules['XOrder_Statuses'])) {
        return func_orderstatuses_get_order_status($status);
    }

    $statuses = array(
        'I' => func_get_langvar_by_name('lbl_not_finished'),
        'Q' => func_get_langvar_by_name('lbl_queued'),
        'A' => func_get_langvar_by_name('lbl_pre_authorized'),
        'P' => func_get_langvar_by_name('lbl_processed'),
        'D' => func_get_langvar_by_name('lbl_declined'),
        'B' => func_get_langvar_by_name('lbl_backordered'),
        'F' => func_get_langvar_by_name('lbl_failed'),
        'C' => func_get_langvar_by_name('lbl_complete'),
        'X' => func_get_langvar_by_name('lbl_xpc_order'),
    );

    return ($status && isset($statuses[$status]))
        ? $statuses[$status]
        : $statuses;
} // }}}

/**
 * Replace current rate value to saved value from order detailes
 * Rate is identified by tax_name/taxid/rateid bt:0095797 bt:0135095
 */
function func_aom_tax_rates_replace($productid, $current_tax_name, $current_tax_rate)
{ // {{{
    global $global_store;
    $global_store_taxes = $global_store['product_taxes'];

    if (
        !isset($global_store_taxes[$productid]) 
        || !is_array($global_store_taxes[$productid])
    ) {
        return $current_tax_rate;
    }

    // Disable tax in AOM if customer info (zoneid, membershipid) is changed and tax is disappeared.
    if (empty($current_tax_rate)) {
        return array();
    }

    foreach ($global_store_taxes[$productid] as $aom_tax_name => $aom_tax) {
        if (
            $aom_tax_name == $current_tax_name
            && $aom_tax['taxid'] == $current_tax_rate['taxid']
            && $aom_tax['rateid'] == $current_tax_rate['rateid']
        ) {
            $current_tax_rate['formula'] = $aom_tax['formula'];
            $current_tax_rate['rate_value'] = $aom_tax['rate_value'];
            $current_tax_rate['rate_type'] = $aom_tax['rate_type'];
            $current_tax_rate['tax_display_name'] = $global_store['tax_display_names'][$aom_tax_name];
            break;
        }
    }

    return $current_tax_rate;
} // }}}

/**
 * Create an empty order record in the database
 */
function func_aom_create_new_order($userid = false)
{ // {{{
    global $config;

    x_load('user');

    $new_orderid = false;
    $now = XC_TIME;

    $order_data = array(
        'date' => $now,
        'status' => 'Q',
        'giftcert_ids' => '',
        'taxes_applied' => '',
        'notes' => '',
        'details' => '',
        'customer_notes' => '',
        'taxes_applied' => '',
        'extra' => '',
    );

    $extras = array(
        'created_by_admin' => 'Y',
    );

    // Fill order details
    if (!empty($userid)) {
        
        // Copy user info to order data
        $userinfo = func_userinfo($userid, 'C', false, false, array('C','H'), false);
        
        $_fields = array (
            'title',
            'firstname',
            'lastname',
            'email',
            'url',
            'company',
            'tax_number',
            'tax_exempt',
            'membershipid'
        );

        foreach ($_fields as $k) {
            if (!isset($userinfo[$k])) {
                continue;
            }
            $order_data[$k] = addslashes($userinfo[$k]);
        }

        $_fields = array (
            'title',
            'firstname',
            'lastname',
            'address',
            'city',
            'county',
            'state',
            'country',
            'zipcode',
            'zip4',
            'phone',
            'fax',
        );

        foreach (array('b_', 's_') as $p) {
            foreach ($_fields as $k) {
                $f = $p . $k;
                if (isset($userinfo[$f])) {
                    $order_data[$f] = addslashes($userinfo[$f]);
                }
            }
        }


    } else {
        // Create an anonymous customer with empty details
        $new_login = $config['Advanced_Order_Management']['aom_new_order_login_prefix'] . $now;
        $user_data = array(
            'usertype' => 'C', 
            'login' => $new_login,
        );
        // Do not call XCUserSignature->updateSignature for C user
        $userid = func_array2insert('customers', $user_data); 

        $extras['no_customer'] = 'Y';
    }

    // Place order
    if (!empty($userid)) {
        $order_data['userid'] = $userid;
        $order_data['all_userid'] = $userid;
        $new_orderid = func_array2insert('orders', $order_data);

        // Save extra data
        if (!empty($new_orderid) && !empty($extras) && is_array($extras)) {
            $extras['unique_id'] = md5(func_microtime() . mt_rand());
            foreach ($extras as $k => $v) {
                if (strlen($v) > 0) {
                    func_array2insert(
                        'order_extras',
                        array(
                            'orderid' => $new_orderid,
                            'khash'   => addslashes($k),
                            'value'   => addslashes($v)
                        )
                    );
                }
            }
        }
    }

    return $new_orderid;
} // }}}

/**
 * Update customer info in the 'xcart_customers' table (for manually created orders)
 */
function func_aom_update_customer_info($userinfo)
{ // {{{
    global $sql_tbl;

    x_load('user');

    $userinfo['userid'] = intval($userinfo['userid']);
    if ($userinfo['userid'] <= 0) {
        return false;
    }

    static $storage = array();
    if (empty($storage)) {
        $storage = func_data_cache_get('sql_tables_fields');
    }

    $update_customer_fields = array_flip($storage[$sql_tbl['customers']]);
    $update_address_fields = array_flip($storage[$sql_tbl['address_book']]);

    // Update customers
    $update_customer = array();
    foreach ($userinfo as $k => $v) {
        if (
            !is_array($v)
            && isset($update_customer_fields[$k])
        ) {
            $update_customer[$k] = $v;
        }
    }
    if (!empty($update_customer)) {
        // Do not call XCUserSignature->updateSignature for C user
        func_array2update(
            'customers',
            $update_customer,
            "id = '$userinfo[id]'"
        );
    }

    // Clear address book
    db_query("DELETE av.* FROM $sql_tbl[register_field_address_values] AS av INNER JOIN $sql_tbl[address_book] AS bk ON av.addressid=bk.id WHERE bk.userid = '$userinfo[id]'");
    db_query("DELETE FROM $sql_tbl[address_book] WHERE userid = '$userinfo[id]'");
    // Update address book
    foreach (array('B', 'S') as $prefix) {
        $update_address = func_create_address($userinfo, $prefix);
        if (!empty($update_address)) {
            $update_address['default_' . strtolower($prefix)] = 'Y';
            $update_address['userid'] = $userinfo['userid'];
            foreach ($update_address as $k => $v) {
                if (
                    is_array($v)
                    || !isset($update_address_fields[$k])
                ) {
                    unset($update_address[$k]);
                }
            }
            if (!empty($update_address)) {
                $update_address = func_addslashes($update_address);
                func_array2insert(
                    'address_book',
                    $update_address
                );
            }
        }
    }
} // }}}

/**
 * Get product info used in the editing order
 *
 * @global array $cart_tmp
 * @param integer $productid
 *
 * @return type
 */
function func_aom_get_product_info($productid)
{ // {{{
    global $cart_tmp;

    $product = array();

    if (is_array($cart_tmp['products'])) {
        foreach ($cart_tmp['products'] as $k => $v) {
            if ($cart_tmp['products'][$k]['productid'] == $productid) {
                $product = $cart_tmp['products'][$k];
                break;
            }
        }
    }

    return $product;
} // }}}

/**
 * Generate anonymous userinfo for shipping and tax calculations
 *
 * @global string $current_area
 * @global type $login
 * @global type $logged_userid
 * @global type $user_account
 * @global type $xaom_saved_data
 *
 * @param type $order_info
 */
function func_aom_generate_anonymous_userinfo($order_info)
{ // {{{
    global $current_area, $login, $logged_userid, $user_account, $xaom_saved_data;

    $old_anonymous_userinfo = func_get_anonymous_userinfo();

    $xaom_saved_data = compact('current_area', 'login', 'logged_userid', 'user_account', 'old_anonymous_userinfo');

    $current_area = 'C';

    $login = $order_info['userinfo']['login'];
    $logged_userid = $order_info['userinfo']['userid'];
    $user_account = $order_info['userinfo'];

    func_set_anonymous_userinfo($order_info['userinfo'], 'skip_x_session_save');
} // }}}

/**
 * Restore original anonymous userinfo
 *
 * @global string $current_area
 * @global type $login
 * @global type $logged_userid
 * @global type $user_account
 * @global type $xaom_saved_data
 */
function func_aom_restore_anonymous_userinfo()
{ // {{{
    global $current_area, $login, $logged_userid, $user_account, $xaom_saved_data;

    if (!empty($xaom_saved_data)) {
        extract($xaom_saved_data);
    }

    func_set_anonymous_userinfo($old_anonymous_userinfo, 'skip_x_session_save');

    unset($xaom_saved_data);
} // }}}

/**
 * Get available payment methods for provided order info
 *
 * @global string $sql_tbl
 * @param type $order_info
 *
 * @return array
 */
function func_aom_get_payment_methods_for_order($order_info)
{ // {{{
    global $sql_tbl;

    $payment_methods = func_query(
        "SELECT $sql_tbl[payment_methods].paymentid,"
        . " $sql_tbl[payment_methods].payment_method,"
        . " $sql_tbl[payment_methods].surcharge_type,"
        . " $sql_tbl[payment_methods].surcharge"
        . " FROM $sql_tbl[payment_methods]"
        . " LEFT JOIN $sql_tbl[pmethod_memberships]"
        . " ON $sql_tbl[payment_methods].paymentid = $sql_tbl[pmethod_memberships].paymentid"
        . " WHERE $sql_tbl[payment_methods].active='Y'"
        . " AND ($sql_tbl[pmethod_memberships].membershipid = '" . $order_info['userinfo']['membershipid'] . "'"
        . " OR $sql_tbl[pmethod_memberships].membershipid IS NULL)"
        . " ORDER BY $sql_tbl[payment_methods].orderby"
    );

    return $payment_methods;
} // }}}

/**
 * Get payment method surcharge info
 *
 * @global string $sql_tbl
 * @param integer $paymentid
 *
 * @return array
 */
function func_aom_get_payment_method_surcharge_info($paymentid, $membershipid)
{ // {{{
    global $sql_tbl;

    $payment_methods = func_query_first(
        "SELECT $sql_tbl[payment_methods].paymentid,"
        . " $sql_tbl[payment_methods].payment_method,"
        . " $sql_tbl[payment_methods].surcharge_type,"
        . " $sql_tbl[payment_methods].surcharge"
        . " FROM $sql_tbl[payment_methods]"
        . " LEFT JOIN $sql_tbl[pmethod_memberships]"
        . " ON $sql_tbl[payment_methods].paymentid = $sql_tbl[pmethod_memberships].paymentid"
        . " WHERE $sql_tbl[payment_methods].paymentid = '$paymentid'"
        . " AND $sql_tbl[payment_methods].active='Y'"
        . " AND ($sql_tbl[pmethod_memberships].membershipid = '$membershipid'"
        . " OR $sql_tbl[pmethod_memberships].membershipid IS NULL)"
        . " ORDER BY $sql_tbl[payment_methods].orderby"
    );

    return $payment_methods;
} // }}}

/**
 * Calculate shipping rates for the provided order info
 *
 * @global object $smarty
 * @global array $order_data
 *
 * @param array $order_info
 *
 * @return array
 */
function func_aom_get_shipping_rates_for_order(&$order_info)
{ // {{{
    global $smarty, $order_data;

    func_aom_generate_anonymous_userinfo($order_info);

    $shipping = func_get_shipping_methods_list($order_info, $order_info['products'], $order_info['userinfo']);

    func_aom_restore_anonymous_userinfo();

    if (is_array($shipping)) {
        $found = false;
        foreach ($shipping as $v) {
            // Check original order shipping method
            if ($order_data['order']['shippingid'] == $v['shippingid']) {
                $found = true;
                break;
            }
        }
        if (!$found && empty($order_info['order']['shippingid'])) {
        }
        else {
            if (!$found && $order_info['shippingid'] == $order_info['order']['shippingid']) {
                $order_info['shippingid'] = $shipping[0]['shippingid'];
                $order_info['shipping'] = $shipping[0]['shipping'];
            }
            if (!$found) {
                $smarty->assign('shipping_lost', $shipping);
            }
        }
    }

    return $shipping;

} // }}}

/**
 * Get discount coupons available
 *
 * @global string $sql_tbl
 *
 * @param type $order_info
 *
 * @return array
 */
function func_aom_get_coupons_for_order($order_info)
{ // {{{
    global $sql_tbl;

    $coupons = func_query("SELECT * FROM $sql_tbl[discount_coupons] ORDER BY coupon");

    if (
        !empty($order_info['__original_coupon_info']['coupon'])
        && !func_query_first_cell("SELECT COUNT(*) FROM $sql_tbl[discount_coupons] WHERE coupon = '".addslashes($order_info['__original_coupon_info']['coupon'])."'")
    ) {
        $coupons[] = array(
            'coupon' => $order_info['__original_coupon_info']['coupon'],
            'discount' => $order_info['__original_coupon_info']['discount'],
            'coupon_type' => $order_info['__original_coupon_info']['coupon_type'],
            '__deleted' => true
        );
    }

    return $coupons;
} // }}}

function func_aom_prepare_additiona_fields(&$order_additional_fields)
{ // {{{
    $current_additional_fields = func_get_additional_fields('H');

    foreach ($order_additional_fields as $ok => $ov) {

        // Set default values
        $order_additional_fields[$ok]['is_found'] = false;
        $order_additional_fields[$ok]['is_avail'] = false;

        // Loop through current additional fields
        foreach ($current_additional_fields as $ck => $cv) {

            if (
                $ov['fieldid'] === $cv['fieldid']
                && $ov['section'] === $cv['section']
                && $ov['type'] === $cv['type']
            ) {
                // Field is found
                $order_additional_fields[$ok]['is_found'] = true;
                // Field is avail
                $order_additional_fields[$ok]['is_avail'] = ($cv['avail'] == 'Y');
                break;
            }

        }

        // Define original value
        $order_additional_fields[$ok]['original_value'] = $ov['value'];
    }
} // }}}

/**
 * Initialize additional fields data
 *
 * @param array $order_info
 */
function func_aom_initialize_additional_fields_data(&$order_info)
{ // {{{
    // Check if order has saved original info
    if (
        !empty($order_info['extra']['additional_fields'])
        && is_array($order_info['extra']['additional_fields'])
    ) {
        $order_info['userinfo']['additional_fields'] = $order_info['extra']['additional_fields'];
    }

    // Prepare additional fields
    func_aom_prepare_additiona_fields($order_info['userinfo']['additional_fields']);

} // }}}

/**
 * Initialize provider info data
 *
 * @param type $product
 */
function func_aom_initialize_provider_info(&$product)
{ // {{{
    global $sql_tbl;

    static $providers = null;

    if (!isset($providers[$product['provider']])) {

        $_provider_name = func_query_first_cell(
            "SELECT"
            . " email"
            . " FROM $sql_tbl[customers]"
            . " WHERE id='$product[provider]' AND usertype IN ('P','A')"
        );

        if (!empty($_provider_name)) {
            $provider_name = $_provider_name;
        } else {
            $provider_name = func_get_langvar_by_name('lbl_not_found');
        }

        $providers[$product['provider']] = $provider_name;

    } else {

        $provider_name = $providers[$product['provider']];
    }

    $product['provider_name'] = $provider_name . " (id: $product[provider])";
} // }}}

/**
 * Caclulate payment method surcharge
 *
 * @param double $total
 * @param array $cart
 *
 * @return double
 */
function func_aom_calculate_payment_method_surcharge($total, $cart)
{ // {{{

    $surcharge = 0;

    if (
        !empty($cart['use_payment_surcharge_alt'])
        && !empty($cart['payment_surcharge_type_alt'])
    ) {

        // Calculate surcharge amount
        if ($cart['payment_surcharge_type_alt'] == 'percent') {
            $surcharge = $total * $cart['payment_surcharge_alt'] / 100;
        } else {
            $surcharge = $cart['payment_surcharge_alt'];
        }

    } elseif (
        !empty($cart['paymentid'])
        && isset($cart['userinfo']['membershipid'])
        && !empty($cart['use_payment_alt'])
        && $cart['use_payment_alt'] == 'Y'
    ) {

        // Alternative payment method is selected

        $surcharge_info = func_aom_get_payment_method_surcharge_info($cart['paymentid'], $cart['userinfo']['membershipid']);

        if (!empty($surcharge_info['surcharge_type'])) {

            // Calculate surcharge amount
            if ($surcharge_info['surcharge_type'] == 'percent') {
                $surcharge = $total * $surcharge_info['surcharge'] / 100;
            } else {
                $surcharge = $surcharge_info['surcharge'];
            }

        }

    } elseif (
        !empty($cart['payment_surcharge'])
    ) {
        // Return saved value
        $surcharge = $cart['payment_surcharge'];
    }

    return $surcharge;

} // }}}

/**
 * Initialize order discount data
 *
 * @global array $order_data
 *
 * @param ref $cart
 */
function func_aom_initialize_order_discount_data(&$cart)
{ // {{{
    global $order_data; // original order data

    $cart['discount_alt'] = $order_data['order']['discount'];

    if (empty($cart['extra']['discount_info'])) {
        $cart['extra']['discount_info'] = array(
            'discount' => $order_data['order']['discount'],
            'discount_type' => 'absolute'
        );
    }

} // }}}

/**
 * Initialize order coupon discount data
 *
 * @global array $order_data
 *
 * @param ref $cart
 */
function func_aom_initialize_order_coupon_discount_data(&$cart)
{ // {{{
    global $order_data; // original order data

    $cart['coupon_discount_alt'] = $order_data['order']['coupon_discount'];
    // Initialize Discount_Coupons module data
    $cart['discount_coupon'] = $order_data['order']['coupon'];

    if (empty($cart['extra']['discount_coupon_info'])) {
        $cart['extra']['discount_coupon_info'] = array(
            'coupon' => $order_data['order']['coupon'],
            'discount' => $order_data['order']['coupon_discount'],
            'coupon_type' => 'absolute'
        );
    }

    $cart['__original_coupon'] = $cart['extra']['discount_coupon_info']['coupon'];
    $cart['__original_coupon_info'] = $cart['extra']['discount_coupon_info'];

} // }}}

/**
 * Initialize order shipping data
 *
 * @global array $sql_tbl
 * @global string $current_carrier
 *
 * @param ref $cart
 */
function func_aom_initialize_order_shipping_data(&$cart)
{ // {{{
    global $sql_tbl, $current_carrier;

    $cart['shipping_cost_alt'] = $cart['display_shipping_cost'];
    if (func_query_first_cell("SELECT code FROM $sql_tbl[shipping] WHERE shippingid='$cart[shippingid]'") != 'UPS') {
        $current_carrier = '';
    }

} // }}}

/**
 * Update order discount
 *
 * @global array $order_data
 *
 * @param ref $cart
 * @param ref $total_details
 */
function func_aom_update_order_discount(&$cart, &$total_details)
{ // {{{
    global $order_data; // original order data

    if (empty($total_details['discount_alt'])) {
        $total_details['discount_alt'] = '0.00';
    }

    if (!empty($total_details['use_discount_alt']) && !empty($total_details['discount_alt'])) {

        // Use alt discount
        $cart['discount_alt'] = $cart['discount'] = $total_details['discount_alt'] = func_aom_validate_price($total_details['discount_alt']);
        $cart['extra']['discount_info']['discount'] = price_format($total_details['discount_alt']);
        $cart['extra']['discount_info']['discount_type'] = $total_details['discount_type_alt'];

        $cart['use_discount_alt'] = 'Y';

    } else {

        $cart['use_discount_alt'] = 'N';
    }

    if (!isset($cart['use_discount_alt'])) {

        // Use original discount
        $cart['discount_alt'] = $cart['discount'] = $total_details['discount_alt'] = $order_data['order']['discount'];
        $cart['extra']['discount_info']['discount'] = $order_data['order']['extra']['discount_info']['discount'];
        $cart['extra']['discount_info']['discount_type'] = $order_data['order']['extra']['discount_info']['discount_type'];

        unset($cart['use_discount_alt']);
    }
} // }}}

/**
 * Update order coupon discount
 *
 * @global array $order_data
 * @global array $sql_tbl
 *
 * @param ref $cart
 * @param ref $total_details
 */
function func_aom_update_order_coupon_discount(&$cart, &$total_details)
{ // {{{
    global $order_data; // original order data

    if (empty($total_details['coupon_discount_alt'])) {
        $total_details['coupon_discount_alt'] = '0.00';
    }

    if (!empty($total_details['use_coupon_discount_alt']) && !empty($total_details['coupon_discount_alt'])) {

        // Use alt coupon discount
        $cart['coupon_discount_alt'] = $total_details['coupon_discount_alt'] = func_aom_validate_price($total_details['coupon_discount_alt']);
        $cart['discount_coupon'] = 'AOM#'.$cart['orderid'];
        $cart['coupon_type'] = $total_details['coupon_discount_type_alt'];

        // Update coupon info
        $cart['extra']['discount_coupon_info'] = array();

        $cart['extra']['discount_coupon_info']['coupon'] = $cart['discount_coupon'];
        $cart['extra']['discount_coupon_info']['discount'] = price_format($total_details['coupon_discount_alt']);
        $cart['extra']['discount_coupon_info']['coupon_type'] = $total_details['coupon_discount_type_alt'];

        $cart['use_old_coupon_discount'] = false;

        $cart['use_coupon_discount_alt'] = 'Y';

    } elseif (!empty($total_details['coupon_alt'])) {

        func_unset($cart, 'use_coupon_discount_alt', 'coupon_discount_alt', 'coupon', 'coupon_discount', 'discount_coupon',  'use_old_coupon_discount');

        if ($total_details['coupon_alt'] == '__old_coupon__') {

            // Use old deleted coupon
            $cart['coupon_discount_alt'] = $total_details['coupon_discount_alt'] = $cart['coupon_discount'] = $order_data['order']['coupon_discount'];
            $cart['discount_coupon'] = $cart['coupon'] = $order_data['order']['coupon'];
            $cart['coupon_type'] = $order_data['order']['coupon_type'];

            // Restore coupon info
            $cart['extra']['discount_coupon_info'] = $cart['__original_coupon_info'];

            $cart['use_old_coupon_discount'] = true;

        } else {

            // Use exists coupon
            $cart['discount_coupon'] = $cart['coupon'] = stripslashes($total_details['coupon_alt']);

            global $sql_tbl;
            $cart['extra']['discount_coupon_info'] = func_query_first("SELECT * FROM $sql_tbl[discount_coupons] WHERE coupon='" . addslashes($cart['coupon']) . "'");
        }

    } elseif (empty($total_details['use_coupon_discount_alt']) && empty($total_details['coupon_alt'])) {

        func_unset($cart, 'use_coupon_discount_alt', 'coupon_discount_alt', 'coupon_discount', 'discount_coupon', 'coupon', 'use_old_coupon_discount');

        // Update coupon info
        $cart['extra']['discount_coupon_info'] = array();

        $cart['extra']['discount_coupon_info']['coupon'] = '';
        $cart['extra']['discount_coupon_info']['discount'] = price_format(0);
        $cart['extra']['discount_coupon_info']['coupon_type'] = 'absolute';

    }

} // }}}

/**
 * Update order shipping data
 *
 * @global array $config
 * @global array $active_modules
 * @global string $current_carrier
 * @global string $selected_carrier
 * @global array $order_data
 *
 * @param ref $cart
 * @param ref $total_details
 */
function func_aom_update_order_shipping_data(&$cart, &$total_details)
{ // {{{
    global $config, $active_modules, $current_carrier, $selected_carrier;
    global $order_data; // original order data

    if (empty($total_details['shipping_cost_alt'])) {
        $total_details['shipping_cost_alt'] = '0.00';
    }

    if (
        $config['Shipping']['realtime_shipping'] == 'Y'
        && !empty($active_modules['UPS_OnLine_Tools'])
        && $config['Shipping']['use_intershipper'] != 'Y'
    ) {
        $current_carrier = $selected_carrier;
    }

    if (!empty($total_details['use_shipping_alt'])) {
        $_shipping = explode(":::", $total_details['shipping_alt']);
        $cart['shipping'] = $_shipping[1];
        $cart['shippingid'] = $_shipping[0];
        $cart['use_shipping_alt'] = 'Y';
    }
    else {
        $cart['shipping'] = $total_details['shipping'];
        $cart['shippingid'] = $order_data['order']['shippingid'];
        $cart['use_shipping_alt'] = 'N';
    }

    if (
        !empty($total_details['use_shipping_cost_alt'])
        && !empty($total_details['shipping_cost_alt'])
    ) {

        // Use alt shipping cost
        $total_details['shipping_cost_alt'] = func_aom_validate_price($total_details['shipping_cost_alt']);

        $cart['shipping_cost'] = $total_details['shipping_cost_alt'];
        $cart['shipping_cost_alt'] = $total_details['shipping_cost_alt'];

        $cart['shippingid'] = '';

        $cart['use_shipping_cost_alt'] = 'Y';

    } else {
        func_unset($cart, 'use_shipping_cost_alt');
    }

} // }}}

/**
 * Update order payment data
 *
 * @global array $order_data
 *
 * @param ref $cart
 * @param ref $total_details
 */
function func_aom_update_order_payment_data(&$cart, &$total_details)
{ // {{{
    global $order_data; // original order data

    if (empty($total_details['payment_surcharge_alt'])) {
        $total_details['payment_surcharge_alt'] = '0.00';
    }

    if (!empty($total_details['use_payment_alt'])) {
        $_payment_method = explode(":::", $total_details['payment_alt']);
        $cart['payment_method'] = $_payment_method[1];
        $cart['paymentid'] = $_payment_method[0];
        $cart['use_payment_alt'] = 'Y';
    }
    else {
        $cart['payment_method'] = $total_details['payment_method'];
        $cart['paymentid'] = $order_data['order']['paymentid'];
        $cart['use_payment_alt'] = 'N';
    }

    if (
        !empty($total_details['use_payment_surcharge_alt'])
        && !empty($total_details['payment_surcharge_type_alt'])
    ) {

        // Use alt payment method surcharge
        $cart['use_payment_surcharge_alt'] = 'Y';
        $cart['payment_surcharge_alt'] = func_aom_validate_price($total_details['payment_surcharge_alt']);
        $cart['payment_surcharge_type_alt'] = $total_details['payment_surcharge_type_alt'];

    } else {
        func_unset($cart,'use_payment_surcharge_alt','payment_surcharge_alt','payment_surcharge_type_alt');
    }

} // }}}

?>
