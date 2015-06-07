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
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2013 Qualiteam software Ltd <info@x-cart.com>. All rights reserved
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    $Id$
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

require './auth.php';

if (empty($active_modules['Abandoned_Cart_Reminder'])) {
    exit;
}

if (func_is_ajax_request() && $mode == 'save_cart' && !empty($user_email) && empty($logged_userid)) {

    x_load('mail');

    if (!func_check_email($user_email)) {
        exit;
    }

    x_session_register('cart');
    x_session_register('abcr_session_flags', array());
    $save_cart = $cart;

    if (empty($save_cart['userinfo'])) {
        $save_cart['userinfo'] = array();
    }

    // Set email
    $save_cart['userinfo']['email'] = $user_email;

    if (!empty($address_book)) {
        // Validate and set address fields
        $address_fields = array_keys(func_get_default_fields('C', 'address_book', true, true));

        foreach ($address_book as $addrid => $address) {
            if (!in_array($addrid, array('B', 'S'))) {
                unset($address_book[$addrid]);
                continue;
            }
            $prefix = strtolower($addrid) . '_';
            foreach ($address as $field => $value) {
                if (!in_array($field, $address_fields) || empty($value)) {
                    unset($address_book[$addrid][$field]);
                    continue;
                }
                $save_cart['userinfo'][$prefix . strtolower($field)] = $value;
            }

        }
        if (!empty($address_book['B'])) {
            foreach ($address_book as $addrid => $address) {
                if (!empty($address['country'])) {
                    $address_book[$addrid]['countryname'] = func_get_country($address['country']);
                }
                if (!empty($address['state'])) {
                    $address_book[$addrid]['statename'] = func_get_state($address['state'], $address['country']);
                }
            }
            if (empty($ship2diff) || empty($address_book['S'])) {
                $address_book['S'] = $address_book['B'];
                foreach ($address_book['S'] as $field => $value) {
                    $save_cart['userinfo']['s_' . strtolower($field)] = $value;
                }
            }
            $save_cart['userinfo']['address'] = $address_book;
        }
    }

    if (!isset($abcr_session_flags['previous_email'])) {
        $abcr_session_flags['previous_email'] = '';
    }

    if (!empty($abcr_session_flags['previous_email']) && $abcr_session_flags['previous_email'] != $user_email) {
        func_abcr_delete_abandoned_cart($abcr_session_flags['previous_email']);
    }

    func_abcr_save_abandoned_cart(0, $save_cart);

    $abcr_session_flags['previous_email'] = $user_email;
    x_session_save();

    echo 'OK';
    exit;
}

if (!func_abcr_is_valid_request($cart_skey)) {
    func_header_location('home.php');
}

$abcr_request   = func_abcr_parse_cart_request($cart_skey);
$abcr_cart      = func_abcr_get_abandoned_cart($abcr_request['email']);

x_session_register('cart');
x_session_register('abcr_session_flags', array());
$abcr_session_flags['returned_customer'] = true;

$cart['products'] = array();

if (
    isset($abcr_cart['abandoned_cart']['products'])
    && is_array($abcr_cart['abandoned_cart']['products'])
) {
    $cart = func_abcr_populate_cart($abcr_cart['abandoned_cart']['products']);
}

if (!empty($abcr_cart['coupon_data'])) {
    $cart = func_abcr_apply_coupon($cart, $abcr_cart['coupon_data']['coupon']);
}

if (
    !empty($abcr_cart['userid'])
    && ($abcr_cart['userid'] > 0)
) {
    func_authenticate_user($abcr_cart['userid']);
} else {

    $skip = empty($abcr_cart['customer_info']['address']);

    if (!$skip) {
        foreach ($abcr_cart['customer_info'] as $k => $v) {
            if ($k != 'email' && $k != 'language') {
                $skip = empty($v);
                if (!$skip) {
                    break;
                }
            }
        }
    }

    if (!$skip) {
        $address_fields = func_get_default_fields('C', 'address_book');
        foreach ($address_fields as $field => $field_status) {
            foreach ($abcr_cart['customer_info']['address'] as $addrid => $address) {
                if ($field_status['required'] == 'Y' && empty($address[$field])) {
                    $skip = true;
                    break;
                }
            }
        }
    }

    if (!$skip) {
        func_set_anonymous_userinfo($abcr_cart['customer_info'], false, false);
    }
}

func_header_location($http_location . DIR_CUSTOMER . '/cart.php?mode=checkout');
