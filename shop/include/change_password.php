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
 * Change password processor
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Lib
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v83 (xcart_4_7_0), 2015-02-17 23:56:28, change_password.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if ( !defined('XCART_SESSION_START') ) { header("Location: ../"); die("Access denied"); }

x_load(
    'crypt',
    'user'
);

x_session_register('login');
x_session_register('logged_userid');
x_session_register('login_change');
x_session_register('chpass_referer', array());

if (!isset($chpass_referer[AREA_TYPE])) {

    if (
        !empty($HTTP_REFERER)
        && !preg_match('/change_password.php|help.php\?section=Password_Recovery|error_message.php\?/', $HTTP_REFERER)
        && func_is_internal_url($HTTP_REFERER)
    ) {

        $chpass_referer[AREA_TYPE] = $HTTP_REFERER;

    } else {

        $chpass_referer[AREA_TYPE] = 'home.php';

    }

}

if (!empty($logged_userid)) {

    $status = func_query_first_cell("SELECT status FROM $sql_tbl[customers] WHERE id = '$logged_userid'");

    if (trim($status) == 'A') {

        $url = $chpass_referer[AREA_TYPE];

        func_unset($chpass_referer, AREA_TYPE);

        func_header_location($url);

    }

}

$reset_password = FALSE;

unset($account);

if (
    !empty($password_reset_key)
    && !empty($user)
) {

    $user = intval($user);

    require_once $xcart_dir . '/include/classes/class.XCSignature.php';
    $account = func_query_first("SELECT userid, password_reset_key, password_reset_key_date, signature FROM $sql_tbl[reset_passwords] WHERE userid='$user'");
    $objPassReset = new XCResetPasswordSignature($account);
    unset($account['signature']);

    $is_account_valid = is_array($account) && !empty($account);
    $is_account_valid = $is_account_valid && text_verify($password_reset_key, text_decrypt($account['password_reset_key']));
    $is_url_expired   = XC_TIME > ($account['password_reset_key_date'] + 3600);

    if (
        $is_account_valid
        && $is_url_expired
    ) {

        // Password recovery key is expired
        db_query("DELETE FROM $sql_tbl[reset_passwords] WHERE userid='$user'");
        $top_message = array(
            'type' => 'E',
            'content' => func_get_langvar_by_name('txt_password_reset_url_expired')
        );

        func_header_location('home.php');

    } elseif (!$is_account_valid) {

        $top_message['type'] = 'E';
        $top_message['content'] = func_get_langvar_by_name('txt_password_reset_url_invalid');

        func_header_location('home.php');

    } elseif (!$objPassReset->checkSignature()) {
        // Password recovery key is fake
        db_query("DELETE FROM $sql_tbl[reset_passwords] WHERE userid='$user'");
        $top_message = array(
            'type' => 'E',
            'content' => func_get_langvar_by_name('txt_password_reset_key_is_fake')
        );

        func_header_location('home.php');
    } elseif (!$is_url_expired) {

        $tmp = func_query_first("SELECT usertype, login FROM $sql_tbl[customers] WHERE id='$user'");

        $account = func_array_merge($account, $tmp);

        $smarty->assign('mode',                 'recover_password');
        $smarty->assign('password_reset_key',     $password_reset_key);

        $reset_password = TRUE;

    }

}

