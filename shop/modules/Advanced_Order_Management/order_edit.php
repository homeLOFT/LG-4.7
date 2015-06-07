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
 * Order editing interface
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Modules
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    e44b86db8de0d5fb6c830b5a545165043a629b0c, v176 (xcart_4_7_1), 2015-03-09 10:35:32, order_edit.php, aim
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if ( !defined('XCART_SESSION_START') ) { header("Location: ../../"); die("Access denied"); }

require $xcart_dir.'/modules/Advanced_Order_Management/func.edit.php';

$location[count($location)-1][1] = "order.php?orderid=" . intval($orderid);
$location[] = array(func_get_langvar_by_name('lbl_advanced_order_management'), '');

$global_store = array();

x_session_register('intershipper_rates');
x_session_register('intershipper_recalc');
x_session_register('current_carrier','UPS');
x_session_register('dhl_ext_country_store');

if (isset($dhl_ext_country)) {
    $dhl_ext_country_store = $dhl_ext_country;
} else {
    $dhl_ext_country = $dhl_ext_country_store;
}

define ('XAOM', true);

$intershipper_recalc = 'Y';

if ($mode != 'edit' || empty($active_modules['Advanced_Order_Management'])) {
    func_403(39);
}

if (!defined('IS_ADMIN_USER')) {
    func_403(40);
}

$smarty->assign(
    'default_fields',
    func_get_default_fields('H', 'user_profile')
);

$smarty->assign(
    'address_fields',
    func_get_default_fields('H', 'address_book')
);

if (defined('DEVELOPMENT_MODE')) {
    register_shutdown_function('func_aom_dev_check_non_saved_anonymous_userinfo');
}

/**
 * Check if the modification of the order is permitted
 */
if (!$single_mode) {  // in PRO mode
    $providers_array = array();
    foreach ($order_data['products'] as $k=>$v) {
        if (empty($providers_array[$v['provider']])) {
            $providers_array[$v['provider']] = 0;
        }
        $providers_array[$v['provider']]++;
    }
    if (count($providers_array) > 1) {
        $smarty->assign('rejected', 'Y');
        $smarty->assign('main','order_edit');
        if (is_readable($xcart_dir.'/modules/gold_display.php')) {
            include $xcart_dir.'/modules/gold_display.php';
        }
        func_display('admin/home.tpl',$smarty);
    }
}

/**
 * Get show mode and assign it to Smarty
 */
if (!isset($show) || !in_array(strtolower($show), array('preview','products','giftcerts','customer','totals'))) {
    $show = 'preview';
    if ($action != 'save') {
        $initial_point = true;
    }
}
$smarty->assign('show', $show);

/**
 * Restore original order tax settings
 */
if (
    !empty($order_data['order']['extra']['tax_info']['display_taxed_order_totals'])
    && in_array($order_data['order']['extra']['tax_info']['display_taxed_order_totals'], array('Y','N'))
) {
    $config['Taxes']['display_taxed_order_totals'] = $order_data['order']['extra']['tax_info']['display_taxed_order_totals'];
}
if (
    !empty($order_data['order']['extra']['tax_info']['display_cart_products_tax_rates'])
    && in_array($order_data['order']['extra']['tax_info']['display_cart_products_tax_rates'], array('Y','N'))
) {
    $config['Taxes']['display_cart_products_tax_rates'] = $order_data['order']['extra']['tax_info']['display_cart_products_tax_rates'];
}
if (
    !empty($order_data['order']['extra']['tax_info']['tax_operation_scheme'])
    && in_array($order_data['order']['extra']['tax_info']['tax_operation_scheme'], XCTaxesDefs::getAvaliableTaxSchemes())
) {
    $config['Taxes']['tax_operation_scheme'] = $order_data['order']['extra']['tax_info']['tax_operation_scheme'];
}

$smarty->assign('config', $config);

/**
 * Register temporary order in the session
 */
