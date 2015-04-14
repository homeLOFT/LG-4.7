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
 * This script contains common functions for minicart operating
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Cart
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v4 (xcart_4_7_0), 2015-02-17 23:56:28, func.minicart.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if ( !defined('XCART_START') ) { header("Location: ../"); die("Access denied"); }


function func_get_minicart_totals() {

    global $cart;

    x_session_register('cart');

    $minicart['minicart_total_cost']  = price_format(0);
    $minicart['minicart_total_items'] = 0;

    if (!empty($cart)) {

        x_load('cart');
        if (func_is_minicart_update_needed()) {
            list($cart, $products) = func_generate_products_n_recalculate_cart();
        } 

        // Assign total cost
        if (!func_cart_is_zero_total_cost($cart)) {
            $minicart['minicart_total_cost'] = $cart['display_subtotal'];
        }

        // Sum up products items
        if (
            !empty($cart['products'])
            && is_array($cart['products'])
        ) {
            foreach ($cart['products'] as $p) {
                if (
                    !isset($p['hidden'])
                    || empty($p['hidden'])
                ) {
                    $minicart['minicart_total_items'] += $p['amount'];
                }
            }
        }

        // Sum up giftcerts items
        if (
            !empty($cart['giftcerts'])
            && is_array($cart['giftcerts'])
        ) {
            foreach ($cart['giftcerts'] as $p) {
                $minicart['minicart_total_items']++;
            }
        }

    }

    return $minicart;
}

?>
