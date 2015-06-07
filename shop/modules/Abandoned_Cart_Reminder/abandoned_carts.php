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
 * @copyright  Copyright (c) 2001-2013 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    $Id$
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if (!defined('XCART_START')) {
    header('Location: ../../');
    die('Access denied');
}

$location[] = array(func_get_langvar_by_name('lbl_abcr_abandoned_carts'), '');

// Handling search params and writing them into session variable
x_session_register('abcr_search_params');

$abcr_default_search_params = array(
    'end_time'      => time() + SECONDS_PER_DAY,
    'start_time'    => time() - ($config['Abandoned_Cart_Reminder']['abcr_expire_after'] + 1) * SECONDS_PER_DAY,
    'abcr_pattern'  => '',
);

$abcr_search_params = func_array_merge_assoc(
    $abcr_default_search_params,
    $abcr_search_params
);

if (isset($end_time)) {
    $abcr_search_params['end_time'] = func_prepare_search_date($end_time, true);
    $abcr_search_params['is_new_search'] = true;
}

if (isset($start_time)) {
    $abcr_search_params['start_time'] = func_prepare_search_date($start_time);
    $abcr_search_params['is_new_search'] = true;
}

if (isset($abcr_pattern)) {
    // for backward compatibility with early versions of X-Cart
    $abcr_search_params['abcr_pattern'] = addslashes(stripslashes($abcr_pattern));
    $abcr_search_params['is_new_search'] = true;
}

if (
    isset($page)
    && is_int($page) 
    && $page > 0
) {

    $abcr_search_params['page'] = intval($page);

} elseif (
    !isset($abcr_search_params['page'])
    || isset($abcr_search_params['is_new_search'])
) {

    $abcr_search_params['page'] = 1;

}

if (
    !isset($mode)
    || empty($mode)
) {
    $mode = 'search';
}
// end handling input params

// Handling POST requests
if ($REQUEST_METHOD == 'POST') {

    if ('delete' == $mode) {

        // Delete abandoned carts

        foreach ($abandoned_carts as $email => $value) {

            if ('on' == $value) {
                func_abcr_delete_abandoned_cart($email);
            }

        }

    } elseif ('notify' == $mode) {

        // Notify abandoned carts

        // Check if there are abandoned carts to notify
        if (
            empty($abandoned_carts)
            || !is_array($abandoned_carts)
        ) {
            $top_message['content'] = func_get_langvar_by_name('msg_abcr_adm_no_abandoned_carts_selected');
            $top_message['type']    = 'E';
        }

        // Create coupon_settings variable 
        // It must be either array or null, if no coupon is attached
        if (
            isset($abcr_attach_coupon_group)
            && 'on' == $abcr_attach_coupon_group
            && $single_mode
        ) {

            $abcr_coupon_expire_group = func_prepare_search_date($abcr_coupon_expire_group, true);
            $coupon_settings = func_abcr_create_coupon_settings($abcr_coupon_type_group, $abcr_coupon_value_group, $abcr_coupon_expire_group);

            if (!is_array($coupon_settings)) {
                $top_message['content'] = func_get_langvar_by_name('msg_abcr_adm_coupon_parameters_incorrect');
                $top_message['type']    = 'E';
            }
                
        } else {
            $coupon_settings = null;
        }

        $abcr_success_carts = array();
        $abcr_failed_carts = array();

        // If no errors then run the notification process
        if (empty($top_message)) {

            foreach ($abandoned_carts as $email => $value) {

                if ('on' == $value) { 
                    $abcr_cart = func_abcr_get_abandoned_cart($email);

                    if (is_array($coupon_settings)) {
                        $provider = func_abcr_detect_cart_provider($abcr_cart);
                        $abcr_cart['coupon_data'] = func_abcr_create_coupon($email, $coupon_settings, $provider);
                    }

                    $result = func_abcr_send_message_to_abandoned_cart($abcr_cart);

                    if ($result) {
                        $abcr_success_carts[] = $email;
                    } else {
                        $abcr_failed_carts[] = $email;
                    }
                }

            } // end foreach ($abandoned_cart as $email => $value)

            if (!empty($abcr_failed_carts)) { 
            
                // If any notifications were not sent
                if (1 == count($abcr_failed_carts)) {
                    $top_message['content'] = func_get_langvar_by_name('msg_abcr_adm_notification_to_customer_was_not_sent', array('customer' => implode(', ', $abcr_failed_carts)));
                } else {
                    $top_message['content'] = func_get_langvar_by_name('msg_abcr_adm_notifications_to_customers_were_not_sent', array('customers' => implode(', ', $abcr_failed_carts)));
                }

                $top_message['type'] = 'E';

            } elseif (empty($top_message)) { 
            
                // If all notifications were sent properly AND there were no other errors
                if (1 == count($abcr_success_carts)) {
                    $top_message['content'] = func_get_langvar_by_name('msg_abcr_adm_notification_was_sent', array('customer' => implode(', ', $abcr_success_carts)));
                } else {
                    $top_message['content'] = func_get_langvar_by_name('msg_abcr_adm_notifications_were_sent', array('customers' => implode(', ', $abcr_success_carts)));
                }
            }
        }

    } elseif ('create_order' == $mode && ('A' == $current_area || !empty($active_modules['Simple_Mode']) || $single_mode)) {

        // Creating order for an abandoned cart

        if (empty($active_modules['Advanced_Order_Management'])) {
            func_403();
        }

        if (isset($abcr_create_order_email)) {
            $abcr_cart = func_abcr_get_abandoned_cart($abcr_create_order_email);

            if ($abcr_cart != false) {
                $orderid = func_abcr_create_order($abcr_cart);
                x_session_unregister('cart_tmp');
                func_header_location('order.php?orderid=' . $orderid . '&mode=edit&show=products');
            }
        }
    }

    func_header_location('abandoned_carts.php?page=' . $abcr_search_params['page']);
}
// end handling POST requests 