if (!x_session_is_registered('cart_tmp') || !empty($initial_point)) {

    $cart_tmp = $order_data['order'];
    $cart_tmp['orders'] = $order_data['order'];
    $cart_tmp['total_cost'] = $order_data['order']['total'];
    $cart_tmp['giftcerts'] = $order_data['giftcerts'];
    $cart_tmp['products'] = $order_data['products'];
    $cart_tmp['userinfo'] = $order_data['userinfo'];

    $cart_tmp['userinfo']['userid'] = isset($cart_tmp['userinfo']['userid']) ? $cart_tmp['userinfo']['userid'] : 0;
    $cart_tmp['userinfo']['login'] = isset($cart_tmp['userinfo']['login']) ? $cart_tmp['userinfo']['login'] : '';

    // Overwrite data from func_userinfo-address-book with customer data from xcart_orders
    if (!empty($cart_tmp['userinfo']['userid'])) {
        $cart_tmp['userinfo']['address']['S'] = func_prepare_address(func_create_address($cart_tmp['userinfo'], 'S'));
        $cart_tmp['userinfo']['address']['B'] = func_prepare_address(func_create_address($cart_tmp['userinfo'], 'B'));
    }

    // Initialize discount and coupon data
    func_aom_initialize_order_discount_data($cart_tmp);
    func_aom_initialize_order_coupon_discount_data($cart_tmp);

    // Initialize shipping data
    func_aom_initialize_order_shipping_data($cart_tmp);

    if (is_array($cart_tmp['products'])) {
        foreach ($cart_tmp['products'] as $k => $v) {
            $cart_tmp['products'][$k]['cartid'] = $k;
            $cart_tmp['products'][$k]['free_price'] = $v['price'];
            $cart_tmp['products'][$k]['price'] = $v['extra_data']['price_precise'];
            $cart_tmp['products'][$k]['taxed_price'] = $v['display_price'];
            if ($v['product_type'] == 'C') {
                $cart_tmp['products'][$k]['options_surcharge'] = $v['price'];
            }
            if (!empty($active_modules['Product_Options'])) {
                $cart_tmp['products'][$k]['keep_options'] = 'Y';
            }
        }
    }

    // Initialize additional fields
    func_aom_initialize_additional_fields_data($cart_tmp);

    // Initialization global var for func_calculate_single bt:0095797
    foreach ($cart_tmp['products'] as $k => $v) {
        $global_store['product_taxes'][$v['productid']] = (!empty($v['extra_data']['taxes'])) ? $v['extra_data']['taxes'] : false;
    }

    // Global tax names for func_get_product_tax_rate function bt:0096284
    if (!empty($order['applied_taxes']) && is_array($order['applied_taxes'])) {
        foreach ($order['applied_taxes'] as $k => $v) {
            $global_store['tax_display_names'][$k] = func_get_order_tax_name($v);
        }
    }
}

x_session_register('cart_tmp', (!empty($cart_tmp) ? $cart_tmp : array()));

if ($action == 'delete') {
/**
 * Update order info in the database
 */
    if ($REQUEST_METHOD == 'POST' && $confirmed == 'Y') {
        func_delete_order($orderid);
        $show = 'preview';
        x_session_unregister('cart_tmp');
        func_header_location('orders.php');
    }
    else {
        $show = 'preview';
        $smarty->assign('confirmation', 'Y');
        $smarty->assign('confirm_deletion', 'Y');
    }
}
elseif ($action == 'save') {
/**
 * Update order info in the database
 */
    if ($REQUEST_METHOD == 'POST' && $confirmed == 'Y') {

        func_update_order($cart_tmp, $order_data);

        // Write changes to the history

        $old_order_data = $order_data;
        $order_data = func_order_data($orderid);

        $diff = func_aom_prepare_diff('A', $order_data, $old_order_data, $cart_tmp);
        $details = array(
            'old_status' => $old_order_data['order']['status'],
            'new_status' => $order_data['order']['status'],
            'diff' => $diff,
            'comment' => stripslashes($history_comment),
            'is_public' => !empty($history_is_public) ? 'Y' : 'N',
            'is_edit' => true
        );

        func_aom_save_history($orderid, 'A', $details);

        $show = 'preview';
        x_session_register('message');
        $message = 'saved';
        x_session_unregister('cart_tmp');

        $notify = (!empty($notify_customer) || !empty($notify_provider) || !empty($notify_orders_dept));
        if ($notify && !empty($order_data)) {
            $mail_smarty->assign('products',$order_data['products']);
            $mail_smarty->assign('giftcerts',$order_data['giftcerts']);
            $mail_smarty->assign('userinfo',$order_data['userinfo']);
            $mail_smarty->assign('order',$order_data['order']);

            // Send notification to customer
            if (!empty($notify_customer)) {
                func_send_mail($order_data['userinfo']['email'], 'mail/order_updated_customer_subj.tpl', 'mail/order_updated_customer.tpl', $config['Company']['orders_department'], false);
            }

            // Send notification to Orders department
            if (!empty($notify_orders_dept)) {
                func_send_mail($config['Company']['orders_department'], 'mail/order_updated_subj.tpl', 'mail/order_updated.tpl', $config['Company']['site_administrator'], false);
            }

            // Send notification to provider
            if (!empty($notify_provider)) {
                $provider_data = func_query_first("SELECT email, language FROM $sql_tbl[customers] WHERE id='".$order_data["products"][0]["provider"]."'");
                if (!empty($provider_data)) {
                    list($email_pro, $to_customer) = array_values($provider_data);
                }
                if (!empty($email_pro) && !($notify_orders_dept && $email_pro == $config['Company']['orders_department'])) {
                    if (empty($to_customer)) {
                        $to_customer = $config['default_admin_language'];
                    }

                    func_send_mail($email_pro, 'mail/order_updated_subj.tpl', 'mail/order_updated.tpl', $config['Company']['orders_department'], false);
                }
            }
        }

        func_header_location("order.php?orderid=$orderid&mode=edit");
    }
    else {
        $show = 'preview';
        $smarty->assign('confirmation', 'Y');
    }
}
elseif ($action == 'cancel') {
/**
 * Cancel order modifications and go to preview
 */
    $show = 'preview';
    $smarty->assign('message', 'cancel');
    x_session_unregister('cart_tmp');
}

