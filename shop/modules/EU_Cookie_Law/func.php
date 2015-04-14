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
 * Common functions for EU Cookie Law Module
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Modules
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v9 (xcart_4_7_0), 2015-02-17 23:56:28, func.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if ( !defined('XCART_SESSION_START') ) { header('Location: ../../'); die('Access denied'); }

function func_eucl_reset_delayed_cookies()
{
    global $eucl_cookies_storage;
    unset($eucl_cookies_storage);
}

function func_eucl_get_delayed_cookies()
{
    global $eucl_cookies_storage;

    if (!is_array($eucl_cookies_storage))
        $eucl_cookies_storage = array();

    return $eucl_cookies_storage;
}

function func_eucl_get_corrected_access($cookie_access = array(0,'Y','Y'))
{
    if (
        !is_array($cookie_access)
        || empty($cookie_access)
    ) {
        $cookie_access = array(0,'Y','Y');
    }
    // the item is confirmation time,
    $cookie_access[0] = intval($cookie_access[0]);
    // the item is access to functional cookies
    $cookie_access[1] = ($cookie_access[1] != 'N') ? 'Y' : 'N';
    // the item is access to other cookies (thirdparty cookies);
    $cookie_access[2] = ($cookie_access[2] != 'N') ? 'Y' : 'N';

    return $cookie_access;

}

function func_eucl_get_user_cookie_permission()
{
    global $logged_userid, $sql_tbl;
    static $cookie_access;

    if (is_array($cookie_access)) {
        return $cookie_access;
    }

    if (!empty($logged_userid) && $logged_userid > 0) {
        $_u_cookie_access = func_query_first_cell("SELECT eu_cookie_access FROM $sql_tbl[customer_eu_cookie_accesses] WHERE userid='$logged_userid'");
        $_u_cookie_access = empty($_u_cookie_access) ? array() : explode('/', $_u_cookie_access);

        $user_cookie_access = func_eucl_get_corrected_access($_u_cookie_access);
    } else {
        $user_cookie_access = func_eucl_get_corrected_access();
    }

    $need_set_cookie_access_to_cookie = false;
    $eucl_cookie_access = @$_COOKIE['eucl_cookie_access'];

    if (empty($eucl_cookie_access)) {
        $cookie_access = func_eucl_get_corrected_access();
        $need_set_cookie_access_to_cookie = true;
    } else {
        $cookie_access = func_eucl_get_corrected_access(explode('/', $eucl_cookie_access));
        if ($cookie_access[0] == 0) {
            $need_set_cookie_access_to_cookie = true;
        }
    }

    if ($user_cookie_access[0] > $cookie_access[0]) {
        $cookie_access = $user_cookie_access;
        $need_set_cookie_access_to_cookie = true;
    } elseif (
        !empty($logged_userid) 
        && $logged_userid > 0 
        && !empty($cookie_access[0])
    ) {
        db_query("REPLACE INTO $sql_tbl[customer_eu_cookie_accesses] set eu_cookie_access='" . implode('/', $cookie_access) . "', userid='$logged_userid'");
    }
    if ($need_set_cookie_access_to_cookie) {
        $cookie_access[0] = 1; // the cookie set is automatically without user confiramation
        func_setcookie('eucl_cookie_access', implode('/', $cookie_access), XC_TIME + 31536000); // one year
    }

    return $cookie_access;
}

function func_eucl_get_allowed_cookies()
{
    global $config;

    list($confirm_time, $functional_access, $other_access) = func_eucl_get_user_cookie_permission();

    $allowed_cookies = $config['EU_Cookie_Law']['strictly_necessary_cookies'];

    if ($functional_access == 'Y') {
        $allowed_cookies = $allowed_cookies + $config['EU_Cookie_Law']['functional_cookies'];
    }

    if ($other_access == 'Y') {
        $allowed_cookies = array();
    }

    return $allowed_cookies;
}

function func_eucl_is_allowed_cookie($cookie_name)
{
    $allowed_cookies = func_eucl_get_allowed_cookies();
    return empty($allowed_cookies) || in_array($cookie_name, $allowed_cookies);
}

function func_eucl_reset_unallowed_cookies()
{
    $delayed_cookies = func_eucl_get_delayed_cookies();

    // check delayed cookies
    if (is_array($delayed_cookies)) {
        foreach ($delayed_cookies as $cookie) {
            if (!func_eucl_is_allowed_cookie($cookie[0])) {
                func_setcookie($cookie[0], $cookie[1]);
            }
        }
    }

    // check existing cookies
    foreach ($_COOKIE as $name => $value) {
        if (!func_eucl_is_allowed_cookie($name)) {
            func_setcookie($name, $value);
        }
    }
    func_eucl_reset_delayed_cookies();
}

function func_eucl_get_expiration_time($cookie)
{
    if (is_array($cookie) && func_eucl_is_allowed_cookie($cookie[0])) {
        return $cookie[2];
    }

    return XC_TIME - 3600; // one hour ago
}

function func_eucl_is_set_access_cookie()
{
    $eucl_cookie_access = @$_COOKIE['eucl_cookie_access'];
    if (empty($eucl_cookie_access)) {
        return true;
    }

    $cookie_access = func_eucl_get_corrected_access(explode('/', $eucl_cookie_access));

    return $cookie_access[0] == 0;
}

function func_is_eucl_is_ready()
{
    global $is_init_eucl;
    return ($is_init_eucl === true);
}

function func_eucl_ready()
{
    global $is_init_eucl;
    $is_init_eucl = true;
}

function func_eucl_init()
{
    global $smarty, $current_area;

    if (in_array($current_area, array('C', 'B'))) {
        $smarty->assign('allowed_cookies', func_eucl_get_allowed_cookies());
        $smarty->assign('view_info_panel', (func_eucl_is_set_access_cookie()) ? 'Y' : 'N');
        $smarty->assign('cookie_access', func_eucl_get_user_cookie_permission());
        func_eucl_ready();
        func_eucl_reset_unallowed_cookies();
    }

}

?>
