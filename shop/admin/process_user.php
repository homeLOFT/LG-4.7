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
 * Process users-related group actions (delete/export)
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Admin interface
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    3968cba5ecdb78320d43cbe05a25fe35597bc800, v107 (xcart_4_7_0), 2015-02-17 13:29:01, process_user.php, aim
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

require __DIR__.'/auth.php';
require $xcart_dir.'/include/security.php';

x_load('category','export','mail',
'user'); // For XCUserSql and other

if ($REQUEST_METHOD == 'POST') {

    if ($mode == 'export' && !empty($user)) {

        // Export some user(s)
        func_export_range_save('USERS', array_keys($user));

        $top_message['content'] = func_get_langvar_by_name("lbl_export_users_add");
        $top_message['type'] = 'I';
        func_header_location("import.php?mode=export");

    } elseif ($mode == 'group_operation') {

        $change_statement = array();

        if (!empty($op_change_password)) {
            // Require to change password at next log in
            $change_statement[] = "change_password='Y'";
        }

        if (!empty($op_change_status)) {
            // Enable/suspend accounts
            $change_statement[] = "status='".($op_change_status==='N'?'N':'Y')."'";
            $change_statement[] = "suspend_date='".($op_change_status==='N'?XC_TIME:0)."'";
            $new_status = ($op_change_status==='N'?'N':'Y');
        }

        if (!empty($op_change_activity)) {
            // Enable/suspend accounts
            $new_activity = ($op_change_activity === 'N' ? 'N' : 'Y');
        }

        $operation_ok = false;

        if (
            !empty($change_statement)
            || !empty($new_activity)
        ) {
            if (!empty($change_statement)) {
                $change_statement = "SET ".implode(', ', $change_statement);
            }

            $recount_providers = array();

            if ($for_users == 'A') {

                // For all found users

                if (x_session_is_registered('users_search_condition')) {

                    x_session_register('users_search_condition');
                    x_session_unregister('users_search_condition');
                    $_all_users_search_condition = ($users_search_condition ? $users_search_condition : "WHERE 1") . " AND id<>'$logged_userid'";

                    // For optimization purposes new_status condition is checked
                    if (!empty($new_status))
                        $changed_admin_users = func_query("SELECT * FROM $sql_tbl[customers] $_all_users_search_condition AND " . XCUserSignature::getApplicableSqlCondition() . " AND status!='$new_status'");

                    if (!empty($change_statement)) {
                        db_query("UPDATE $sql_tbl[customers] $change_statement $_all_users_search_condition");
                        db_query("UPDATE $sql_tbl[customers] SET activation_key='' ".($users_search_condition ? $users_search_condition." AND " : "WHERE ")."status!='N' AND activation_key!='' AND id<>'$logged_userid'");
                    }
                    if (!empty($new_activity)) {
                        XCUserActivity::updateUsers($new_activity, $_all_users_search_condition);
                    }

                    $operation_ok = true;

                    if (!empty($op_change_activity)) {
                        // (usertype='P' OR usertype='A')
                        $_providers_condition = " AND (usertype='P' ".(!empty($active_modules['Simple_Mode'])?"OR usertype='A'":"").") ";
                        $recount_providers = func_query_column("SELECT id FROM $sql_tbl[customers] ".($users_search_condition ? $users_search_condition : "WHERE 1")." $_providers_condition AND id<>'$logged_userid'");
                    }
                }

            } else {

                // For selected users only

                if (is_array($user)) {
                    foreach ($user as $k => $v)
                        $to_update[] = "'$k'";

                    if (!empty($op_change_activity))
                        $recount_providers = array_keys($user);

                    $to_update = implode(",", $to_update);
                    $_all_users_search_condition = "WHERE id IN ($to_update) AND id <> '$logged_userid'";

                    // For optimization purposes new_status condition is checked
                    if (!empty($new_status))
                        $changed_admin_users = func_query("SELECT * FROM $sql_tbl[customers] $_all_users_search_condition AND " . XCUserSignature::getApplicableSqlCondition() . " AND status!='$new_status'");

                    if (!empty($change_statement)) {
                        db_query("UPDATE $sql_tbl[customers] $change_statement $_all_users_search_condition");
                        db_query("UPDATE $sql_tbl[customers] SET activation_key='' WHERE status!='N' AND activation_key!='' AND id IN ($to_update)");
                    }

                    if (!empty($new_activity)) {
                        XCUserActivity::updateUsers($new_activity, $_all_users_search_condition);
                    }

                    $operation_ok = true;

                }

            }

            // For optimization purposes only new_status condition is checked
            if (!empty($changed_admin_users)) {
                foreach ($changed_admin_users as $old_admin_user) {
                    func_update_user_signature($old_admin_user, array('status' => $new_status));
                }
            }
        }

        if ($operation_ok) {
            $messages = array();
            if (!empty($op_change_password))
                $messages[] = func_get_langvar_by_name('msg_adm_require_to_change_password');

            if (!empty($op_change_status)) {
                $messages[] = func_get_langvar_by_name($op_change_status==='N'?'msg_adm_accounts_login_suspended':'msg_adm_accounts_login_enabled');
            }

            if (!empty($op_change_activity) && !empty($recount_providers)) {
                $messages[] = func_get_langvar_by_name($op_change_activity==='N'?'msg_adm_accounts_activity_disabled':'msg_adm_accounts_activity_enabled');
                $p_categories = db_query("SELECT $sql_tbl[products_categories].categoryid FROM $sql_tbl[products] INNER JOIN $sql_tbl[products_categories] ON $sql_tbl[products].productid = $sql_tbl[products_categories].productid AND $sql_tbl[products].provider IN ('".implode("','",$recount_providers)."') GROUP BY $sql_tbl[products_categories].categoryid ORDER BY NULL");
                if ($p_categories) {
                    $cats = array();
                    while ($row = db_fetch_array($p_categories)) {
                        $cats[] = $row['categoryid'];

                        if (count($cats) >= 100) {
                            func_recalc_product_count(func_array_merge($cats, func_get_category_parents($cats)));
                            $need_rebuild_fc_categories = TRUE;
                            $cats = array();
                        }
                    }

                    if (!empty($cats)) {
                        func_recalc_product_count(func_array_merge($cats, func_get_category_parents($cats)));
                        $need_rebuild_fc_categories = TRUE;
                    }

                    // Update categories data cache for Fancy categories module
                    // Must be run after func_recalc_product_count/func_cat_tree_rebuild/func_recalc_subcat_count
                    if (!empty($active_modules['Flyout_Menus']) && func_fc_use_cache() && !empty($need_rebuild_fc_categories)) {
                        func_fc_build_categories(1);
                    }

                    db_free_result($p_categories);
                }
            }

            if (!empty($messages)) {
                $top_message['content'] = implode('<hr width="20%" size="1" noshade="noshade" />', $messages);
            }
        }

    } elseif ($mode == 'delete') {

        // Request to delete user profile

        x_session_register('users_to_delete');

        if (!empty($confirmed) && $confirmed == 'Y') {

            // If request is confirmed

            require $xcart_dir.'/include/safe_mode.php';

            if (is_array($users_to_delete['user'])) {
                foreach ($users_to_delete['user'] as $user=>$v) {

                    // Delete user from database

                    $usertype = func_query_first_cell("SELECT usertype FROM $sql_tbl[customers] WHERE id='$user'");
                    if (empty($usertype))
                        continue;

                    $olduser_info = func_userinfo($user,$usertype);
                    $to_customer = $olduser_info['language'];
                    func_delete_profile($user, $usertype, true, false, (isset($next_provider[$user]) ? $next_provider[$user] : false));
                    x_log_flag('log_activity', 'ACTIVITY', "'$login' user has deleted '$user' profile");

                    // Send mail notifications to customer department and signed customer
                    $mail_smarty->assign('userinfo',$olduser_info);

                    // Send mail to registered user

                    if (
                        $config['Email_Note']['eml_profile_deleted'] == 'Y'
                        && !XCUserSql::isAnonymousUser($olduser_info)
                    ) {
                        func_send_mail($olduser_info['email'], 'mail/profile_deleted_subj.tpl', 'mail/profile_deleted.tpl', $config['Company']['users_department'], false);
                    }

                    // Send mail to customers department

                    if ($config['Email_Note']['eml_profile_deleted_admin'] == 'Y')
                        func_send_mail($config['Company']['users_department'], 'mail/profile_admin_deleted_subj.tpl', 'mail/profile_admin_deleted.tpl', $olduser_info['email'], true);

                }

                // Prepare the message

                $top_message['content'] = func_get_langvar_by_name('msg_adm_users_del');
                $top_message['type'] = 'I';
            } else {

                // If no selected users display the warning

                $top_message['content'] = func_get_langvar_by_name('msg_adm_warn_users_sel');
                $top_message['type'] = 'W';
            }

            x_session_unregister('users_to_delete');

        } else {
            $users_to_delete['user'] = $user;
            $users_to_delete['pagestr'] = $pagestr;
            func_header_location("process_user.php?mode=delete");
        }

    } elseif ($mode == 'update') {

        if (!empty($active_modules['XAffiliate']) && isset($plan) && is_array($plan)) {
            foreach ($plan as $u => $planid) {
                func_array2insert(
                    'partner_commissions',
                    array(
                        'userid' => $u,
                        'plan_id' => intval($planid)
                    ),
                    true
                );
            }
        }

        $top_message = array(
            'content' => func_get_langvar_by_name("lbl_users_have_been_updated")
        );
    }

    func_header_location("users.php?mode=search" . (empty($pagestr) ? '' : $pagestr));
}