if (x_session_is_registered('message')) {
    x_session_register('message');
    $smarty->assign('message', $message);
    x_session_unregister('message');
}

$customer_membershipid = $cart_tmp['userinfo']['membershipid'];

/**
 * Process and update orders data
 */
if ($REQUEST_METHOD == 'POST') {

    $cart_tmp['flag_change'] = true;

    if ($action == 'update_products') {

        if (!empty($product_details) && is_array($product_details)) {

            foreach ($product_details as $k => $v) {

            // Update ordered product details

                $productid = $cart_tmp['products'][$k]['productid'];

                // Set 0 values for deleted product to avoid notices 
                if (!isset($v['amount'])) {
                    $v['amount'] = 0;                
                } else {
                    $v['amount'] = intval($v['amount']);
                }
                if (!isset($v['price'])) {
                    $v['price'] = 0;
                }

                // Check if product is out of stock
                $count_product_in_stock = func_get_quantity_in_stock(
                    $productid,
                    $order_data['order']['status'],
                    (!empty($v['product_options'])) ? $v['product_options'] : false,
                    (!empty($order_data['products'][$k])) ? $order_data['products'][$k] : false
                );

                if ($v['amount'] > 0) {
                    if (
                        $config['General']['unlimited_products'] == 'Y'
                        || empty($v['stock_update'])
                        || (
                            $v['stock_update'] == 'Y'
                            && $v['amount'] <= $count_product_in_stock
                        )
                    ) {
                        // stock not controlled
                        // or
                        // not requested to be updated
                        // or
                        // it should be updated and it covers the requested quantity
                        $cart_tmp['products'][$k]['amount'] = $v['amount'];

                    } else {
                        $top_message['content'] = func_get_langvar_by_name('txt_aom_product_stock_update_error');
                        $top_message['type'] = 'E';
                        func_header_location("order.php?orderid=$orderid&mode=edit&show=products");
                    }
                }

                // Delete products from order or mark / unmark deleted
                if (!empty($v['delete']) && $v['delete'] == $productid) {
                    if ($cart_tmp['products'][$k]['new'] == 'Y') {
                        // Products that were added via current edit order session are deleted
                        unset($cart_tmp['products'][$k]);
                    } else {
                        // Mark / Unmark products as 'deleted'
                        $cart_tmp['products'][$k]['deleted'] = (!empty($cart_tmp['products'][$k]['deleted']) ? false : true);
                    }
                    continue;
                }

                $v['price'] = func_aom_validate_price($v['price']);

                if (isset($v['display_price_alt'])) {
                    // Validate display price value
                    $v['display_price_alt'] = func_aom_validate_price($v['display_price_alt']);

                    if (!empty($v['display_price_alt'])) {
                        $cart_tmp['products'][$k]['display_price_alt'] = $v['price'] = $v['display_price_alt'];
                    }
                }

                $product_options_result = array();
                $product_options_txt = '';

                if (!empty($active_modules['Product_Options']) && (!empty($v['product_options']) || !empty($v['keep_options']))) {

                    // Update product options selected

                    if ($v['keep_options'] == 'Y') {
                        // Keep originally selected options
                        $product_options_result = $cart_tmp['products'][$k]['product_options'] = $products[$k]['product_options'];
                        $v['product_options'] = $products[$k]['extra_data']['product_options'];
                    } else {
                        // Process selected options
                        if (!func_check_product_options($productid, $v['product_options'])) {
                            $v['product_options'] = func_get_default_options($productid, $v['amount'], $cart_tmp['userinfo']['membershipid']);
                        }
                        list($variant, $product_options_result) = func_get_product_options_data($productid, $v['product_options'], $cart_tmp['userinfo']['membershipid']);
                    }

                    $cart_tmp['products'][$k]['options_surcharge'] = 0;
                    if (is_array($product_options_result)) {
                        foreach ($product_options_result as $key => $o) {
                            $cart_tmp['products'][$k]['options_surcharge'] += ($o['modifier_type'] == '%' ? ($v['price'] * $o['price_modifier'] / 100) : $o['price_modifier']);
                        }
                    }

                    if (!empty($variant) && !empty($variant['productcode']) && $variant['productid'] == $cart_tmp['products'][$k]['productid']) {
                        $cart_tmp['products'][$k]['productcode'] = $variant['productcode'];
                        $cart_tmp['products'][$k]['variantid'] = $variant['variantid'];
                    }

                    if ($all_languages && is_array($all_languages) && count($all_languages) > 1) {
                        if ($v['keep_options'] != 'Y') {
                            foreach ($all_languages as $lng) {
                                $product_options_alt_result[$lng['code']] = func_serialize_options($v['product_options'], false, $lng['code']);
                            }
                        } else {
                            $product_options_alt_result = (isset($cart_tmp['products'][$k]['extra_data']['product_options_alt'])) ? $cart_tmp['products'][$k]['extra_data']['product_options_alt'] : array();
                        }
                    }

                    $product_options_txt = $product_options_alt_result[$shop_language] ? $product_options_alt_result[$shop_language] : func_serialize_options($v['product_options'], false);
                }

                if ($cart_tmp['products'][$k]['product_type'] == 'C') {
                    $cart_tmp['products'][$k]['options_surcharge'] = $v['price'];
                }

                $cart_tmp['products'][$k]['price'] = $v['price'];
                $cart_tmp['products'][$k]['free_price'] = $v['price'];
                $cart_tmp['products'][$k]['weight'] = $v['weight'];

                $cart_tmp['products'][$k]['product_options'] = $product_options_result;
                $cart_tmp['products'][$k]['product_options_txt'] = $product_options_txt;
                if (!empty($product_options_txt)) {
                    $cart_tmp['products'][$k]['force_product_options_txt'] = true;
                }

                $cart_tmp['products'][$k]['extra_data']['product_options'] = (!empty($v['product_options'])) ? $v['product_options'] : array();
                $cart_tmp['products'][$k]['extra_data']['product_options_alt'] = (!empty($product_options_alt_result)) ? $product_options_alt_result : array();

                $cart_tmp['products'][$k]['stock_update'] = ($v['stock_update']) ? 'Y' : 'N';
                $cart_tmp['products'][$k]['keep_options'] = (!empty($v['keep_options'])) ? $v['keep_options'] : 'N';
            }
        }

        if (!empty($newproductid) && is_numeric($newproductid)) {

            func_aom_generate_anonymous_userinfo($cart_tmp);

            if (($prd = func_select_product($newproductid, $customer_membershipid, false, false, true))) {
                if (!$single_mode && is_array($cart_tmp['products']) && !empty($cart_tmp['products'])) {
                    $_providers = array();
                    foreach ($cart_tmp['products'] as $_product) {
                        $_providers[$_product['provider']] = 1;
                    }
                    if (!in_array($prd['provider'], array_keys($_providers))) {
                        func_aom_restore_anonymous_userinfo();
                        $top_message['content'] = func_get_langvar_by_name('txt_aom_product_provider_pro');
                        $top_message['type'] = 'E';
                        func_header_location("order.php?orderid=$orderid&mode=edit&show=products");
                    }
                }
                if ($prd['avail'] <= 0 && $config['General']['unlimited_products'] == 'N') {
                    func_aom_restore_anonymous_userinfo();
                    $top_message['content'] = func_get_langvar_by_name('txt_aom_product_is_out_of_stock');
                    $top_message['type'] = 'E';
                    func_header_location("order.php?orderid=$orderid&mode=edit&show=products");
                }

                $prd['catalog_price'] = $prd['price'];

                if (!empty($active_modules['Product_Options'])) {
                    $prd['extra_data']["product_options"] = func_get_default_options($newproductid, 1, $cart_tmp["userinfo"]['membershipid']);
                    list($variant, $product_options_result) = func_get_product_options_data($newproductid, $prd['extra_data']["product_options"], $cart_tmp["userinfo"]['membershipid']);
                    $surcharge = 0;
                    $prd['product_options'] = $product_options_result;
                    if($product_options_result) {
                        foreach ($product_options_result as $key => $o) {
                            $surcharge += ($o['modifier_type'] == '%' ? ($prd['price'] * $o['price_modifier'] / 100) : $o['price_modifier']);
                        }
                    }
                    if (!empty($variant) && !empty($variant['productcode']) && $variant['productid'] == $cart_tmp['products'][$k]['productid']) {
                        $cart_tmp['products'][$k]['productcode'] = $variant['productcode'];
                        $cart_tmp['products'][$k]['variantid'] = $variant['variantid'];
                        $cart_tmp['products'][$k]['catalog_price'] = $prd['price'] = $variant['price'];
                    }

                    $prd['price'] = price_format($prd['price'] + $surcharge);
                }
                $prd['amount'] = 1;
                $prd['new'] = true;
                // By default consider the 'Update quantity in stock after the changes are applied' checkbox is selected for just added products
                $prd['stock_update'] = 'Y';
                $cart_tmp['products'][] = $prd;
                unset($prd);

            } else {
                func_aom_restore_anonymous_userinfo();
                $top_message['content'] = func_get_langvar_by_name('txt_aom_product_cannot_be_added');
                $top_message['type'] = 'E';
                func_header_location("order.php?orderid=$orderid&mode=edit&show=products");
            }

            func_aom_restore_anonymous_userinfo();
        }

        func_header_location("order.php?orderid=$orderid&mode=edit&show=products");
    }
    elseif ($action == 'update_giftcerts') {
        if (is_array($giftcert_details)) {
            foreach ($giftcert_details as $k=>$v) {

                if (!empty($v['delete']) && $v['delete'] == $cart_tmp['giftcerts'][$k]['gcid']) {
                    // Delete or restore Gift Certificate in order
                    $cart_tmp['giftcerts'][$k]['deleted'] = ($cart_tmp['giftcerts'][$k]['deleted'] ? false : true);
                    continue;
                }

                $v['amount'] = func_convert_number($v['amount']);
                $cart_tmp['giftcerts'][$k]['amount'] = $v['amount'];
            }
        }
        func_header_location("order.php?orderid=$orderid&mode=edit&show=giftcerts");
    }
    elseif ($action == 'update_customer') {

        if (is_array($customer_info)) {
            $cart_tmp['userinfo'] = func_array_merge($cart_tmp['userinfo'], func_array_map('stripslashes', $customer_info));
            $cart_tmp['userinfo']['title'] = func_get_title($cart_tmp['userinfo']['titleid']);
            $cart_tmp['userinfo']['b_title'] = func_get_title($cart_tmp['userinfo']['b_titleid']);
            $cart_tmp['userinfo']['s_title'] = func_get_title($cart_tmp['userinfo']['s_titleid']);

            if (
                empty($cart_tmp['userinfo']['email'])
                || !func_check_email($cart_tmp['userinfo']['email'])
            ) {
                $top_message['content'] = func_get_langvar_by_name('txt_email_invalid');
                $top_message['type'] = 'E';
            }
        }

        $address_book = func_prepare_address_book_data_for_save($address_book);

        // Prepare address information
        $default_address_fields = func_get_default_fields($cart_tmp['userinfo']['usertype'], 'address_book', true, true);
        foreach ($address_book as $type => $address) {
            $address = $cart_tmp['userinfo']['address'][$type] = func_prepare_address(func_stripslashes($address));
            $cart_tmp['userinfo'] = func_userinfo_set_b_s_prefixed_fields($cart_tmp['userinfo'], $address, $type, $default_address_fields);
        }

        if(!empty($additional_fields) && is_array($additional_fields)) {
            $cart_tmp['userinfo']['additional_fields'] = $additional_fields;
        }

        // Update customer info in the 'xcart_customers' table if an order is created manually 
        // using automatically created account ('Create orders in back-end' feature)
        if (
            (!empty($order_data['order']['extra']['created_by_admin']) && $order_data['order']['extra']['created_by_admin'] == 'Y')
            && (!empty($order_data['order']['extra']['no_customer']) && $order_data['order']['extra']['no_customer'] == 'Y')
        ) {
            func_aom_update_customer_info($cart_tmp['userinfo']);
        }

        func_header_location("order.php?orderid=$orderid&mode=edit&show=customer");
    }
    elseif ($action == 'update_totals') {

        func_aom_update_order_discount($cart_tmp, $total_details);
        func_aom_update_order_coupon_discount($cart_tmp, $total_details);

        func_aom_update_order_shipping_data($cart_tmp, $total_details);
        func_aom_update_order_payment_data($cart_tmp, $total_details);

        func_header_location("order.php?orderid=$orderid&mode=edit&show=totals");
    }

    func_header_location("order.php?orderid=$orderid&mode=edit&show=preview");

}

