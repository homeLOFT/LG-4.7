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
 * Log in / log out actions processor
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Lib
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v275 (xcart_4_7_0), 2015-02-17 23:56:28, login.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if ( !defined('XCART_START') ) { header('Location: ../'); die("Access denied"); }

x_load(
    'crypt',
    'mail',
    'user', // For XCUserSql and other
    'backoffice'
);

x_session_register('username');
x_session_register('login_antibot_on');
x_session_register('logout_user');
x_session_register('previous_login_date');
x_session_register('login_attempt');
x_session_register('cart');
x_session_register('intershipper_recalc');
x_session_register('merchant_password');
x_session_register('antibot_err');
x_session_register('login_redirect');

$merchant_password = '';

// Redirect already logged user to home page
if (!$mode && !empty($login)) {

    if (func_is_ajax_request()) {
        func_close_window();
    }

    func_header_location('home.php');
}

if ($REQUEST_METHOD == 'POST') {

    $intershipper_recalc = 'Y';

    if ($mode == 'login') {

        $usertype = ($current_area == 'A' && !empty($active_modules['Simple_Mode']))
            ? 'P'
            : $current_area;

        if (!empty($login)) {
            // Already logged in

            func_login_error(1, 'home.php');

        } elseif (strlen($_POST['username']) > 128) {
            // Username length error

            func_login_error(2);

        } elseif (strlen($_POST['password']) > 64) {
            // Password length error

            func_login_error(3);

        }

        // Check for existing user
        $username = trim($_POST['username']);

        $user_data = func_query_first("SELECT * FROM $sql_tbl[customers] WHERE login='$username' AND usertype='$usertype' AND " . XCUserSql::getSqlRegisteredCond());

        if (!empty($user_data)) {
            $userid = $user_data['id'];
        }


        // Image verification module
        if (
            !empty($active_modules['Image_Verification'])
            && $login_antibot_on
        ) {

            if (func_validate_image('on_login', $_POST['antibot_input_str'])) {

                func_login_error(4);
                $user_data = $userid = FALSE;

            }

        }

        if (empty($user_data)) {

            func_login_error();

        }

        // Check account Activity
        if (!func_check_account_activity($userid)) {

            func_login_error(5);

        }

        // Suspend admin account which was not logged in for N days
        if (
            (
                $usertype == 'A'
                || (
                    $usertype == 'P'
                    && !empty($active_modules['Simple_Mode'])
                )
            )
            && intval($config['Security']['suspend_admin_after']) > 0
            && $user_data['last_login'] > 0
            && (XC_TIME > ($user_data['last_login'] + $config['Security']['suspend_admin_after']*24*3600))
            && func_suspend_account($user_data['id'], $usertype, 'long_unused')
        ) {

                db_query("UPDATE $sql_tbl[customers] SET last_login='" . XC_TIME . "' WHERE id='$user_data[id]'");

                func_login_error(6);

        }

        $allow_login = TRUE;

        // Check admin account integrity
        global $xcart_dir;
        require_once $xcart_dir . '/include/classes/class.XCSignature.php'; 
        if (
            XCSecurity::CHECK_CUSTOMERS_INTEGRITY
            && XCUserSignature::isApplicable($user_data)
        ) {
            $obj = new XCUserSignature($user_data);

            if (!$obj->checkSignature()) {
                $allow_login = FALSE;
                func_login_error(10);
            }
        }

        // Check by IP for admin staff
        if (
            $usertype == 'A'
            || (
                $usertype == 'P'
                 && !empty($active_modules['Simple_Mode'])
            )
        ) {

            $iplist = preg_grep("/^\d+\.\d+\.\d+\.\d+$/", array_unique(preg_split('/[ ,]+/', trim(XCSecurity::ADMIN_ALLOWED_IP))));

            $allow_login = count($iplist) > 0 ? func_compare_ip($REMOTE_ADDR, $iplist) : TRUE;

            if (!$allow_login) {
                $allow_login = FALSE;
                func_login_error(11);
            }

            if (
                $allow_login
                && !empty($config['allowed_ips'])
                && XCSecurity::BLOCK_UNKNOWN_ADMIN_IP
                && !func_check_allow_admin_ip()
            ) {

                func_send_admin_ip_reg('L', $username, $user_data);

                if (
                    empty($user_data['first_login'])
                    && empty($user_data['last_login'])
                ) {

                    func_register_admin_ip($REMOTE_ADDR);

                } else {

                    $allow_login = FALSE;
                    func_login_error(9, null, FALSE);

                }

            }

        }

        // Check password
        
        $allow_old_password_format = (!in_array($user_data['usertype'], array('A', 'P')) && !empty($user_data['usertype']));
        list($is_password_correct, $password_has_old_format) = func_is_password_correct($password, $user_data['password'], $allow_old_password_format);

        if (!$is_password_correct) {
            func_login_error();
            $allow_login = FALSE;
        } elseif ($password_has_old_format) {
            func_change_user_password($user_data['id'], $password);
        }

        // Force password change if non-customer password was not changed for 90 days
        if (
            $allow_login
            && $config['Security']['force_change_password_days'] > 0
            && $usertype != 'C'
            && $user_data['change_password_date'] > 0
            && (
                XC_TIME > ($user_data['change_password_date'] + $config['Security']['force_change_password_days']*24*3600)
                && $user_data['change_password'] != 'Y'
            )
        ) {

            if ($usertype != 'C') {

                db_query("UPDATE $sql_tbl[customers] SET change_password='Y' WHERE id='$user_data[id]'");

                x_session_register('login_change');
                x_session_register('require_change_password');

                $require_change_password[$usertype] = TRUE;

                $login_change[$usertype] = $user_data['id'];
                if (!empty($active_modules['Simple_Mode']) && $usertype == 'P') {
                    // set A to match AREA_TYPE constant in change_password.php for Simple_Mode
                    $login_change['A'] = $user_data['id'];
                }

                func_login_error(7, 'change_password.php');
                $allow_login = FALSE;
            }
        }


        // Do not place new security checks between "if ($allow_login) {" and "Force password change block" bt:136545


        // Register IP for new admin / provider
        if ($allow_login) {

            // Success login
            func_authenticate_user($userid);

            $logout_user     = FALSE;
            $redirect_url     = 'home.php';

            if ($login_type == 'C') {
                x_load('cart');
                func_restore_serialized_cart($user_data['id']);
            }

            if ($login_type == 'C') {

                // Clean anonymous profile data
                func_set_anonymous_userinfo(array());

                // Redirect to saved URL
                x_session_register('remember_data');

                if (
                    isset($is_remember)
                    && $is_remember == 'Y'
                    && !empty($remember_data)
                ) {

                    if (
                        $HTTPS
                        && preg_match("/^http:\/\//", trim($remember_data['URL']))
                        && $config['Security']['leave_https'] != 'Y'
                    ) {
                        $remember_data['URL'] = preg_replace("/^" . preg_quote($http_location, "/") . "/", $https_location, trim($remember_data['URL']));
                    }

                    $redirect_url = $remember_data['URL'];

                } elseif (!func_is_cart_empty($cart)) {

                    // Redirect to cart page
                    $login_redirect = FALSE;

                    if(
                        strpos($HTTP_REFERER, "mode=auth") === FALSE
                        && strpos($HTTP_REFERER, "mode=checkout") === FALSE
                    ) {

                        $redirect_url = 'cart.php';

                    } else {

                        $redirect_url = 'cart.php?mode=checkout';

                    }

                } elseif (!empty($HTTP_REFERER)) {

                    // Redirect to HTTP_REFERER
                    if (
                        func_is_internal_url($HTTP_REFERER)
                        && (preg_match('/\.php(?:\?|$)/s', $HTTP_REFERER) || $config['SEO']['clean_urls_enabled'] == 'Y')
                    ) {
                        if (!preg_match('/(error_message\.php|login\.php|help\.php\?section=Password_Recovery)/s', $HTTP_REFERER)) {

                            $qs = strrchr(func_qs_remove($HTTP_REFERER, $XCART_SESSION_NAME), '/');

                            $redirect_url = func_get_area_catalog($login_type) . $qs;
                        } else {
                            $force_redirect_from_error_page = TRUE;
                        }

                    }

                }

            }

            // If shopping cart is not empty then user is redirected to cart.php
            // Default password alert

            if (
                $login_type == 'A'
                || $login_type == 'P'
            ) {

                $redirect_url = (
                        !empty($active_modules['Simple_Mode'])
                        || $login_type == 'A'
                    ? $xcart_catalogs['admin']
                    : $xcart_catalogs['provider']
                ) . '/home.php';

                // Return to saved last working URL if we have one for specified login type.
                $_tmp_last_working_url = func_url_get_last_working_url($login_type);

                if (!zerolen($_tmp_last_working_url)) {

                    $redirect_url = $_tmp_last_working_url;

                }

                unset($_tmp_last_working_url);

                $current_area = $login_type;

                if (!defined('GET_LANGUAGE')) {

                    include $xcart_dir . '/include/get_language.php';

                }

                // Check expiration for preauth orders
                x_load('payment');

                func_check_preauth_expiration();

                if ($shop_evaluation) {
                    func_enable_evaluation_popup();
                }

            } else {

                $redirect_url = $redirect_url;

            }

            if ($login_type == 'P') {

                x_session_register('show_seller_address_warning');

                $show_seller_address_warning = TRUE;

            }

            if (!empty($active_modules['TwoFactorAuth'])) {
                list($twofactor_enabled, $redirect_url) = func_twofactor_on_login($userid, $login_type, $redirect_url);
            }

            // Ajax request
            if (
                func_is_ajax_request()
                && (empty($active_modules['TwoFactorAuth']) || !$twofactor_enabled)
            ) {

                if (
                    !empty($force_redirect_from_error_page)
                    && !empty($redirect_url)
                ) {
                    func_reload_parent_window($redirect_url);
                } else {
                    func_reload_parent_window();
                }

            }

            $default_accounts = func_check_default_passwords($logged_userid);

            if (!empty($default_accounts)) {

                $current_area = $login_type;

                $txt_message = func_get_langvar_by_name('txt_your_password_warning_js',FALSE,FALSE,TRUE);

                $javascript_message = func_js_alert($txt_message, $redirect_url);

            } elseif (
                $usertype == 'A'
                || (
                    $usertype == 'P'
                    && !empty($active_modules['Simple_Mode'])
                )
            ) {

                $default_accounts = func_check_default_passwords();

                $is_allowed_membership = $user_data['membershipid'] <= 0 || func_query_first_cell("SELECT flag FROM $sql_tbl[memberships] WHERE membershipid  = '$user_data[membershipid]'") != 'FS';

                if (
                    !empty($default_accounts)
                    && $is_allowed_membership
                ) {

                    $txt_message = func_get_langvar_by_name('txt_default_passwords_warning_js', array('accounts'=>implode(", ", $default_accounts)),FALSE,TRUE);
                    $javascript_message = func_js_alert($txt_message, $redirect_url);

                }

            }

            if (
                !empty($javascript_message)
                && XCSecurity::$admin_safe_mode == FALSE
            ) {

                if (defined('XC_SESSION_DB_SAVE_LOGIN')) {
                    x_session_save();
                }

                echo $javascript_message;

                exit;

            }

            func_header_location($redirect_url);

        }

    }

}

