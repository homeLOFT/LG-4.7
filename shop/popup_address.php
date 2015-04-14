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
 * Interface for adding/editing address book entry
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Customer interface
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v40 (xcart_4_7_0), 2015-02-17 23:56:28, popup_address.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

require __DIR__.'/auth.php';

x_session_register('av_error', false);

// Process UPS suggestion
if (
    $REQUEST_METHOD == 'POST'
    && !empty($active_modules['UPS_OnLine_Tools'])
    && !empty($av_suggest)
) {

    // Shipping Address Validation by UPS OnLine Tools module
    // Apply suggestion and restore old request variables
    $av_data = func_ups_av_process_suggestion($av_suggest, $rank);

    if ($av_suggest == 'R') {

        // Restore saved data to re-enter
        $reg_error['saved_data'] = $_POST['posted_data'];
        if (!empty($_POST['additional_values'])) {
            $reg_error['saved_data_additional'] = $_POST['additional_values'];
        }

        $REQUEST_METHOD = 'GET';

    } elseif ($av_suggest == 'K') {

        // Restore and process saved data
        // Do nothing at the moment

    } elseif (
        $av_suggest == 'Y'
        && !empty($av_data)
    ) {
        // Apply suggestion
        foreach ($av_data as $f => $val) {
            $_POST['posted_data'][$f] = addslashes($val);
        }
    }

    extract($_POST);

    extract($_GET);

    $mode = 'update';
}

// Check input data
if (!$av_error) {

    $err = empty($login);

    $err = $err || ($mode != 'select' && (!empty($id) && (!is_numeric($id) || $id < 0)));

    $err = $err || ($mode == 'select' && (empty($for) || empty($type)));

    if ($err) {
        func_close_window();
    }
}

x_load('user');

x_session_register('reg_error', array());

include $xcart_dir . '/include/states.php';

include $xcart_dir . '/include/countries.php';

if ($config['General']['use_counties'] == 'Y') {

    include $xcart_dir . '/include/counties.php';

}

if ($REQUEST_METHOD == 'POST') {

    // Security check
    if (in_array($mode, array('update', 'delete', 'select')) && !empty($id)) {
        $is_owner = func_check_address_owner($logged_userid, $id);
        if (!$is_owner) {
            func_close_window();
        }
    }

    // Apply selected address to certain object
    if ($mode == 'select' && !empty($for) && !empty($id)) {
        if ($for == 'cart') {
            x_session_register('cart');
            $cart = func_set_cart_address($cart, $type, $id, '', $id);
            $res = true;

            
            // Keep default shipping address if billing address is changed for Fast_Lane_Checkout module bt:108303
            if (
                $checkout_module == 'Fast_Lane_Checkout'
                && $type == 'B'
                && !func_get_cart_address('S')
            ) {
                $def_s_addresses = func_get_default_address($logged_userid, 'S');
                $def_s_id = !empty($def_s_addresses['S']['id']) ? $def_s_addresses['S']['id'] : $def_s_addresses['B']['id'];

                if (!empty($def_s_id)) {
                    $cart = func_set_cart_address($cart, 'S', $def_s_id, '', $def_s_id);
                }
            }
        }

        if (
            $checkout_module == 'One_Page_Checkout'
            && func_is_ajax_request()
        ) {
            func_register_ajax_message(
                'opcUpdateCall',
                array(
                    'action'  => 'selectAddress',
                    'status'  => 1,
                    'content' => func_get_langvar_by_name('txt_address_selected', false, false, true)
                )
            );
        }
    }

    $errors = false;

    // Add/update address
    if (($mode == 'update' || $mode == 'add') && !empty($posted_data)) {

        $result = func_check_address($posted_data, 'C');

        $additional_fields = func_get_additional_fields('C', '', 'B');
        if (!empty($additional_fields)) {
            $result_additional = func_check_address_additional_fields($additional_values, 'C', $additional_fields);
            if (!empty($result_additional['errors'])) {
                if (empty($result['errors'])) {
                    $result['errors'] = $result_additional['errors'];
                    $result['status'] = $result_additional['status'];
                } else {
                    $result['errors'] = array_merge($result['errors'], $result_additional['errors']);
                }
            }
        }

        if (!empty($result['errors'])) {
            $errors = $result['errors'];

            // Prepare errors data
            if (!$is_ajax_request) {
                $top_message = array(
                    'content' => func_get_langvar_by_name('txt_registration_error'),
                    'type'    => 'E'
                );
            }

            $reg_error = func_prepare_error($errors);
            $reg_error['saved_data'] = func_stripslashes($posted_data);
            if (!empty($additional_values)) {
                $reg_error['saved_data_additional'] = func_stripslashes($additional_values);
            }
        }
        else {
            $result = func_save_address($logged_userid, $id, $posted_data);
            if (!empty($result) && !empty($additional_values)) {
                func_save_address_additional_values((!empty($id) ? $id : $result['addressid']), $additional_values);
            }
            if (!empty($active_modules['TwoFactorAuth'])) {
                func_twofactor_on_phone_update($logged_userid);
            }
            $res = true;
        }
    }

    // Delete address
    if ($mode == 'delete') {
        $res = func_delete_address($id);
    }

    if ($res) {

        x_load('cart');
        func_cart_set_flag('need_recalculate', true);

        if ($mode != 'select') {
            $top_message = array(
                'type'    => 'I',
                'content' => func_get_langvar_by_name('txt_address_' . $mode . '_success')
            );
        }

        if (
            $is_ajax_request
            && !(
                $checkout_module == 'One_Page_checkout'
                && $mode == 'select'
            )
        ) {
            func_reload_parent_window();
        }

    }

    if (func_is_ajax_request()) {

        func_register_ajax_message(
            'popupDialogCall',
            array(
                'action' => 'load',
                'src'    => 'popup_address.php?mode=' . $mode . '&id=' . $id
            )
        );

    }

    func_header_location('popup_address.php?mode=' . $mode . '&id=' . $id);
}