if ($show == 'products') {
    /**
     * Get the products info
     */
    if (!empty($active_modules['Product_Options'])) {
        $ids = array();
        $options_markups = array();

        foreach($cart_tmp['products'] as $pk => $product) {
            if (isset($product['catalog_price'])) {
                $ids[$product['productid']] = $product['catalog_price'];
            }
        }

        if (!empty($ids)) {
            $options_markups = func_get_default_options_markup_list($ids);
            unset($ids);
        }
    }

    /**
     * Prepare original products data
     */
    foreach ($products as $pk => $product) {
        // Initialize original provider info
        func_aom_initialize_provider_info($products[$pk]);
    }

    /**
     * Prepare editing products data
     */
    foreach ($cart_tmp['products'] as $pk => $product) {

        $productid = $product['productid'];
        // Check if the product was not deleted from the db
        // and get the current price and amount

        $options = @$product['extra_data']['product_options'];
        if (empty($product['is_deleted']) || $product['is_deleted'] != 'Y') {
            $cart_tmp['products'][$pk]['items_in_stock'] = func_get_quantity_in_stock($productid, $order_data['order']['status'], $options, @$order_data['products'][$pk]);
            $cart_tmp['products'][$pk]['catalog_price'] = func_query_first_cell("SELECT $sql_tbl[pricing].price FROM $sql_tbl[pricing], $sql_tbl[quick_prices] WHERE $sql_tbl[quick_prices].productid = '$productid' AND $sql_tbl[quick_prices].priceid = $sql_tbl[pricing].priceid AND $sql_tbl[quick_prices].membershipid IN ('$customer_membershipid', '0')");
        }

        // Update product options with selected values

        if (!empty($active_modules['Product_Options'])) {

            if (!empty($options_markups[$productid])) {
                $cart_tmp['products'][$pk]['catalog_price'] += $options_markups[$productid];
            }


            $old_user_account = $user_account;
            $user_account = $userinfo = $cart_tmp['userinfo'];
            $old_current_area = $current_area;
            $current_area = 'C';

            $product_info = func_select_product($productid, $customer_membershipid, false, false, false, false);
            include $xcart_dir.'/modules/Product_Options/customer_options.php';

            // Check if the options were changed or deleted
            // since order placement (last edit)
            $orig_options = (!empty($products[$pk]['extra_data']['product_options'])) ? $products[$pk]['extra_data']['product_options'] : array();
            $adv_opt_choice = 'N';

            if (!isset($product['new']) || $product['new'] !== true) {
                // Compare original and currently selected options
                if (
                    !func_check_product_options($productid, $orig_options) ||
                    ( !empty($orig_options) && empty($product_options) ) ||
                    ( empty($orig_options) && !empty($product_options) ) ||
                    ( count($orig_options) != count($product_options) )
                )
                {
                    $adv_opt_choice = 'Y';
                }
            }

            $cart_tmp['products'][$pk]['adv_option_choice'] = $adv_opt_choice;

            $user_account = $old_user_account;
            $current_area = $old_current_area;

            $cart_tmp['products'][$pk]['display_options'] = $product_options;

            // Correct catalog price
            if (!empty($variants) && !empty($cart_tmp['products'][$pk]['variantid'])) {
                $vid = $cart_tmp['products'][$pk]['variantid'];
                $cart_tmp['products'][$pk]['catalog_price'] = $variants[$vid]['price'];
            }

        }

        // Initialize current provider info
        func_aom_initialize_provider_info($cart_tmp['products'][$pk]);

    } // /foreach

    // Select the product configurations for displaying

    $pconf_found = false;
    foreach ($cart_tmp['products'] as $k=>$v) {
        if (!empty($v['extra_data']['pconf']['cartid'])) {
            $cartids_[$v['extra_data']['pconf']['cartid']] = "#$v[productid]. $v[product]";
            $pconf_found = true;
        }
    }
    if ($pconf_found) {
        foreach ($cart_tmp['products'] as $k=>$v) {
            if (!empty($v['extra_data']['pconf']['parent'])) {
                $cart_tmp['products'][$k]['pconf_parent'] = $cartids_[$v['extra_data']['pconf']['parent']];
            }
        }
    }

    // Calculate totals
    $cart_tmp = func_recalculate_totals($cart_tmp);

    $smarty->assign('total_products', count($cart_tmp['products']));
    $smarty->assign('cart_products', $cart_tmp['products']);
    $smarty->assign('orig_products', $products);
} // /if ($show == 'products')

