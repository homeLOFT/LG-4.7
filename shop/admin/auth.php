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
 * Base authentication, defining common variables 
 * and including common scripts
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Admin interface
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v111 (xcart_4_7_0), 2015-02-17 23:56:28, auth.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

define('AREA_TYPE', 'A');

if (is_readable('../top.inc.php')) {
    include_once '../top.inc.php';
}

if (!defined('DIR_CUSTOMER')) die("ERROR: Can not initiate application! Please check configuration.");

require_once $xcart_dir . '/init.php';

if (!empty($active_modules['Klarna_Payments'])) {
    require_once $xcart_dir . '/modules/Klarna_Payments/postinit.php';
}

x_load(
    'backoffice',
    'perms',
    'security' // For func_check_admin_security_redirect
);
require_once $xcart_dir . '/include/classes/class.XCSignature.php';

x_session_register('login', '');
x_session_register('login_type', '');
x_session_register('logged_userid', 0);
x_session_register('identifiers', array());

x_session_register('export_ranges');

$smarty->assign('js_enabled', 'Y');

x_session_register('top_message');

if (!empty($top_message)) {

    $smarty->assign('top_message', $top_message);

    if($config['Adaptives']['is_first_start'] != 'Y') {
        $top_message = array();
    }
}

x_session_register('login_antibot_on', '');

$smarty->assign('login_antibot_on', $login_antibot_on);

$current_area = 'A';

include $xcart_dir . '/https.php';

$location = array();
$location[] = array(func_get_langvar_by_name('lbl_main_page'), 'home.php');

if (is_readable($xcart_dir . '/modules/gold_auth.php')) {
    include $xcart_dir . '/modules/gold_auth.php';
}

include $xcart_dir . '/include/check_useraccount.php';

func_check_admin_security_redirect();

include $xcart_dir . '/include/get_language.php';

$login_type = !empty($active_modules['Simple_Mode']) ? 'P' : $login_type;

x_session_register('require_change_password');

if (
    !empty($login)
    && !strstr($PHP_SELF,'change_password.php')
    && !empty($require_change_password[$login_type])
) {

    // Require password change before proceed
    $top_message['content'] = func_get_langvar_by_name('txt_chpass_msg');
    $top_message['type']    = 'E';

    func_header_location('change_password.php');
}

$has_user_account = !empty($login) && ($user_account['flag'] != 'FS');

$smarty->assign('need_storefront_link', $has_user_account);

#func_set_resellers();

if (
    $has_user_account
    && $REQUEST_METHOD == 'GET'
) {

    if (
        func_is_enabled_evaluation_popup()
        && !func_is_resellers()
    ) {
        $smarty->assign('is_enabled_evaluation_popup', true);
        func_disable_evaluation_popup();
    }

    if ($shop_evaluation == 'WRONG_DOMAIN') {

        $smarty->assign('license_url', $config['license_url']);

        $wrong_domain = $http_location;

        if ($xcart_http_host != $xcart_https_host) {

            $wrong_domain .= ' (' . $https_location . ')';

        }

        $smarty->assign('wrong_domain', $wrong_domain);
    }

    func_storefront_update();
}

if (defined('XC_SESSION_DB_SAVE_AUTH')) {
    x_session_save();
}

// Create the user types list for search form

$usertypes = func_get_usertypes();

$smarty->assign('redirect', 'admin');
?>