if ($REQUEST_METHOD == 'GET') {

    if ($reset_password === TRUE) {

        $xlogin      = $account['login'];
        $xlogin_type = $account['usertype'];
        $xuserid     = $account['userid'];

    } elseif ($mode == 'updated') {

        $smarty->assign('mode', $mode);

    } elseif (
        empty($login)
        && !isset($login_change[AREA_TYPE])
    ) {

        $top_message['content'] = func_get_langvar_by_name('txt_chpass_login');

        func_header_location('home.php');

    } elseif (isset($login_change[AREA_TYPE])) {

        $xuserid     = $login_change[AREA_TYPE];
        $xlogin_type = AREA_TYPE;
        $xlogin      = func_get_login_by_userid($xuserid);

    } else {

        $xlogin      = $login;
        $xlogin_type = $login_type;
        $xuserid     = $logged_userid;

    }

    $smarty->assign('username', $xlogin);
    $smarty->assign('usertype', $xlogin_type);
    $smarty->assign('userid',   $xuserid);

} elseif ($REQUEST_METHOD == 'POST') {

    if ($reset_password === TRUE) {

        $xlogin      = $account['login'];
        $xlogin_type = $account['usertype'];
        $xuserid     = $account['userid'];

    } elseif (isset($login_change[AREA_TYPE])) {

        $xuserid     = $login_change[AREA_TYPE];
        $xlogin_type = AREA_TYPE;
        $xlogin      = func_get_login_by_userid($xuserid);

    } else {

        $xlogin      = $login;
        $xlogin_type = $login_type;
        $xuserid     = $logged_userid;

    }

    if (
        $xlogin_type == 'A'
        && !empty($active_modules['Simple_Mode'])
        && $reset_password !== TRUE
    ) {
        $xlogin_type = 'P';
    }

    $smarty->assign('username', $xlogin);
    $smarty->assign('usertype', $xlogin_type);
    $smarty->assign('userid',   $xuserid);

    $userinfo = func_userinfo($xuserid, $xlogin_type);

    $smarty->assign('old_password',     @$old_password);
    $smarty->assign('new_password',     $new_password);
    $smarty->assign('confirm_password', $confirm_password);

    if ($reset_password === TRUE)
        $old_password = $userinfo['password'];

    // Check old plain password from POST
    if (
        $reset_password !== TRUE
        && !text_verify($old_password, text_decrypt($userinfo['password']))
    ) {

        $top_message['content'] = func_get_langvar_by_name('txt_chpass_wrong');
        $top_message['type'] = 'E';

    } elseif ($new_password != $confirm_password) {

        $top_message['content'] = func_get_langvar_by_name('txt_chpass_match');
        $top_message['type'] = 'E';

    } elseif (text_verify($new_password, text_decrypt($userinfo['password']))) {

        $top_message['content'] = func_get_langvar_by_name('txt_chpass_another_one');
        $top_message['type'] = 'E';

    } elseif (empty($new_password)) {

        $top_message['content'] = func_get_langvar_by_name('txt_chpass_empty');
        $top_message['type'] = 'E';

    } elseif (strlen($new_password) > 64) {

        $top_message['content'] = func_get_langvar_by_name('txt_wrong_password_len');
        $top_message['type'] = 'E';

    } elseif (
        (   
            $config['Security']['use_complex_pwd'] == 'Y'
            || in_array($xlogin_type, array('A', 'P'))
        )
        && func_is_password_weak($new_password)
    ) {

        $top_message['content'] = func_get_langvar_by_name('txt_simple_password');
        $top_message['type'] = 'E';

    } elseif (
        $new_password == $xlogin
        && $config['Security']['use_complex_pwd'] == 'Y'
    ) {

        $top_message['content'] = func_get_langvar_by_name('txt_simple_password');
        $top_message['type'] = 'E';

    } elseif ($config['Security']['check_old_passwords'] == 'Y') {

        if (!func_has_same_old_passwords($xuserid, $new_password)) {

            if (func_change_user_password($xuserid, $new_password, $userinfo['password'])) {
                x_log_flag(
                    'log_activity',
                    'ACTIVITY',
                    "'$xlogin' user has changed password using 'Change password' page"
                );
            }

            func_unset($login_change, AREA_TYPE);
            $top_message['content'] = $reset_password
                ? func_get_langvar_by_name('txt_chpass_reset')
                : func_get_langvar_by_name('txt_chpass_changed');

            func_unset($require_change_password, $xlogin_type);
            $url = $chpass_referer[AREA_TYPE];
            func_unset($chpass_referer, AREA_TYPE);
            func_authenticate_user($xuserid);
            func_header_location($url);

        } else {

            $top_message['content'] = func_get_langvar_by_name('txt_chpass_another');
            $top_message['type'] = 'E';

        }

    } else {

        if (func_change_user_password($xuserid, $new_password, $userinfo['password'])) {
            x_log_flag(
                'log_activity',
                'ACTIVITY',
                "'$xlogin' user has changed password using 'Change password' page"
            );
        }

        func_unset($login_change, AREA_TYPE);
        $top_message['content'] = $reset_password
            ? func_get_langvar_by_name('txt_chpass_reset')
            : func_get_langvar_by_name('txt_chpass_changed');

        func_unset($require_change_password, $xlogin_type);
        $url = $chpass_referer[AREA_TYPE];
        func_unset($chpass_referer, AREA_TYPE);
        func_authenticate_user($xuserid);
        func_header_location($url);

    }

    if ($reset_password === TRUE) {

        func_header_location("change_password.php?password_reset_key=$password_reset_key&user=$xuserid");

    } else {

        func_header_location('change_password.php');

    }

}

$location[] = array(func_get_langvar_by_name('lbl_chpass'), '');

?>