if ($show == 'giftcerts') {
    /**
     * Get the ordered gift certificates info
     */
    $smarty->assign('cart_giftcerts', $cart_tmp['giftcerts']);
    $smarty->assign('orig_giftcerts', $giftcerts);
}

if ($show == 'customer') {
    /**
     * Get the ordered customer info
     */
    include_once $xcart_dir.'/include/countries.php';
    include_once $xcart_dir.'/include/states.php';

    $smarty->assign('cart_customer', $cart_tmp['userinfo']);
    $smarty->assign('membership_levels', func_get_memberships('C', true));
}

if ($show == 'totals') {

    // Get the allowed payment methods list
    $payment_methods = func_aom_get_payment_methods_for_order($cart_tmp);

    // Get all allowed shipping methods list with rates
    $shipping = func_aom_get_shipping_rates_for_order($cart_tmp);

    // Get order coupons
    $coupons = func_aom_get_coupons_for_order($cart_tmp);

    // Calculate totals
    $cart_tmp = func_recalculate_totals($cart_tmp);

    $smarty->assign('payment_methods', $payment_methods);
    $smarty->assign('shipping', $shipping);
    $smarty->assign('coupons', $coupons);
    $smarty->assign('cart_order', $cart_tmp);
    $smarty->assign('orig_order', $order_data['order']);
    $smarty->assign('cart', $cart_tmp);
    $smarty->assign('list_length', count(@$cart_tmp['products']) + count(@$cart_tmp['giftcerts']));
    $smarty->assign('products_length', count(@$cart_tmp['products']));
}