if ($mode == 'delete') {

    // Prepare for deleting users profiles

    x_session_register('users_to_delete');

    $users = array();
    if (is_array($users_to_delete['user'])) {
        $location[] = array(func_get_langvar_by_name('lbl_users_management'), 'users.php');
        $location[] = array(func_get_langvar_by_name('lbl_delete_users'), '');

        $users = func_query("SELECT * FROM $sql_tbl[customers] WHERE id IN ('".implode("', '", array_keys($users_to_delete["user"]))."') ORDER BY login DESC, lastname DESC, firstname DESC");
    }

    if (is_array($users) && count($users) > 0) {

        $providers = array();
        foreach ($users as $k => $v) {
            $users[$k]['usertype_name'] = $usertypes[$v['usertype']];
            if (in_array($v['usertype'], array("A", "P"))) {
                $users[$k]['provider_counters'] = func_get_provider_counters($v['id']);
                $users[$k]['is_provider_profile'] = ($v['usertype'] == "P" && !$single_mode);
                $providers[] = $v['id'];
            }
        }

        $smarty->assign('users', $users);
        if (!empty($users_to_delete['pagestr']))
            $smarty->assign('pagestr', $users_to_delete['pagestr']);

        if (count($providers) > 0) {
            $smarty->assign('move_to_providers', func_get_next_providers($providers));
        }

        $smarty->assign('main', 'user_delete_confirmation');

        include $xcart_dir . DIR_ADMIN . '/users_tools.php';

        // Assign the current location line
        $smarty->assign('location', $location);

        // Assign the section navigation data
        $smarty->assign('dialog_tools_data', $dialog_tools_data);

        if (is_readable($xcart_dir.'/modules/gold_display.php')) {
            include $xcart_dir.'/modules/gold_display.php';
        }
        func_display('admin/home.tpl', $smarty);
        exit;
    }

    // If no selected users display the warning

    $top_message['content'] = func_get_langvar_by_name('msg_adm_warn_users_sel');
    $top_message['type'] = 'W';
}

func_header_location("users.php?mode=search".(!empty($users_to_delete['pagestr'])?$users_to_delete['pagestr']:''));

?>
