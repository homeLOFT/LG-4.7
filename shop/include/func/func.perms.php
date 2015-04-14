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
 * Functions to check permissions to do some actions
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Lib
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v18 (xcart_4_7_0), 2015-02-17 23:56:28, func.perms.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if ( !defined('XCART_START') ) { header("Location: ../"); die("Access denied"); }

abstract class XCActions {
    const CHANGE_ALLOWED_ADMIN_IP = 'CHANGE_ALLOWED_ADMIN_IP';
    const CHANGE_DB = 'CHANGE_DB';
    const CHANGE_SECURITY_OPTIONS = 'CHANGE_SECURITY_OPTIONS';
    const DOWNLOAD_DB = 'DOWNLOAD_DB';
    const FILE_OPERATIONS = 'FILE_OPERATIONS';
    const IMPORT_ADMIN_PROFILES = 'IMPORT_ADMIN_PROFILES';
    const MANAGE_ADMIN_PROFILES = 'MANAGE_ADMIN_PROFILES';
    const MANAGE_LOGS = 'MANAGE_LOGS';
    const MANAGE_SYSTEM_FINGERPRINTS = 'MANAGE_SYSTEM_FINGERPRINTS';
    const MANAGE_XMONITORING_FILES = 'MANAGE_XMONITORING_FILES';
    const PATCH = 'PATCH';
    const PATCH_FILES = 'PATCH_FILES';
    const UPGRADE = 'UPGRADE';
}

global $var_dirs;

define('FILE_ALLOW_DIR', $var_dirs['tmp'] . '/');
define('FILE_ALLOW1', 'XC_UNLOCK');
define('FILE_ALLOW2', 'xc_unlock');
define('FILE_ALLOW_TTL', 3600 * 6); //6 hours

/*
* Check if a user has perms to run an action
*/
function func_check_perms_redirect($action, $result_action = 'redirect') {
    assert('!empty($action) /*!empty($action) '.__FUNCTION__.'*/');
    
    if (!func_fs_changes_is_allowed($action)) {

        $is_ip_protect_method = (func_get_action_protect_method($action) === 'ip');
        $protect_method = $is_ip_protect_method ? 'ip_protect_method' : 'file_protect_method';

        if ($is_ip_protect_method) {
            $lng_replace_to = func_send_admin_ip_reg();
        } else {
            $lng_replace_to = array();
        }

        // Set error message by default
        $refl = new ReflectionClass('XCActions');
        $all_consts = $refl->getConstants();
        foreach($all_consts as $name_const => $value) {
            $err_msg[$name_const]['file_protect_method'] = 80;//func_403(80);
            $err_msg[$name_const]['ip_protect_method'] = 82;//func_403(82);
        }

        // Custom error messages
        $err_msg[XCActions::FILE_OPERATIONS]['file_protect_method'] = 81;//func_403(81);
        $err_msg[XCActions::FILE_OPERATIONS]['ip_protect_method'] = 83;//func_403(83);
        $err_msg[XCActions::IMPORT_ADMIN_PROFILES]['file_protect_method'] = 94;//func_403(94);
        $err_msg[XCActions::IMPORT_ADMIN_PROFILES]['ip_protect_method'] = 95;//func_403(95);
        $err_msg[XCActions::MANAGE_ADMIN_PROFILES]['file_protect_method'] = 96;//func_403(96);
        $err_msg[XCActions::MANAGE_ADMIN_PROFILES]['ip_protect_method'] = 97;//func_403(97);

        $err_msg_id = $err_msg[$action][$protect_method];
        if ($action !== XCActions::FILE_OPERATIONS) {
            assert('$protect_method == "file_protect_method" || $err_msg_id % 2 == 0 /* '.__FUNCTION__.': Use even(0,2,4,6...) numbers for "file_protect_method and odd(1,3,5,7...) for "ip_protect_method"*/');
            assert('$protect_method == "ip_protect_method" || $err_msg_id % 2 == 0 /* '.__FUNCTION__.': Use even(0,2,4,6...) numbers for "file_protect_method and odd(1,3,5,7...) for "ip_protect_method"*/');
        }

        switch ($result_action) {
            case 'redirect':
                func_403($err_msg_id, $lng_replace_to);
                break;
            case 'return_err_msg_code':
                return array_merge(array('message_name' => 'txt_err_msg_code_' . $err_msg_id), $lng_replace_to);
                break;
            default:
                func_403($err_msg_id, $lng_replace_to);
        }
    }

    return TRUE;
}

