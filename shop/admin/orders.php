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
 * Orders management interface
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Admin interface
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    3968cba5ecdb78320d43cbe05a25fe35597bc800, v58 (xcart_4_7_0), 2015-02-17 13:29:01, orders.php, aim
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

define('NUMBER_VARS', "posted_data['total_min'],posted_data['total_max'],posted_data['price_min'],posted_data['price_max']");

require __DIR__.'/auth.php';

require $xcart_dir . '/include/security.php';

x_session_register('search_data');

$smarty->assign('show_order_details', 'Y');


if ('delete_all' === $mode) {

    // Delete ALL orders and move them to the orders_deleted table
    include $xcart_dir . '/include/process_order.php';

} else if (
    !empty($active_modules['Advanced_Order_Management'])
    && 'create' === $mode
) {

    // Create an empty order record in the database
    if (empty($user)) {
        $user = false;
    }
    $new_orderid = func_aom_create_new_order($user);

    // Redirect user to the new order's details editing page
    if (!empty($new_orderid)) {
        x_session_unregister('cart_tmp');
        func_header_location("order.php?orderid=$new_orderid&mode=edit&show=products");

    } else {
        $mode = '';
    }
}

include $xcart_dir . '/include/orders.php';


// Assign the current location line
$smarty->assign('location', $location);

$smarty->assign('gmap_enabled', 'Y');

if (is_readable($xcart_dir.'/modules/gold_display.php')) {
    include $xcart_dir.'/modules/gold_display.php';
}
func_display('admin/home.tpl',$smarty);
?>