$address_additional_fields = func_get_additional_fields('C', '', 'B');
$address_additional_values = array();

if (!empty($id)) {

    // Get address details
    $address = func_get_address($id);

    // Security check
    if (empty($address) || $address['userid'] != $logged_userid) {
        func_close_window();
    }

    $smarty->assign('address', $address);
    $smarty->assign('id', $id);

} else {

    // Check if there are any addresses already filled in
    $is_address_book_empty = func_is_address_book_empty($logged_userid);
    $smarty->assign('is_address_book_empty', $is_address_book_empty);
}

// Assign error data
if (!empty($reg_error)) {
    $address_additional_values = $reg_error['saved_data_additional'];
    $smarty->assign('reg_error', $reg_error);
    $smarty->assign('address', func_prepare_address($reg_error['saved_data']));
    $reg_error = array();
}

// Address validation error for anonymous customers
if (
    $av_error
    && $is_anonymous
) {
    $smarty->assign('address', $av_error['params']);
}

if ($mode != 'select' || isset($id)) {

    if (!empty($address_additional_fields) && $id > 0) {

        if (empty($address_additional_values)) {
            $address_additional_values = func_get_address_additional_values($id);
        }

        $address_additional_fields = func_set_address_additional_values($address_additional_fields, $address_additional_values, true);

    }

    $smarty->assign('additional_fields', $address_additional_fields);

    $location = array(
        array(func_get_langvar_by_name($id > 0 ? 'lbl_edit_address' : 'lbl_new_address'))
    );

    $smarty->assign('template_name', 'customer/main/address.tpl');

} else {

    $location = array(
        array(func_get_langvar_by_name('lbl_select_address'))
    );

    $addresses = func_get_address_book($logged_userid);
    $smarty->assign('addresses', $addresses);

    if (!empty($address_additional_fields)) {
        $address_book_additional_fields = func_get_address_book_additional_fields($addresses, $address_additional_fields);
        $smarty->assign('address_book_additional_fields', $address_book_additional_fields);
    }   

    if (
        $for == 'cart'
    ) {
        $_cart_address = func_get_cart_address($type);
        if (
            !empty($_cart_address)
            && is_numeric($_cart_address)
        ) {
            $smarty->assign('current', $_cart_address);
        }
    }

    $smarty->assign('template_name', 'customer/main/address_book.tpl');
}

$default_fields = func_get_default_fields('C', 'address_book');
$smarty->assign('default_fields', $default_fields);

// Assign smarty variables
$smarty->assign('mode',     $mode);
$smarty->assign('for',      $for);
$smarty->assign('type',     @$type);
$smarty->assign('return',   @$return);

$smarty->assign('av_error', $av_error);

$smarty->assign('titles', func_get_titles());

func_display('customer/help/popup_info.tpl', $smarty);
?>