// Pagination
$objects_per_page = ABCR_ABANDONED_CARTS_PER_PAGE_ADMIN;

$search_params = array(
    'start'     => $abcr_search_params['start_time'],
    'end'       => $abcr_search_params['end_time'],
    'pattern'   => $abcr_search_params['abcr_pattern'],
);

$total_items = func_abcr_search_abandoned_carts($search_params, true); 
$page = $abcr_search_params['page'];

include $xcart_dir . '/include/navigation.php';
$smarty->assign('navigation_script', 'abandoned_carts.php?mode=' . $mode);
// end pagination

$search_params['offset_params'] = array(
    'how_many' => $objects_per_page,
    'start' => ($page - 1) * $objects_per_page,
);

unset($abcr_search_params['is_new_search']);

$abandoned_carts = func_abcr_search_abandoned_carts($search_params);

foreach ($abandoned_carts as $k => $abcr_cart) {

    $abandoned_carts[$k]['products_detailed'] = func_abcr_get_products($abcr_cart['abandoned_cart']['products'], $abcr_cart['customer_info']['membershipid']);
    $abandoned_carts[$k]['subtotal'] = func_abcr_get_products_subtotal($abandoned_carts[$k]['products_detailed']);
    $abandoned_carts[$k]['time'] += $config['Appearance']['timezone_offset']; 

}

// Assign the current location line
$smarty->assign('location', $location);

// Assign search params
$abcr_search_params['abcr_pattern'] = stripslashes($abcr_search_params['abcr_pattern']);
$smarty->assign('abcr_search_prefilled', $abcr_search_params);

// Prepare and assign default search values
$tmp_default_values = $abcr_default_search_params;
$abcr_default_search_params = array();

foreach ($tmp_default_values as $param => $value) {

    if (in_array($param, array('start_time', 'end_time'))) {
        $abcr_default_search_params['f_' . $param] = func_strftime($config['Appearance']['date_format'], $value - SECONDS_PER_DAY);
    } else {
        $abcr_default_search_params[$param] = $value;
    }

}

$address_fields = func_get_default_fields('C', 'address_book', true, true);
$smarty->assign('default_fields', $address_fields);
$smarty->assign('address_types', array('B', 'S'));

$smarty->assign('abcr_default_search_params', $abcr_default_search_params);

$smarty->assign('main', 'abandoned_carts');
$smarty->assign('group_expiration_date', time() + $config['Abandoned_Cart_Reminder']['abcr_expire_after'] * SECONDS_PER_DAY);
$smarty->assign('abandoned_carts', $abandoned_carts);

if (
    file_exists($xcart_dir . '/modules/gold_display.php')
    && is_readable($xcart_dir . '/modules/gold_display.php')
) 
{
    include $xcart_dir . '/modules/gold_display.php';
}

$display_area = ($current_area == 'A' || $single_mode) ? 'admin' : 'provider';

func_display($display_area . '/home.tpl', $smarty);

?>