if ($show == 'preview') {

    $cart_tmp = func_recalculate_totals($cart_tmp);

    if (!empty($initial_point)) {

        // Replace some fields by original values
        $fields_to_orig = array('shipping_cost', 'total', 'tax', 'discount', 'coupon_discount');

        foreach ($fields_to_orig as $k => $v) {
            if ($order[$v] != $cart_tmp[$v]) {
                $cart_tmp[$v] = $order[$v];
            }
        }
    }

    $smarty->assign('products', $cart_tmp['products']);
    $smarty->assign('giftcerts', $cart_tmp['giftcerts']);
    $smarty->assign('customer', $cart_tmp['userinfo']);
}

$empty_order = true;
if (is_array($cart_tmp['products'])) {
    foreach ($cart_tmp['products'] as $product) {
        if (empty($product['deleted'])) {
            $empty_order = false;
            break;
        }
    }
}
if (is_array($cart_tmp['giftcerts'])) {
    foreach ($cart_tmp['giftcerts'] as $gc) {
        if (empty($gc['deleted'])) {
            $empty_order = false;
            break;
        }
    }
}

$smarty->assign('empty_order', $empty_order);

$smarty->assign('order', $cart_tmp);

$smarty->assign('has_giftcerts', !empty($cart_tmp['giftcerts']) ? 'Y' : '');

$smarty->assign('current_carrier', $current_carrier);

$smarty->assign('titles', func_get_titles());

if (!empty($dhl_ext_country)) {
    $smarty->assign('dhl_ext_country', $dhl_ext_country);
}
if (!empty($dhl_ext_countries)) {
    $smarty->assign('dhl_ext_countries', $dhl_ext_countries);
}

/**
 * Set Smarty template to display
 */
$smarty->assign('main','order_edit');

?>
