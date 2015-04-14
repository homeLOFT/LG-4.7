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
 * Customer's address book interface
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Customer interface
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v11 (xcart_4_7_0), 2015-02-17 23:56:28, saved_cards.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

require __DIR__.'/auth.php';

if (empty($active_modules['XPayments_Connector'])) {
    func_header_location('home.php');
    exit;
}

func_xpay_func_load();

if (!func_xpc_use_recharges()) {
    func_header_location('home.php');
    exit;
}

require $xcart_dir . '/include/remember_user.php';
require $xcart_dir . '/include/security.php';

include $xcart_dir . '/include/common.php';

x_load('user');

if (
    in_array($mode, array('delete', 'set_default'))
    && !empty($id)
) {

    if ('delete' == $mode) {
        $res = func_xpc_delete_saved_card($logged_userid, $id);
        $msg = func_get_langvar_by_name('txt_saved_card_removed');
    } elseif ('set_default' == $mode) {
        $res = func_xpc_set_default_card($logged_userid, $id);
        $msg = func_get_langvar_by_name('txt_saved_card_updated');
    }

    if ($res) {
        $top_message = array(
            'type'    => 'I',
            'content' => $msg, 
        );
    }

    func_header_location('saved_cards.php');
}

$saved_cards = func_xpc_get_saved_cards();
$allow_save_cards = func_xpc_get_allow_save_cards();
$default_card_id = func_xpc_get_default_card();

$smarty->assign('saved_cards', $saved_cards);
$smarty->assign('allow_save_cards', $allow_save_cards);
$smarty->assign('default_card_id', $default_card_id);

$xpc_save_cc_paymentid = func_xpc_get_save_cc_paymentid();
$allow_add_new_card =
                $config['XPayments_Connector']['xpc_save_cc_enable'] == 'Y'
                && $xpc_save_cc_paymentid > 0
                && !func_is_address_book_empty($logged_userid);

if (
    $xpc_save_cc_paymentid
    && $allow_add_new_card
) {
    x_load('cart');
    $allow_add_new_card = false;
    $payment_methods = check_payment_methods(($user_account['membershipid']) ? $user_account['membershipid'] : 0);
    foreach ($payment_methods as $payment) {
        if ($payment['paymentid'] == $xpc_save_cc_paymentid) {
            $allow_add_new_card = true;
            break;
        }
    }
}

$smarty->assign('xpc_save_cc_paymentid', $xpc_save_cc_paymentid);
$smarty->assign('allow_add_new_card', $allow_add_new_card);

$smarty->assign('main', 'saved_cards');

// Assign the current location line
$location[] = array(func_get_langvar_by_name('lbl_saved_cards'), '');
$smarty->assign('location', $location);

func_display('customer/home.tpl', $smarty);
?>