/*
* Check if an user allow to create/modify admin/provider profiles
*/
function func_is_allow_to_modify_admins($old_userinfo, $login_type, $email, $status, $usertype) { // {{{
    global $config, $current_area, $active_modules, $login;

    // Allow for providers(when Simple_Mode is disabled) to register from any IP
    $check_usertypes_for_new = ($config['General']['provider_register'] == 'Y' && $current_area != 'A' && empty($active_modules['Simple_Mode']) && empty($login))
        ? array('A')
        : array('A','P');

    $_err_message = array();
    $default_error_id = 19;
    $changed_statuses = array('changed_statuses' => '');
    $changed_emails = array('changed_email' => $email);
    $old_userinfo['status'] = empty($old_userinfo['status']) ? 'N' : $old_userinfo['status'];

    if (
        !empty($old_userinfo)
        && in_array($login_type, array('A','P'))
    ) {
        // Check if admin has perms to change admin emails/status
        if (
            (
                $old_userinfo['email'] != $email
                || (
                    $old_userinfo['status'] != $status
                    && $status == 'Y' // New status is enabled
                )
            )
            && ($check_perms_result = func_check_perms_redirect(XCActions::MANAGE_ADMIN_PROFILES, 'return_err_msg_code'))
            && $check_perms_result !== true
        ) {
            $_err_message['error_id'] = $default_error_id;

            if ($old_userinfo['status'] != $status) {
                $_statuses = $old_userinfo['status'] . ' -> ' . $status;
                $changed_statuses = array('changed_statuses' => '<br /><strong>' . func_get_langvar_by_name('lbl_account_status') . ':' . $_statuses . '</strong>');
                $_err_message['error_id'] = 20;
            }

            if ($old_userinfo['email'] != $email) {
                $_err_message['error_id'] = $old_userinfo['status'] != $status ? 21 : $default_error_id;
                $changed_emails = array('changed_email' => $old_userinfo['email'] . ' -> ' . $email);
            }

            $_err_message['error_text'] = func_get_langvar_by_name($check_perms_result['message_name'], array_merge($changed_statuses, $changed_emails, $check_perms_result), false, true);
        }
    } elseif (
        // Check if admin has perms to create admin profiles
        in_array($usertype, $check_usertypes_for_new)
        && ($check_perms_result = func_check_perms_redirect(XCActions::MANAGE_ADMIN_PROFILES, 'return_err_msg_code'))
        && $check_perms_result !== true
    ) {
        $_err_message['error_text'] = func_get_langvar_by_name($check_perms_result['message_name'], array_merge($changed_statuses, $changed_emails, $check_perms_result), false, true);
        $_err_message['error_id'] = $default_error_id;
    }

    return $_err_message;
} //func_is_allow_to_modify_admins}}}

/*
* Check if a user has perms to change/create/delete files
*/
function func_fs_changes_is_allowed($action) {

    $protect_method = func_get_action_protect_method($action);

    if (!$protect_method) {
        // Protection disabled
        return TRUE;
    }
    
    if ($protect_method == 'ip') {
        // Protect by IP address
        $is_allow = func_check_allow_admin_ip();

    } else {
        // Protect by var/tmp/xc_unlock file
        
        func_fs_changes_remove_expired(); 

        $is_allow = is_writable(FILE_ALLOW_DIR . FILE_ALLOW1) 
                    || is_writable(FILE_ALLOW_DIR . FILE_ALLOW2);

        $is_allow = $is_allow && is_writable(FILE_ALLOW_DIR);
    }

    return $is_allow;
}

function func_fs_changes_allow() {
   @file_put_contents(FILE_ALLOW_DIR . FILE_ALLOW1, 'This is flag to allow file operations in X-Cart'); 
}

function func_fs_changes_deny() {
    @unlink(FILE_ALLOW_DIR . FILE_ALLOW1);
    @unlink(FILE_ALLOW_DIR . FILE_ALLOW2);
}

function func_fs_changes_remove_expired() {
    foreach(array(FILE_ALLOW1, FILE_ALLOW2) as $file) {
        $file = FILE_ALLOW_DIR . $file;

        if (!is_writable($file))
            continue;

        $m_time = @filemtime($file);

        if ($m_time + FILE_ALLOW_TTL < XC_TIME) {
            @unlink($file);
        }
    }
}

/*
 * Get protection method from XCSecurity settings according to action
 */
function func_get_action_protect_method($action) {

    // Some users type 'FALSE' as value instead of FALSE 
    if ($action === XCActions::FILE_OPERATIONS) {

        return (XCSecurity::PROTECT_ESD_AND_TEMPLATES === 'FALSE' ? FALSE : XCSecurity::PROTECT_ESD_AND_TEMPLATES);

    } else {

        return (XCSecurity::PROTECT_DB_AND_PATCHES === 'FALSE' ? FALSE : XCSecurity::PROTECT_DB_AND_PATCHES);
    }
}

?>
