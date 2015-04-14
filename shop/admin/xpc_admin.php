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
 * Settings/modules configuration interface
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Admin interface
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    3968cba5ecdb78320d43cbe05a25fe35597bc800, v8 (xcart_4_7_0), 2015-02-17 13:29:01, xpc_admin.php, aim
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

define('USE_TRUSTED_POST_VARIABLES',1);

$trusted_post_variables = array(
    'xpc_private_key_password',
    'xpc_private_key',
    'xpc_public_key',
);

require_once __DIR__.'/auth.php';

require_once $xcart_dir.'/include/security.php';

x_load('mail','order');

if (empty($active_modules['XPayments_Connector'])) {
    $top_message['content'] = func_get_langvar_by_name('msg_err_module_not_installed',array('module' => 'XPayments_Connector'));
    func_header_location($xcart_catalogs['admin'] . "/configuration.php");
}

// Update configuration variables
// these variables are for internal use in PHP scripts

$location[] = array(
    func_get_langvar_by_name('module_name_XPayments_Connector')
);

if ($REQUEST_METHOD == 'POST') {
    require_once $xcart_dir . '/include/safe_mode.php';
}

$dialog_tools_data = array();
$dialog_tools_data['right'][] = array(
    'title' => func_get_langvar_by_name('lbl_payment_methods'),
    'link' => 'payment_methods.php',
);
$dialog_tools_data['right'][] = array(
    'title' => func_get_langvar_by_name('lbl_general_settings'),
    'link' => 'configuration.php',
);

require_once $xcart_dir . '/modules/XPayments_Connector/xpc_admin.php';

$smarty->assign('main', 'xpc_admin');

// Assign the current location line
$smarty->assign('location', $location);

// Assign the section navigation data
$smarty->assign('dialog_tools_data', $dialog_tools_data);

if (is_readable($xcart_dir . '/modules/gold_display.php')) {
    include_once $xcart_dir . '/modules/gold_display.php';
}

func_display('admin/home.tpl', $smarty);
?>
