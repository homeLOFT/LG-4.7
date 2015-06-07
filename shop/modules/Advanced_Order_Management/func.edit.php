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
 * Order editing functions
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Modules
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    19083a0be06baecd0a2e9948692c7a4ca7bb7013, v64 (xcart_4_7_1), 2015-03-09 10:50:11, func.edit.php, mixon
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if ( !defined('XCART_SESSION_START') ) { header("Location: ../../"); die("Access denied"); }

x_load('cart','mail','order','product','shipping','taxes','category');

/**
 * This function calculates the product's quantity in stock to display it
 * on the Edit products dialog
 */
function func_get_quantity_in_stock($productid, $order_status, $options = array(), $order_product = array())
{ // {{{
    global $sql_tbl, $active_modules;

    $quantity_in_stock = (strpos('PCQI',$order_status) !== false) ? ((!empty($active_modules['Egoods']) && !empty($order_product['distribution'])) ? 0 : $order_product['amount']) : 0;
    if (!empty($active_modules['Product_Options']) && !empty($options)) {
        $is_equal = true;

        $order_options = @$order_product['extra_data']['product_options'];
        if (!empty($order_options)) {
            $order_variantid = func_get_variantid($order_options);
            $variantid = func_get_variantid($options);
            $is_equal = ($order_variantid == $variantid);
        }
        
        if (!$is_equal) {
            $quantity_in_stock = 0;
        }

        $quantity_in_stock += func_get_options_amount($options, $productid);

    } else {
        $quantity_in_stock += func_query_first_cell("SELECT avail FROM $sql_tbl[products] WHERE productid='$productid'");
    }

    if (!empty($active_modules['RMA'])) {
        $quantity_in_stock -= (int) func_query_first_cell("SELECT SUM(returned_amount) FROM $sql_tbl[returns] WHERE itemid = '$order_product[itemid]'");
    }

    return $quantity_in_stock;
} // }}}

/**
 * This function validates the price that can be entered e.g. as $15.07
 */
function func_aom_validate_price($price)
{ // {{{
    return func_detect_price($price);
} // }}}

/**
 * This function updates products prices with VAT values
 */
function func_aom_update_prices($products, $customer_info)
{ // {{{
    foreach ($products as $k=>$v) {

        // Consider price deducted tax by default
        $products[$k]['price_deducted_tax'] = 'Y';

        // Check if we specified display price instead
        if (
            !empty($products[$k]['display_price_alt'])
        ) {
            $products[$k]['price_deducted_tax'] = 'N';

            unset($products[$k]['display_price_alt']);
        }

        // Get current products taxes
        $_product_taxes = !empty($v['extra_data']['taxes']) ? $v['extra_data']['taxes'] : '';

        // Get product taxes
        $_taxes = func_get_product_taxes($products[$k], $customer_info['userid'], false, $_product_taxes, array(), $customer_info, $products[$k]['price_deducted_tax'] == 'Y');

        // Check if we have taxes that are included into the price
        foreach ($_taxes as $_tax) {
            if (
                $_tax['price_includes_tax'] == 'Y'
                || $_tax['display_including_tax'] == 'Y'
            ) {
                $products[$k]['is_calculated_price'] = 'Y';
                break;
            }
        }

        // Update product taxes
        $products[$k]['taxes'] = $_taxes;
    }
    return $products;
} // }}}

/**
 * This function recalculate order totals
 */
