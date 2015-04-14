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
 * This script authenticates user (session variables 'login', 'login_type', 'logged_userid')
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Lib
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v134 (xcart_4_7_0), 2015-02-17 23:56:28, check_useraccount.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if ( !defined('XCART_SESSION_START') ) { header("Location: ../"); die("Access denied"); }

x_session_register('login', '');
x_session_register('login_type', '');
x_session_register('logged_userid', 0);
x_session_register('identifiers', array());

if (!is_array($identifiers)) $identifiers = array();

if (defined('AREA_TYPE')) {
    if (AREA_TYPE != 'C') {
        x_load('crypt'); // Can be removed in future
    }

    if (
        !empty($_GET['operate_as_user'])
        && func_is_allowed_operate_as_user_mode($identifiers)
    ) {

        // Operate as user feature

        x_load('user');

        func_start_user_session($operate_as_user);

        // Get cart content when admin operate as user
        x_session_register('cart');

        $s_cart = func_query_first_cell("SELECT saved_cart FROM $sql_tbl[customer_saved_carts] WHERE userid='$operate_as_user'");

        $cart = unserialize($s_cart);

        func_header_location('home.php');

    }

    if (
        empty($identifiers[AREA_TYPE])
        && !empty($active_modules['Simple_Mode'])
        && !empty($logged_userid)
        && !empty($login_type)
    ) {
        // grant additional rights when Simple_Mode is turned ON to other logged-in users

        if (
            strpos('AP', $login_type) !== false
            && strpos('AP', AREA_TYPE) !== false
        ) {

            // provider became admin
            if (!isset($identifiers['A']))
                $identifiers['A'] = $identifiers['P'];

            // admin get access to the provider area
            if (!isset($identifiers['P']))
                $identifiers['P'] = $identifiers['A'];

        }

    }

    if (!empty($identifiers[AREA_TYPE])) {

        $login          = $identifiers[AREA_TYPE]['login'];
        $logged_userid  = $identifiers[AREA_TYPE]['userid'];
        $login_type     = empty($active_modules['Simple_Mode'])
            ? $identifiers[AREA_TYPE]['login_type']
            : AREA_TYPE;

    } else {

        $login = $login_type = '';
        $logged_userid = 0;

    }

}

if (!empty($logged_userid)) {

    $__tmp = func_query_first_cell("SELECT status FROM $sql_tbl[customers] WHERE id = '$logged_userid'");

    if (
        $__tmp != 'Y'
        && $__tmp != 'A'
    ) {

        func_unset($identifiers, $login_type);

        if (!empty($active_modules['Simple_Mode'])) {

            if ($login_type == 'A')
                func_unset($identifiers, 'P');

            if ($login_type == 'P')
                func_unset($identifiers, 'A');

        }

        $login = $login_type = $logged_userid = '';

        if ($__tmp) {

            $_top_message['content'] = func_get_langvar_by_name('err_account_temporary_disabled');

            $smarty->assign('top_message', $_top_message);

        }

    }

}

if (
    (
        $current_area == 'A'
        || $current_area == 'P'
    )
    && !defined('IS_IMAGE_SELECTION')
) {

    // $file_upload_data service array initialization

    x_session_register('file_upload_data');

    if (!empty($file_upload_data)) {

        foreach ($file_upload_data as $k => $v) {

            if (!isset($config['available_images'][$k])) {

                unset($file_upload_data[$k]);

            } elseif (isset($v['file_path'])) {

                if (!empty($v['is_redirect'])) {

                    unset($file_upload_data[$k]);

                } else {

                    $file_upload_data[$k]['is_redirect'] = true;

                }

            } elseif(!empty($v) && is_array($v)) {

                foreach ($v as $k2 => $v2) {

                    if (!isset($v2['file_path']))
                        continue;

                    if ($v2['is_redirect']) {

                        unset($file_upload_data[$k][$k2]);

                    } else {

                        $file_upload_data[$k][$k2]['is_redirect'] = true;

                    }

                }

            }

        }

    }

}

