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
 * Functions for RMA module
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Modules
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v50 (xcart_4_7_0), 2015-02-17 23:56:28, func.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if ( !defined('XCART_START') ) { header("Location: ../../"); die("Access denied"); }

/**
 * Generic function to get reasons or actions
 */
function func_get_rma_data_generic($conf_key, $lbl_pref)
{
    global $shop_language, $config;

    $values = unserialize($config[$conf_key]);

    if (
        is_array($values)
        && !empty($values)
    ) {

        foreach ($values as $k => $v) {

            $tmp = func_get_languages_alt($lbl_pref.$k, $shop_language);

            if ($tmp) {

                $values[$k] = $tmp;

            }

            $values[$k] = stripslashes($values[$k]);

        }

    } else {

        $values = array();

    }

    return $values;
}

/**
 * Get reasons
 */
function func_get_rma_reasons()
{
    return func_get_rma_data_generic('rma_reasons', 'lbl_rma_reason_');
}

/**
 * Get actions
 */
function func_get_rma_actions()
{
    return func_get_rma_data_generic('rma_actions', 'lbl_rma_action_');
}

/**
 * Get return record
 */
function func_return_data($returnid)
{
    global $sql_tbl, $active_modules, $current_area;

    x_load('order');

    $return = func_query_first("SELECT * FROM $sql_tbl[returns] WHERE returnid = '$returnid'");

    if (empty($return))
        return false;

    $orderid = func_query_first_cell("SELECT orderid FROM $sql_tbl[order_details] WHERE $sql_tbl[order_details].itemid = '$return[itemid]'");

    $tmp = func_order_data($orderid);

    if (
        empty($tmp)
        || !is_array($tmp['products'])
        || empty($tmp['products'])
    ) {
        return false;
    }

    $return['order'] = $tmp['order'];
    $return['userinfo'] = $tmp['userinfo'];

    foreach ($tmp['products'] as $v) {

        if ($v['itemid'] == $return['itemid']) {

            $return['product'] = $v;

            break;

        }

    }

    if (
        $return['status'] != 'R'
        && $current_area == 'C'
    ) {
        $return['readonly'] = 'Y';
    }

    if (empty($return['product']))
        return false;

    return $return;
}

/**
 * Send email (authorize/decline) to customer
 */
function func_rma_send($returnid)
{
    global $config, $mail_smarty;

    x_load(
        'mail',
        'user'
    );

    $return = func_return_data($returnid);

    $userinfo = $return['userinfo'];

    $mail_smarty->assign('return', $return);
    $mail_smarty->assign('userinfo', $userinfo);

    $mail_smarty->assign('reasons', func_get_rma_reasons());
    $mail_smarty->assign('actions', func_get_rma_actions());

    if ($return['status'] == 'A') {

        if ($config['RMA']['eml_rma_authorize'] == 'Y') {

            return func_send_mail(
                $userinfo['email'],
                'mail/rma_authorize_subj.tpl',
                'mail/rma_authorize.tpl',
                $config['Company']['orders_department'],
                false
            );

        }

    } elseif ($return['status'] == 'D') {

        if ($config['RMA']['eml_rma_decline'] == 'Y') {

            return func_send_mail(
                $userinfo['email'],
                'mail/rma_decline_subj.tpl',
                'mail/rma_decline.tpl',
                $config['Company']['orders_department'],
                false
            );

        }

    }

    return false;
}

/**
 * Check for new return requests
 */
function func_rma_new_returns_avail()
{
    global $sql_tbl;

    return (func_query_first_cell("SELECT COUNT(*) FROM $sql_tbl[returns] WHERE status='R'") > 0);
}

/**
 * Check for available inventory
 */
function func_rma_check_inventory($returnid, $amount)
{
    global $sql_tbl, $active_modules;

    if ($amount > 0)
        return true;

    x_load('order');

    $tmp = func_return_data($returnid);

    $product = $tmp['product'];

    if (
        (
            $product['product_type'] == 'C'
            && !empty($active_modules['Product_Configurator'])
        )
        || $product['deleted']
        || $product['distribution']
    ) {
        return true;
    }

    $variantid = '';

    if (
        !empty($active_modules['Product_Options'])
        && (
            !empty($product['extra_data']['product_options'])
            || !empty($product['options'])
        )
    ) {

        $options = (!empty($product['extra_data']['product_options'])
            ? $product['extra_data']['product_options']
            : $product['options']);

        $variantid = func_get_variantid($options);

    }

    if (!empty($variantid)) {

        $avail = XCVariantsSQL::getVariantAvail($product['productid'], $variantid);

    } else {

        $avail = func_query_first_cell("SELECT avail FROM $sql_tbl[products] WHERE productid='$product[productid]'");

    }

    return ($avail !== false)
        ? ($avail >= abs($amount))
        : true;
}

/**
 * Update inventory items
 */
function func_rma_update_inventory($returnid, $amount)
{
    global $active_modules;

    x_load('order');

    $tmp = func_return_data($returnid);

    if (
        !empty($tmp)
        && !$tmp['product']['deleted']
    ) {

        $product             = $tmp['product'];
        $product['amount']     = abs($amount);
        $is_increment         = ($amount > 0);

        if (
            !empty($active_modules['XOrder_Statuses'])
        ) {

            if (
                func_orderstatuses_check_if_status_changes_stock($tmp['order']['status'])
            ) {
                func_update_quantity(
                    array($product),
                    $is_increment
                );
            }

        } else {

            func_update_quantity(
                array($product),
                $is_increment
            );
        }
    }

}

/**
 * Checks if add return is allowed or not
 */
function func_rma_is_add_return_allowed($order_data, $itemid, $amount)
{
    global $sql_tbl;

    if (
        empty($itemid)
        || empty($amount)
    ) {
        return false;
    }

    $return_amount = (int)func_query_first_cell(
            "SELECT SUM($sql_tbl[returns].amount)"
            . " FROM $sql_tbl[returns]"
            . " WHERE $sql_tbl[returns].itemid = '$itemid'"
            . " AND status <> 'E'"
        );

    if (!empty($return_amount)) {
        $amount += $return_amount;
    }

    if (
        !empty($order_data['products'])
        && is_array($order_data['products'])
    ) {
        $item_was_found = false;

        foreach ($order_data['products'] as $product_info) {
            if ($product_info['itemid'] == $itemid) {
                $item_was_found = true;
                break;
            }
        }

        if ($item_was_found) {
            if (
                !empty($product_info['amount'])
                && $product_info['amount'] >= $amount
            ) {
                return true;
            }
        }
    }

    return false;
}

?>
