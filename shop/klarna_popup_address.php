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
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Modules
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v9 (xcart_4_7_0), 2015-02-17 23:56:28, klarna_popup_address.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

require_once __DIR__.'/auth.php';

if (!isset($active_modules['Klarna_Payments']) || empty($active_modules['Klarna_Payments'])) {
    func_header_location('home.php');
}

x_session_register('klarna_addresses');
x_session_register('cart');

if (
    $REQUEST_METHOD == 'POST' 
    && $mode == 'select'
) {
    
    if ($selected_address != '' && isset($klarna_addresses[$selected_address])) {
        
        if ($klarna_addresses[$selected_address]['firstname'] == '') {
            unset($klarna_addresses[$selected_address]['firstname']);
        }
        if ($klarna_addresses[$selected_address]['lastname'] == '') {
            unset($klarna_addresses[$selected_address]['lastname']);
        }
        $cart['klarna_address'] = $klarna_addresses[$selected_address];
        $cart['use_klarna_address'] = 'Y';

        if (!empty($cart['used_b_address'])) {

            $cart['used_b_address'] = func_array_merge($cart['used_b_address'], $cart['klarna_address']);
        }
        x_session_save();
        func_define('XC_DISABLE_SESSION_SAVE', true);

        func_reload_parent_window();
    }
    
}

if (
    empty($ssn)
    || $mode == 'return'
) {
    func_close_window();
}

// define variable for the Klarna exception if any


$userinfo = func_userinfo($user_account['id'], $user_account['usertype']);

$klarna_addresses = func_klarna_get_address($ssn);

$cart['klarna_ssn'] = $ssn;

if (!empty($klarna_addresses)) {

    $smarty->assign('klarna_addresses', $klarna_addresses);
}

$smarty->assign('template_name', 'modules/Klarna_Payments/klarna_popup_addresses.tpl');

func_display('customer/help/popup_info.tpl', $smarty);

?>