function func_recalculate_totals($cart)
{ // {{{
    global $active_modules, $order_data, $config, $global_store;

    func_aom_generate_anonymous_userinfo($cart);

    $saved_state = false;
    if (!empty($active_modules['Special_Offers'])) {
        $saved_state = true;
        unset($active_modules['Special_Offers']);
    }

    if (!empty($cart['use_discount_alt']) && $cart['use_discount_alt'] == 'Y') {

        if (
            !defined('XAOM_WO_DISCOUNT_DATA')
            && !empty($cart['extra']['discount_info'])
            && !empty($cart['extra']['discount_info']['discount'])
        ) {
            define('XAOM_WO_DISCOUNT_DATA', 1);
        }

        $global_store['discounts'] = array(array(
            '__override' => true,
            'discountid' => 999999999,
            'minprice' => 0,
            'discount' => $cart['extra']['discount_info']['discount'],
            'discount_type' => $cart['extra']['discount_info']['discount_type']
        ));

    } elseif (
        !empty($cart['extra']['discount_info'])
        && !empty($cart['extra']['discount_info']['discount'])
    ) {
        if (
            !defined('XAOM_WO_DISCOUNT_DATA')
        ) {
            define('XAOM_WO_DISCOUNT_DATA', 1);
        }

        $global_store['discounts'] = array(array(
            '__override' => true,
            'discountid' => 999999999,
            'minprice' => 0,
            'discount' => $cart['extra']['discount_info']['discount'],
            'discount_type' => $cart['extra']['discount_info']['discount_type']
        ));
    }

    if (!empty($cart['use_coupon_discount_alt']) && $cart['use_coupon_discount_alt'] == 'Y') {

        $global_store['discount_coupons'] = array(
            array(
                '__override' => true,
                'coupon' => 'AOM#'.$cart['orderid'],
                'discount' => $cart['coupon_discount_alt'],
                'coupon_type' => $cart['coupon_type'],
                'minimum' => 0,
                'times' => 999999999,
                'times_used' => 0,
                'expire' => XC_TIME+30879000,
                'status' => 'A',
            )
        );

    } elseif (
        !empty($cart['extra']['discount_coupon_info'])
        && $cart['extra']['discount_coupon_info']['coupon'] == $cart['coupon']
    ) {
        $coupon_data = $cart['extra']['discount_coupon_info'];
        $coupon_data['__override'] = true;
        $global_store['discount_coupons'] = array($coupon_data);
    }

    // Initialization global var for func_calculate_single bt:0095797
    foreach ($cart['products'] as $k => $v) {
        $global_store['product_taxes'][$v['productid']] = (!empty($v['extra_data']['taxes'])) ? $v['extra_data']['taxes'] : false;
    } 

    $cart['products'] = func_aom_update_prices($cart['products'], $cart['userinfo']);

    $cart = func_array_merge(
        $cart,
        func_calculate(
            $cart,
            $cart['products'],
            $cart['userinfo']['userid'],
            $cart['userinfo']['usertype'],
            $cart['paymentid']
        )
    );

    // Update product taxes extra data with calculated tax values bt:0143563
    foreach ($cart['products'] as $k => $v) {
        $cart['products'][$k]['extra_data']['taxes'] = $cart['products'][$k]['taxes'];
    }

    $cart['total'] = $cart['total_cost'];

    $cart['applied_taxes'] = $cart['taxes'];

    if (
        isset($cart['orders']['taxes'])
        && isset($cart['orders']['tax_cost'])
    ) {
        $cart['taxes'] = $cart['orders']['taxes'];
        $cart['tax'] = $cart['orders']['tax_cost'];
    }

    // Correct state, country and county full names (if they were modified)

    $uinfo = $cart['userinfo'];

    // Correct the billing address
    if ($uinfo['b_state'].$uinfo['b_country'].$uinfo['b_county'] != $order_data['userinfo']['b_state'].$order_data['userinfo']['b_country'].$order_data['userinfo']['b_county']) {
        $uinfo['b_statename'] = $uinfo['b_state_text'] = func_get_state($uinfo['b_state'], $uinfo['b_country']);
        $uinfo['b_countryname'] = $uinfo['b_country_text'] = func_get_country($uinfo['b_country']);
        if ($config['General']['use_counties'] == 'Y') {
            $uinfo['b_countyname'] = $uinfo['b_county_text'] = func_get_county($uinfo['b_county']);
        }
    }

    // Correct the shipping address
    if ($uinfo['s_state'].$uinfo['s_country'].$uinfo['s_county'] != $order_data['userinfo']['s_state'].$order_data['userinfo']['s_country'].$order_data['userinfo']['s_county']) {
        $uinfo['s_statename'] = $uinfo['s_state_text'] = func_get_state($uinfo['s_state'], $uinfo['s_country']);
        $uinfo['s_countryname'] = $uinfo['s_country_text'] = func_get_country($uinfo['s_country']);
        if ($config['General']['use_counties'] == 'Y') {
            $uinfo['s_countyname'] = $uinfo['s_county_text'] = func_get_county($uinfo['s_county']);
        }
    }

    $cart['userinfo'] = $uinfo;

    if ($saved_state) {
        $active_modules['Special_Offers'] = true;
    }

    func_aom_restore_anonymous_userinfo();

    return $cart;
} // }}}

