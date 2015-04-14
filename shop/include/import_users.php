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
 * Users import/export library
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Lib
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v69 (xcart_4_7_0), 2015-02-17 23:56:28, import_users.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if ( !defined('XCART_SESSION_START') ) { header("Location: ../"); die("Access denied"); }

x_load('crypt','mail',
'user'); // For XCUserSql and other

/******************************************************************************
Used cache format:
Memberships:
    data_type:     M
    key:        <Membership name>
    value:        <Membership ID>
Users (by ID):
    data_type:     UI
    key:        <id>
    value:        <id | RESERVED>
Users (by login):
    data_type:     UL
    key:        <Login>
    value:        <Login | RESERVED>

Note: RESERVED is used if ID is unknown
******************************************************************************/

if ($import_step == 'define') {

    $import_specification['USERS'] = array(
        'script'        => '/include/import_users.php',
        'tpls'            => array(
            'main/import_option_password_crypt.tpl'
        ),
        'permissions'    => 'A',
        'is_range'        => $xcart_web_dir . DIR_ADMIN . '/users.php?is_range',
        'export_sql'    => 'SELECT id FROM ' . $sql_tbl['customers'],
        'table'            => 'customers',
        'key_field'        => 'id',
        'orderby'       => 15,
        'columns'        => array(
            'id'        => array(
                'is_key'    => true,
                'required'  => 0,
                'inherit'   => 0,
                'type'      => 'N',
                'default'   => 0
            ),
            'login'                    => array(
                'required'    => true
            ),
            'email'                    => array(
                'required'    => true
            ),
            'usertype'                => array(
                'required'    => true,
                'type'        => 'E',
                'variants'    => array(
                    'C', 'A', 'P', 'B'
                )
            ),
            'is_anonymous_customer' => array(
                'type' => 'E',
                'variants' => array('1','0'),
                'default'  => '0',
            ),
            'password'                => array(
                'required'    => false
            ),
            'title'                    => array(),
            'firstname'                => array(),
            'lastname'                => array(),
            'company'                => array(),
            'url'                    => array(),
            'status'                => array(
                'type'        => 'E',
                'variants'    => array(
                    'N', 'Y', 'Q', 'D', 'A'
                )
            ),
            'referer'                => array(),
            'ssn'                    => array(),
            'language'                => array(
                'type'        => 'C'
            ),
            'change_password'        => array(
                'type'        => 'B'
            ),
            'activity'                => array(
                'type'        => 'B'
            ),
            'membership'            => array(),
            'pending_membership'    => array(),
            'tax_number'            => array(),
            'tax_exempt'            => array(
                'type'        => 'B'
            ),
            'last_login'            => array(
                'type'        => 'D'
            ),
            'first_login'            => array(
                'type'        => 'D'
            ),
            'suspend_date'            => array(
                'type'        => 'D'
            ),
            'change_password_date'    => array(
                'type'        => 'D'
            )
        )
    );
}
elseif ($import_step == 'process_row') {

    /**
     * PROCESS ROW from import file
     */

    // Check ID
    if (
        !isset($values['id'])
        || !is_numeric($values['id'])
        || abs(intval($values['id'])) != $values['id']
    ) {
        $values['id'] = '';
    }

    if (
        !isset($values['login'])
        || empty($values['login'])
    ) {
        $values['login'] = $values['email'];
        $values['username'] = $values['email'];
    }
    elseif ($config['email_as_login'] == 'Y') {
        $values['username'] = $values['login'];
        $values['login'] = $values['email'];
    }

    // Do not import data for current user
    if (func_import_is_current_user($values))
        return false;

    $tmp = func_import_get_cache('UI', $values['id']);
    if (is_null($tmp)) {
        func_import_save_cache('UI', $values['id'], '');
    }

    // Check login

    $values['login'] = preg_replace(
        '/' . func_login_validation_regexp(true) . '/s',
        '',
        $values['login']
    );

    $tmp = func_import_get_cache('UL' . $values['usertype'], $values['login'] . (isset($values['is_anonymous_customer']) ? $values['is_anonymous_customer'] : ''));
    if (is_null($tmp)) {
        func_import_save_cache('UL' . $values['usertype'], $values['login'] . (isset($values['is_anonymous_customer']) ? $values['is_anonymous_customer'] : ''));
        if ($values['usertype'] == 'P') {
            func_import_save_cache('P', $values['login']);
        }
    }

    // Check parent
    if (
        !empty($values['parent'])
        && is_numeric($values['parent'])
    ) {
        $_parent = func_import_get_cache('UI', $values['parent']);
        if (is_null($_parent)) {
            $_parent = func_query_first_cell("SELECT id FROM $sql_tbl[customers] WHERE id = '$values[parent]'");
            if (empty($_parent)) {
                $_parent = NULL;
            }
            else {
                func_import_save_cache('UI', $values['parent'], $_parent);
            }
        }

        if (
            is_null($_parent)
            || (
                $action == 'do'
                && empty($_parent)
            )
        ) {
            func_import_module_error(
                'msg_err_import_log_message_29',
                array(
                    'login' => $values['parent']
                )
            );
            return false;
        }
    }

    // Check membership
    $values['membershipid'] = 0;
    if (!empty($values['membership'])) {
        $_membershipid = func_import_get_cache('M', $values['membership']);
        if (empty($_membershipid)) {
            $_membershipid = func_detect_membership($values['membership'], $values['usertype']);
            if ($_membershipid == 0) {
                // Membership is specified but does not exist
                func_import_module_error(
                    'msg_err_import_log_message_5',
                    array(
                        'membership' => $values['membership']
                    )
                );
                return false;
            }
            else {
                func_import_save_cache('M', $values['membership'], $_membershipid);
            }
        }

        if (!empty($_membershipid))
            $values['membershipid'] = $_membershipid;
    }

    // Check pending membership
    $values['pending_membershipid'] = 0;
    if (!empty($values['pending_membership'])) {
        $_membershipid = func_import_get_cache('M', $values['pending_membership']);
        if (empty($_membershipid)) {
            $_membershipid = func_detect_membership($values['pending_membership'], $values['usertype']);
            if ($_membershipid == 0) {
                // Membership is specified but does not exist
                func_import_module_error(
                    'msg_err_import_log_message_5',
                    array(
                        'membership' => $values['pending_membership']
                    )
                );
                return false;
            }
            else {
                func_import_save_cache('M', $values['pending_membership'], $_membershipid);
            }
        }

        if (!empty($_membershipid))
            $values['pending_membershipid'] = $_membershipid;
    }

    // Check email
    if (!empty($values['email'])) {
        if (!func_check_email($values['email'])) {
            func_import_module_error(
                'msg_err_import_log_message_28',
                array(
                    'email' => $values['email']
                )
            );
            return false;
        }
    }

    // Check title
    foreach (array('title','s_title','b_title') as $k) {
        if (empty($values[$k])) continue;

        if (func_detect_title($values[$k]) === false) {
            func_import_module_error(
                'msg_err_import_log_message_30',
                array(
                    'title' => $values[$k]
                )
            );

            return false;
        }
    }

    // Check permission of current admin to change email or add new admins/providers
    if (
        in_array($values['usertype'], array('A','P'))
        && ($check_perms_result = func_check_perms_redirect(XCActions::IMPORT_ADMIN_PROFILES, 'return_err_msg_code'))
        && $check_perms_result !== true
    ) {
        func_import_module_error(
            $check_perms_result['message_name'],
            array(
                'imported_email' => $values['email'],
                'unknown_IP' => isset($check_perms_result['unknown_IP']) ? $check_perms_result['unknown_IP'] : '',
                'sent_to_email' => isset($check_perms_result['sent_to_email']) ? $check_perms_result['sent_to_email'] : '',
            )
        );
    }

    $data_row[] = $values;
}
elseif ($import_step == 'finalize') {

    /**
     * FINALIZE rows processing: update database
     */

    // Drop old data
    if (!empty($import_file['drop'][strtolower($section)])) {

        $users = db_query("SELECT id, login, usertype FROM $sql_tbl[customers] WHERE id <> '$logged_userid'");
        if (!empty($users)) {
            while ($user = db_fetch_array($users)) {
                func_delete_profile($user['id'], $user['usertype'], false, false);
            }
        }

        $import_file['drop'][strtolower($section)] = "";
    }

    // Import users
    foreach ($data_row as $row) {

        if (
            (
                isset($row['id'])
                && $row['id'] == $logged_userid
            )
            || (
                $row['login'] == $login
                && $row['usertype'] == $login_type
            )
        ) {
            continue;
        }

        func_unset($row, 'membership', 'pending_membership');

        if ($import_file['crypt_password'] != 'Y') {

            $row['password'] = text_crypt(text_hash($row['password']));

        } else if (empty($passwords_already_hashed)) {

            $decrypted_password = text_decrypt($row['password']);

            if (!isset($passwords_already_hashed)) {
                require_once $xcart_dir . '/include/classes/class.XCPasswordHash.php';
                $passwords_already_hashed = XCPasswordHash::isPasswordHash($decrypted_password);
            }

            if (!$passwords_already_hashed) {
                // Backward compatibility for non-hashed passwords
                $row['password'] = text_crypt(text_hash($decrypted_password));
            }

        }

        $data = func_addslashes($row);

        $_userid = null;
        if (
            isset($data['id'])
            && !empty($data['id'])
        ) {
            $_userid = $data['id'];
        }
        else {
            $_userid = func_query_first_cell("SELECT id FROM $sql_tbl[customers] WHERE login = '" . addslashes($data['login']) . "' AND usertype='$data[usertype]' AND " . XCUserSql::getSqlCondByAnonymousFlag(empty($data['is_anonymous_customer']) ? 'registered' : 'anonymous'));
        }

        if ($_userid) {
            $_uexists = func_query_first_cell("SELECT COUNT(id) FROM $sql_tbl[customers] WHERE id = '$_userid'") > 0;
        }

        $need2update_signature =    !isset($data['usertype']) 
                                    || XCUserSignature::isApplicable($data);

        if (isset($data['signature']))
            unset($data['signature']);

        if (isset($data['activity'])) {
            $_activity = $data['activity'];
            unset($data['activity']);
        } else {
            $_activity = '';
        }

        if ($_userid && $_uexists) {

            // Update user by ID
            
            unset($data['id']);

            if ($need2update_signature) {
                $old_user_data = func_query_first("SELECT * FROM $sql_tbl[customers] WHERE id='$_userid'");
                $updated_user_data = func_array_merge($old_user_data, $data);
            }

            func_array2update(
                'customers',
                $data,
                "id='$_userid'"
            );
            $result[strtolower($section)]['updated']++;

        }
        else {

            // Add user
            $_userid = func_array2insert(
                'customers',
                $data,
                true
            );
            $result[strtolower($section)]['added']++;

            if ($need2update_signature) {
                $old_user_data = array();
                $updated_user_data = func_query_first("SELECT * FROM $sql_tbl[customers] WHERE id='$_userid'");
            }

        }

        // Update signature for admins and unknown usertype users
        if ($need2update_signature) {

            if (empty($old_user_data)) {
                $is_new_user = TRUE;
            } else {
                $obj_user = new XCUserSignature($old_user_data);
                $is_new_user = FALSE;
            }

            if (
                $is_new_user
                || $obj_user->checkSignature()
            ) {
                $obj_user = new XCUserSignature($updated_user_data);
                $obj_user->updateSignature();
            }
        }

        if (XCUserActivity::isActivityApplicable($data['usertype'])) {
            // Activate all providers by default
            $_activity = empty($_activity) ? 'Y' : $_activity;
            XCUserActivity::updateUser($_userid, $_activity);
        }

        func_import_save_cache('UI', $_userid, $_userid);

        if ($data['usertype'] == 'P') {
            if (!$single_mode) {
                func_mkdir(func_get_files_location($_userid, $data['usertype']));
            }
            
            func_import_save_cache('P', $_userid, $_userid);
        }

        func_flush('. ');
    }
}
elseif ($import_step == 'export') {

    /**
     * Export data
     */

    while (($id = func_export_get_row($data)) !== false) {

        if (empty($id)) {
            continue;
        }

        // Get data

        $row = func_query_first("SELECT $sql_tbl[customers].*, m.membership FROM $sql_tbl[customers] LEFT JOIN $sql_tbl[memberships] as m ON m.membershipid = $sql_tbl[customers].membershipid WHERE $sql_tbl[customers].id = '$id'");

        if (empty($row)) {
            continue;
        }

        $row['activity'] = XCUserActivity::getCharActivity($id, $row['usertype']);
        $row['pending_membership'] = func_query_first_cell("SELECT membership FROM $sql_tbl[memberships] WHERE membershipid = '$row[pending_membershipid]'");

        func_unset($row, 'membershipid', 'pending_membershipid');

        // Time zone offset correction
        $row['last_login']  += $config['Appearance']['timezone_offset'];
        $row['first_login'] += $config['Appearance']['timezone_offset'];

        // Write row
        if (!func_export_write_row($row)) {
            break;
        }
    }
}

?>