/**
 * Check for valid activation key and activate account on success
 */
if (
    isset($activation_key)
    && func_check_secure_random_key($activation_key, 32)
    && $userid = func_enable_account($activation_key)
) {

    $userinfo = func_userinfo($userid);

    if (!empty($active_modules['Email_Activation']) && func_email_activation_can_send_signin_notif($userid)) {
        // Check for config and send signin notification to customer
        func_do_signin_email_notification($userinfo);
    }

    $top_message = array(
        'type'    => 'I',
        'content' => func_get_langvar_by_name(
            'txt_account_activated',
            array(
                'username' => $userinfo['login'],
            ),
            FALSE,
            TRUE
        )
    );

    func_header_location(func_get_area_catalog($userinfo['usertype']) . '/login.php');
}

if ($mode == 'logout') {

    $login_antibot_on = FALSE;

    $login_attempt = 0;

    if ($current_area == 'C') {

        x_load('paypal');

        func_paypal_clear_ec_token();

    }

    // Insert into login history
    if (
        !empty($active_modules['Simple_Mode'])
        && $login_type == 'A'
    ) {
        $login_type = 'P';
    }

    func_store_login_action($logged_userid, $login_type, 'logout', 'success');

    x_log_flag(
        'log_activity',
        'ACTIVITY',
        "User '$login' ('$login_type' user type) has logged out. Remote IP '$REMOTE_ADDR'"
    );

    // Clear user session identifiers
    func_end_user_session();

    if ($current_area == 'C') {

        $cart = '';

    }

    $access_status     = '';
    $merchant_password = '';
    $logout_user       = TRUE;

    if (
        $current_area == 'A'
        || $current_area == 'P'
    ) {

        func_ge_erase();

        x_session_register('recent_payment_methods');

        $recent_payment_methods = array();

    }

    x_session_unregister('hide_security_warning');
    x_session_unregister('initial_state_orders');
    x_session_unregister('initial_state_show_notif');

    if (!empty($active_modules['XAuth'])) {

        func_xauth_clear_login_data();
    }

    $login_redirect = 1;

    if (!empty($active_modules['TwoFactorAuth'])) {
        func_twofactor_on_logout($current_area);
    }

    func_header_location('home.php');

}

$qs_match = array(
    'login.php',
    'mode=order_message',
    'mode=wishlist',
    'bonuses.php',
    'returns.php',
    'giftreg_manage.php',
    'order.php',
    'error_message.php',
    'register.php?mode=delete',
    'register.php?mode=update',
);

$qs_match_str = implode('|', $qs_match);

if (
    isset($old_login_type)
    && $old_login_type == 'C'
    && func_is_internal_url($HTTP_REFERER)
    && !preg_match('/('.$qs_match.')/Ss', $HTTP_REFERER)
) {
    func_header_location(strrchr(func_qs_remove($HTTP_REFERER, $XCART_SESSION_NAME), '/'), FALSE);
}

if ($login_antibot_on) {

   $smarty->assign('login_antibot_on', $login_antibot_on);

}

?>