if ($logged_userid) {

    $user_account = func_query_first("SELECT $sql_tbl[customers].id, $sql_tbl[customers].login, $sql_tbl[customers].usertype, $sql_tbl[customers].membershipid, $sql_tbl[customers].title, $sql_tbl[customers].firstname, $sql_tbl[customers].lastname, $sql_tbl[memberships].membership, $sql_tbl[memberships].flag, $sql_tbl[customers].trusted_provider, $sql_tbl[customers].email FROM $sql_tbl[customers] LEFT JOIN $sql_tbl[memberships] ON $sql_tbl[customers].membershipid = $sql_tbl[memberships].membershipid WHERE id='$logged_userid' AND status <> 'Q'");

    if (empty($user_account)) {

        $login = $login_type = $logged_userid = '';

    }

    if (!empty($active_modules['Klarna_Payments'])) {
        $user_account['country'] = func_query_first_cell("SELECT country FROM $sql_tbl[address_book] WHERE userid = '$user_account[id]' AND default_b = 'Y'");
    }

    $user_account['allow_active_content'] = (
        $user_account['trusted_provider'] == 'Y'
        || !empty($active_modules['Simple_Mode'])
        || $user_account['usertype'] != 'P'
    );

    $fullname = $user_account['title'] . ' '
        . $user_account['firstname'] . ' '
        . $user_account['lastname'];

    $smarty->assign('fullname', trim($fullname));

    if (!empty($active_modules['TwoFactorAuth']) && !empty($logged_userid)) {
        func_twofactor_on_check_useraccount($login_type);
    }

}

if (
    !empty($active_modules['Users_online'])
    && !defined('IS_ROBOT')
) {

    include $xcart_dir . '/modules/Users_online/registered_user.php';

    include $xcart_dir . '/modules/Users_online/users_online.php';

}

/**
 * Remember visitor for a long time period
 */
if (
    $remember_user
    && $remember_user_days > 0
) {

    x_session_register('remember_login', false);
    x_session_register('remember_data');
    x_session_register('_remember_vars');

    $remember_key = $XCART_SESSION_NAME . $current_area . '_remember';

    if (!empty($login)) {

        // Check remember data
        if (
            !empty($remember_data)
            && $remember_data['cnt']-- <= 0
        ) {

            $remember_data = false;

            $_remember_vars = array();

        }

        // Set login as cookie's remember key
        if (empty($_COOKIE[$remember_key])) {

            func_setcookie(
                $remember_key,
                $login,
                XC_TIME + 86400 * $remember_user_days
            );

        }

    } elseif (
        zerolen($remember_login)
        && !empty($_COOKIE[$remember_key])
    ) {

        // Check remember key
        $remember_login = $_COOKIE[$remember_key];
        x_load('user'); // For XCUserSql

        if (
            empty($remember_login)
            || !is_string($remember_login)
            || !func_query_first_cell("SELECT COUNT(*) FROM $sql_tbl[customers] WHERE login = '" . addslashes($remember_login) . "' AND usertype = '$current_area' AND " . XCUserSql::getSqlRegisteredCond())
        ) {

            $remember_login = false;

        } else {

            $smarty->assign('remember_login', $remember_login);

        }

    }

}

// Store current URL as a last working URL, so an user can get back to it after successful login.
if (in_array($current_area, array('A', 'P'))) {

    func_url_set_last_working_url($current_area);

}

if (!empty($_FILES)) {
    include $xcart_dir . '/include/logging_files.php';
}    

// Store anonymous user information within session

if ($current_area == 'C') {

    $is_anonymous = false;

    if (
        empty($login)
        && $config['General']['enable_anonymous_checkout'] == 'Y'
    ) {
        $is_anonymous = true;

    } elseif (!empty($login)) {

        include $xcart_dir . '/include/account_tabs.php';

    }

}

if (defined('XC_SESSION_DB_SAVE_CHECK_USERACCOUNT')) {
    x_session_save();
}

$smarty->assign('login',                $login);
$smarty->assign('logged_userid',        $logged_userid);
$smarty->assign('usertype',             $current_area);

$mail_smarty->assign('usertype',        $current_area);

?>
