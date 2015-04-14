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
 * Module configuration
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Modules
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v27 (xcart_4_7_0), 2015-02-17 23:56:28, config.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if ( !defined('XCART_START') ) { header('Location: ../../'); die('Access denied'); }

global $config, $smarty, $xcart_dir, $xpc_crypted_map_fields, $sql_tbl;

// Synchronize with $bf_crypted_tables from include/blowfish.php
$xpc_crypted_map_fields = array(
    'store_id'              => 'xpc_shopping_cart_id',
    'url'                   => 'xpc_xpayments_url',
    'public_key'            => 'xpc_public_key',
    'private_key'           => 'xpc_private_key',
    'private_key_password'  => 'xpc_private_key_password',
);

// Add cron task
$cron_tasks[] = array(
    'function' => 'func_xpc_delete_started_orders',
    'include'  => $xcart_dir . '/modules/XPayments_Connector/xpc_func.php',
);

// Saved cards list
$sql_tbl['xpc_saved_cards'] = XC_TBL_PREFIX . 'xpc_saved_cards';
// Old-style data migration indicator
$sql_tbl['xpc_saved_cards_migrated'] = XC_TBL_PREFIX . 'xpc_saved_cards_migrated';

$_module_dir  = $xcart_dir . XC_DS . 'modules' . XC_DS . 'XPayments_Connector';
/*
 Load module functions
*/

if (!empty($include_func))
    require_once $_module_dir . XC_DS . 'func.php';

define('XPC_REDIRECT_DO_NOTHING',    0);
define('XPC_REDIRECT_DISPLAY_ERROR', 1);
define('XPC_REDIRECT_ALERT_ERROR',   2);
define('XPC_REDIRECT_SEND_EVENT',    3);

define('XPC_IFRAME_DO_NOTHING',      0);
define('XPC_IFRAME_CHANGE_METHOD',   1);
define('XPC_IFRAME_CLEAR_INIT_DATA', 2);
define('XPC_IFRAME_ALERT',           3);

// Add modules/XPayments_Connector/main.css
$css_files['XPayments_Connector'][] = array();
$css_files['XPayments_Connector'][] = array('admin' => TRUE);

?>