function func_aom_is_variant_changed($orig_product, $new_product)
{ // {{{
    if (empty($new_product['extra_data']['product_options'])) {
        return false;
    }

    $new_options = @$new_product['extra_data']['product_options'];
	$orig_options = @$orig_product['extra_data']['product_options'];
	
	return func_get_variantid($new_options) != func_get_variantid($orig_options);
} // }}}

/**
 * This function updates the order info in the database
 */
function func_update_order($order_info, $old_order_data = "")
{ // {{{
    global $sql_tbl, $config, $active_modules, $xcart_dir, $dhl_ext_country, $all_languages, $ship_packages_uniq;
    $old_products = $old_order_data['products'];

    $cart = func_recalculate_totals($order_info);

    $userinfo = $cart['userinfo'];
    $products = $cart['products'];
    $giftcerts = $cart['giftcerts'];

    if (!empty($active_modules['XAffiliate'])) {
        $partner = func_query_first_cell("SELECT userid FROM $sql_tbl[partner_payment] WHERE orderid = '$cart[orderid]' AND affiliate = '0'");
        global $single_mode;
    }

    // Update stock level

    if (
        $config['General']['unlimited_products'] != 'Y'
        && (
            in_array($cart['status'], array('Q','I','P','C'))
            || (
                !empty($active_modules['XOrder_Statuses'])
                && func_orderstatuses_check_if_status_changes_stock($cart['status'])
            )
        )
    ) {

        $_products = $_old_products = array();

        if (is_array($products)) {
            foreach($products as $k => $product) {
                if (!empty($active_modules['Egoods']) && !empty($product['distribution'])) {
                    continue;
                }

                if (!empty($product['deleted'])) {
                    $product['amount'] = 0;
                }

                if (!empty($product['stock_update']) && $product['stock_update'] == 'Y') {
                    if (!func_aom_is_variant_changed($old_products[$k], $product)) {
                        $amount_orig = (is_array($old_products) && $old_products[$k]['amount']) ? $old_products[$k]['amount'] : 0;
                        $amount_ret = (!empty($active_modules['RMA']) && !empty($product['returned_to_stock'])) ? $product['returned_to_stock'] : 0;
                        $amount_change = $amount_orig - $product['amount'] - $amount_ret;

                        if ($amount_change) {
                            $product['amount'] = abs($amount_change);
                            func_update_quantity(array($product), $amount_change > 0);
                        }
                    } else {
                        func_update_quantity(array($old_products[$k]));
                        func_decrease_quantity(array($product));
                    }
                }
            }
        }
    }

    // Prepare data

    $_extra = $cart['extra'];

    $_extra['tax_info'] = array (
        'display_taxed_order_totals'      => $config['Taxes']['display_taxed_order_totals'],
        'display_cart_products_tax_rates' => $config['Taxes']['display_cart_products_tax_rates'],
        'tax_operation_scheme'            => $config['Taxes']['tax_operation_scheme'],
        'taxed_subtotal'                  => $cart['display_subtotal'],
        'taxed_discounted_subtotal'       => $cart['display_discounted_subtotal'],
        'taxed_shipping'                  => $cart['display_shipping_cost'],
    );

    unset($_extra['tax_info']['product_tax_name']);

    $_extra['additional_fields'] = $userinfo['additional_fields'];

    if (!empty($dhl_ext_country)) {
        $is_dhl_shipping = func_query_first_cell("SELECT COUNT(*) FROM $sql_tbl[shipping] WHERE shippingid = '$cart[shippingid]' AND code = 'ARB' AND destination = 'I'") > 0;
        if ($is_dhl_shipping) {
            if (!function_exists('func_shipper_ARB')) {
                require_once $xcart_dir.'/shipping/mod_ARB.php';
            } else {
                global $dhl_ext_countries;
            }

            if (empty($dhl_ext_countries)) {
                $dhl_ext_country = false;
            }
        } else {
            $dhl_ext_country = false;
        }

    }

    if (!empty($dhl_ext_country)) {
        $_extra['dhl_ext_country'] = $dhl_ext_country;
    } else {
        func_unset($_extra, 'dhl_ext_country');
    }

    // Restore tax names for all languages bt:0096284
    if (is_array($cart['taxes'])) {
        foreach($cart['taxes'] as $ktax => $vtax) {
            $cart['taxes'][$ktax]['intl_tax_names'] = isset($old_order_data['order']['applied_taxes'][$ktax]['intl_tax_names']) ?
                    $old_order_data['order']['applied_taxes'][$ktax]['intl_tax_names'] :
                    func_get_languages_alt('tax_'.$vtax['taxid'], false, false, false, true);
        }
    }

    $taxes_applied = serialize($cart['taxes']);

    if (!empty($cart['use_shipping_cost_alt'])) {
        $cart['shipping_cost'] = $cart['shipping_cost_alt'];
    }

    $userinfo['b_address'] .= "\n".$userinfo['b_address_2'];
    $userinfo['s_address'] .= "\n".$userinfo['s_address_2'];

    // Update order info

    $memberships = func_get_memberships('C', true);

    $_query_data = array(
        'total' => $cart['total'],
        'giftcert_discount' => $cart['giftcert_discount'],
        'giftcert_ids' => $cart['giftcert_ids'],
        'subtotal' => $cart['subtotal'],
        'shipping_cost' => $cart['shipping_cost'],
        'shippingid' => $cart['shippingid'],
        'shipping' => $cart['shipping'],
        'tax' => $cart['tax'],
        'taxes_applied' => $taxes_applied,
        'discount' => $cart['discount'],
        'coupon' => ($cart['coupon'] ? ((preg_match("/(free_ship|percent|absolute)/S", $cart['coupon_type'])) ? ($cart['coupon_type']."``".$cart['coupon']) : $cart['coupon']) : ''),
        'coupon_discount' => $cart['coupon_discount'],
        'payment_method' => $cart['payment_method'],
        'paymentid' => $cart['paymentid'],
        'payment_surcharge' => $cart['payment_surcharge'],
        'extra' => serialize($_extra),
        'membership' => !empty($memberships[$userinfo['membershipid']]) ? $memberships[$userinfo['membershipid']]['membership'] : '',
        'membershipid' => $userinfo['membershipid'],
        'title' => $userinfo['title'],
        'firstname' => $userinfo['firstname'],
        'lastname' => $userinfo['lastname'],
        'company' => $userinfo['company'],
        'tax_number' => $userinfo['tax_number'],
        'b_title' => $userinfo['b_title'],
        'b_firstname' => $userinfo['b_firstname'],
        'b_lastname' => $userinfo['b_lastname'],
        'b_address' => $userinfo['b_address'],
        'b_city' => $userinfo['b_city'],
        'b_county' => !empty($userinfo['b_county']) ? $userinfo['b_county'] : '',
        'b_state' => $userinfo['b_state'],
        'b_country' => $userinfo['b_country'],
        'b_zipcode' => $userinfo['b_zipcode'],
        'b_zip4' => $userinfo['b_zip4'],
        'b_phone' => $userinfo['b_phone'],
        'b_fax' => $userinfo['b_fax'],
        's_title' => $userinfo['s_title'],
        's_firstname' => $userinfo['s_firstname'],
        's_lastname' => $userinfo['s_lastname'],
        's_address' => $userinfo['s_address'],
        's_city' => $userinfo['s_city'],
        's_county' => !empty($userinfo['s_county']) ? $userinfo['s_county'] : '',
        's_state' => $userinfo['s_state'],
        's_country' => $userinfo['s_country'],
        's_zipcode' => $userinfo['s_zipcode'],
        's_zip4' => $userinfo['s_zip4'],
        's_phone' => $userinfo['s_phone'],
        's_fax' => $userinfo['s_fax'],
        'email' => $userinfo['email'],
        'url' => $userinfo['url']
    );

    $_query_data['tax_exempt'] = func_does_customer_have_tax_examption($userinfo);

    $query_data = func_array_map('addslashes', $_query_data);

    func_array2update('orders', $query_data, "orderid='$cart[orderid]'");

    // Store packages for Shipping_Label_Generator
    if (!empty($active_modules['Shipping_Label_Generator'])) {
        x_session_register('ship_packages_uniq');

        $_code = func_query_first_cell("SELECT code FROM $sql_tbl[shipping] WHERE shippingid='$cart[shippingid]'");
        $_pack_index = $products[0]['provider'] . $_code;

        db_query("DELETE FROM $sql_tbl[shipping_labels] WHERE orderid='$cart[orderid]'");
        db_query("DELETE FROM $sql_tbl[order_extras] WHERE orderid='$cart[orderid]' AND khash LIKE 'ship_packages_uniq%'");

        if (!empty($ship_packages_uniq[$_pack_index])) {
            $_query_data = array(
                'orderid' => $cart['orderid'],
                'khash' => "ship_packages_uniq$_code",
                'value' => serialize($ship_packages_uniq[$_pack_index])
            );
            $query_data = func_array_map('addslashes', $_query_data);
            func_array2insert('order_extras', $query_data, true);
        }
    }

    // Update order details info

    if (is_array($products)) {
        $items = array();
        foreach ($products as $pk => $product) {
            if (!empty($product['deleted'])) {
                continue;
            }

            if (!empty($active_modules['Product_Options'])) {

                $options = array();

                if (isset($product['keep_options']) && $product['keep_options'] == 'Y') {

                    // Keep original options choice
                    $options = $product['extra_data']['product_options'];
                    $options_alt = $product['extra_data']['product_options_alt'];
                    if (!$options_alt) {
                        $product['product_options'] = func_serialize_options($options);
                    } else {
                        $product['product_options'] = isset($options_alt[$config['default_admin_language']]) ? $options_alt[$config['default_admin_language']] : "";
                    }
                }
                else {

                    // Save selected options
                    if (is_array($product['product_options'])) {
                        foreach ($product['product_options'] as $k=>$v) {
                            $options[intval($v['classid'])] = ($v['is_modifier'] == 'T' || $v['is_modifier'] == 'A') ? $v["option_name"] : $v["optionid"];
                        }
                    }

                    if ($all_languages && is_array($all_languages) && count($all_languages) > 1 && !empty($active_modules['Product_Options'])) {
                        foreach($all_languages as $lng) {
                            $options_alt[$lng['code']] = func_serialize_options($options, false, $lng['code']);
                        }
                    }

                    $product['product_options'] = func_serialize_options($options);
                }

            } else {

                $product['product_options'] = '';

            }

            // Save extra order data for AOM
            $product['extra_data']['price_precise']               = $product['price'];
            $product['extra_data']['product_options']             = $options;
            $product['extra_data']['product_options_alt']         = $options_alt;

            $product['extra_data']['taxes']                       = $product['taxes'];

            $product['extra_data']['display']['price']            = doubleval($product['display_price']);
            $product['extra_data']['display']['discounted_price'] = doubleval($product['display_discounted_price']);
            $product['extra_data']['display']['subtotal']         = doubleval($product['display_subtotal']);

            // Save product price
            $product['price']                                     = price_format($product['price']);

            // For AOM func_is_shipping_method_allowable function bt:#92268
            $product['extra_data']['subtotal'] = $product['subtotal'];
            $product['extra_data']['weight'] = $product['weight'];


            $_query_data = array(
                'orderid' => $cart['orderid'],
                'productid' => $product['productid'],
                'product_options' => $product['product_options'],
                'amount' => $product['amount'],
                'price' => $product['price'],
                'provider' => $product['provider'],
                'extra_data' => serialize($product['extra_data']),
                'productcode' => $product['productcode'],
                'product' => $product['product']
            );

            if (!empty($product['itemid'])) {
                $_query_data['itemid'] = $product['itemid'];
            }

            $query_data = func_array_map('addslashes', $_query_data);

            $items[] = $products[$pk]['itemid'] = func_array2insert("order_details", $query_data, true);

        }
        db_query("DELETE FROM $sql_tbl[order_details] WHERE orderid='$cart[orderid]' AND itemid NOT IN ('".implode("','", $items)."')");

        if (!empty($partner) && !empty($active_modules['XAffiliate'])) {
            $_products = $products;
            $ps = array();
            $orderid = $cart['orderid'];
            foreach($products as $v) {
                $ps[$v['provider']][] = $v;
            }

            // Get partner commision record creation date
            $xaff_force_time = func_query_first_cell("SELECT add_date FROM $sql_tbl[partner_payment] WHERE orderid = '$orderid'");
            if (empty($xaff_force_time))
                $xaff_force_time = func_query_first_cell("SELECT date FROM $sql_tbl[orders] WHERE orerid = '$orderid'");
            if (empty($xaff_force_time))
                $xaff_force_time = XC_TIME;

            db_query("DELETE FROM $sql_tbl[partner_payment] WHERE orderid = '$cart[orderid]'");
            db_query("DELETE FROM $sql_tbl[partner_product_commissions] WHERE orderid = '$cart[orderid]'");

            foreach($ps as $k => $products) {
                $current_order['provider'] = $k;
                include $xcart_dir.'/include/partner_commission.php';
            }
            $products = $_products;
            unset($_products, $current_order, $ps);
        }

        $_products = $products;
        $ps = array();
        $orderid = $cart['orderid'];
        foreach($products as $v) {
            $ps[$v['provider']][] = $v;
        }

        // Get provider commision record creation date
        $commission_force_time = func_query_first_cell("SELECT add_date FROM $sql_tbl[provider_commissions] WHERE orderid = '$orderid'");
        if (empty($commission_force_time)) {
            $commission_force_time = func_query_first_cell("SELECT date FROM $sql_tbl[orders] WHERE orderid = '$orderid'");
        }
        if (empty($commission_force_time)) {
            $commission_force_time = XC_TIME;
        }

        // Get provider paid commisions value
        db_query("DELETE FROM $sql_tbl[provider_product_commissions] WHERE orderid = '$cart[orderid]'");

        $current_order['orderid'] = $orderid;
        foreach($ps as $k => $products) {
            $current_order['provider'] = $k;
            $paid_commissions_details = func_query_first("SELECT paid_commissions, note FROM $sql_tbl[provider_commissions] WHERE orderid = '$orderid' AND userid = '$current_order[provider]'");
            db_query("DELETE FROM $sql_tbl[provider_commissions] WHERE orderid = '$cart[orderid]' AND userid = '$current_order[provider]'");
            // Provider commission
            func_calculate_provider_commission($products, $current_order, $commission_force_time, $paid_commissions_details);
        }

        $products = $_products;
        unset($_products, $current_order, $ps);
    }

    // Update gift certificates info

    if (is_array($giftcerts)) {
        foreach ($giftcerts as $giftcert) {
            if ($giftcert['deleted']) {
                db_query("DELETE FROM $sql_tbl[giftcerts] WHERE gcid='$giftcert[gcid]' AND orderid='$cart[orderid]'");
                continue;
            }
            $old_data = func_query_first("SELECT amount, debit FROM $sql_tbl[giftcerts] WHERE gcid='$giftcert[gcid]' AND orderid='$cart[orderid]'");
            if (!empty($old_data['debit'])) {
                $amount_diff = $giftcert['amount'] - $old_data['amount'];
                $query_data = array(
                    'amount' => $giftcert['amount'],
                    'debit' => 0
                );
                if ($old_data['amount'] <= $old_data['debit']) {
                    $query_data['debit'] = $giftcert["amount"];
                } elseif (($old_data['debit'] + $amount_diff) > 0) {
                    $query_data['debit'] = $old_data["debit"] + $amount_diff;
                }
                func_array2update('giftcerts', $query_data, "gcid='$giftcert[gcid]' AND orderid='$cart[orderid]'");
            }
        }
    }

    $orderid = $cart['orderid'];
} // }}}

?>
