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
 * Define data for the navigation within section
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Admin interface
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v37 (xcart_4_7_0), 2015-02-17 23:56:28, users_tools.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if (!defined('XCART_SESSION_START')) {
    header('Location: ../');
    die('Access denied');
}

$dialog_tools_data = array();

$dialog_tools_data['left'][] = array(
    'link' => 'users.php',
    'title' => func_get_langvar_by_name('lbl_search_users')
);

if (empty($active_modules['Simple_Mode'])) {

    $dialog_tools_data['left'][] = array(
        'link' => 'user_add.php?usertype=A',
        'title' => func_get_langvar_by_name('lbl_create_admin_profile')
    );

    $dialog_tools_data['left'][] = array(
        'link' => 'user_add.php?usertype=P',
        'title' => func_get_langvar_by_name('lbl_create_provider_profile')
    );

} else {

    $dialog_tools_data['left'][] = array(
        'link' => 'user_add.php?usertype=P',
        'title' => func_get_langvar_by_name('lbl_create_admin_profile')
    );

}

$dialog_tools_data['left'][] = array(
    'link' => 'user_add.php?usertype=C',
    'title' => func_get_langvar_by_name('lbl_create_customer_profile')
);

if (!empty($active_modules['XAffiliate'])) {
    $dialog_tools_data['left'][] = array(
        'link' => 'user_add.php?usertype=B',
        'title' => func_get_langvar_by_name('lbl_create_partner_profile')
    );
}

$dialog_tools_data['right'][] = array(
    'link' => 'orders.php',
    'title' => func_get_langvar_by_name('lbl_orders')
);

$dialog_tools_data['right'][] = array(
    'link' => 'memberships.php',
    'title' => func_get_langvar_by_name('lbl_membership_levels')
);

$dialog_tools_data['right'][] = array(
    'link' => 'configuration.php?option=User_Profiles',
    'title' => func_get_langvar_by_name('option_title_User_Profiles')
);

if (func_is_user_access_control_enabled()) {
    $dialog_tools_data['right'][] = array(
        'link' => 'user_access_control.php',
        'title' => func_get_langvar_by_name('lbl_user_access_control')
    );
}

?>
