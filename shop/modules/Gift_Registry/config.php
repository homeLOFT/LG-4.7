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
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v40 (xcart_4_7_0), 2015-02-17 23:56:28, config.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if ( !defined('XCART_START') ) { header('Location: ../../'); die('Access denied'); }

/**
 * Global definitions for Gift_Registry module
 */

global $active_modules, $smarty, $config;

$addons['Gift_Registry'] = true;

$sql_tbl['giftreg_events'] = XC_TBL_PREFIX . 'giftreg_events';
$sql_tbl['giftreg_maillist'] = XC_TBL_PREFIX . 'giftreg_maillist';
$sql_tbl['giftreg_guestbooks'] = XC_TBL_PREFIX . 'giftreg_guestbooks';

if (
    empty($active_modules['Wishlist'])
    && func_constant('AREA_TYPE') == 'C'
) {
    unset($active_modules['Gift_Registry']);
    $smarty->assign('active_modules', $active_modules);
    return;
}

$css_files['Gift_Registry'][] = array();

if (defined('TOOLS')) {
    $tbl_keys['wishlist.event_id'] = array(
        'keys' => array('wishlist.event_id' => 'giftreg_events.event_id'),
        'where' => "wishlist.event_id != 0",
        'fields' => array('wishlistid','userid')
    );
    $tbl_keys['giftreg_events.userid'] = array(
        'keys' => array('giftreg_events.userid' => 'customers.id'),
        'where' => "customers.usertype = 'C'",
        'fields' => array('event_id')
    );
    $tbl_keys['giftreg_maillist.event_id'] = array(
        'keys' => array('giftreg_maillist.event_id' => 'giftreg_events.event_id'),
        'fields' => array('regid','recipient_name')
    );
    $tbl_keys['giftreg_guestbooks.event_id'] = array(
        'keys' => array('giftreg_guestbooks.event_id' => 'giftreg_events.event_id'),
        'fields' => array('message_id','name')
    );
    $tbl_demo_data['Gift_Registry'] = array(
        'giftreg_events' => '',
        'giftreg_maillist' => '',
        'giftreg_guestbooks' => ''
    );
}

$_module_dir  = $xcart_dir . XC_DS . 'modules' . XC_DS . 'Gift_Registry';
/*
 Load module functions
*/
if (!empty($include_func))
    require_once $_module_dir . XC_DS . 'func.php';
?>
