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
 * User profile modification interface
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Admin interface
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    3968cba5ecdb78320d43cbe05a25fe35597bc800, v86 (xcart_4_7_0), 2015-02-17 13:29:01, user_modify.php, aim
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

define('USE_TRUSTED_POST_VARIABLES', 1);
define('USE_TRUSTED_SCRIPT_VARS', 1);
$trusted_post_variables = array('passwd1', 'passwd2');

require __DIR__.'/auth.php';
require $xcart_dir.'/include/security.php';

// Redirect to personal details page if admin tries
// to edit himself
if ($logged_userid == $_GET['user']) {
    func_header_location('register.php?mode=update');
}

x_load('mail','user');

if (!empty($_GET['user']) && !empty($_GET['usertype'])) {
    if (func_query_first_cell("SELECT id FROM $sql_tbl[customers] WHERE id = '".addslashes($_GET['user'])."' AND usertype='".addslashes($_GET['usertype'])."'") == '')
        func_page_not_found();
}

define('USER_MODIFY', 1);

$display_antibot = false;

$location[] = array(func_get_langvar_by_name('lbl_users_management'), 'users.php');

$_usertype = (($usertype == 'P' && !empty($active_modules['Simple_Mode'])) ? 'A' : $usertype);

$_loc_type = array (
    'A' => 'lbl_modify_admin_profile',
    'P' => 'lbl_modify_provider_profile',
    'C' => 'lbl_modify_customer_profile'
);

if (!empty($active_modules['XAffiliate'])) {
    $_loc_type['B'] = 'lbl_modify_partner_profile';
}

if (isset($_loc_type[$_usertype])) {
    $location[] = array(func_get_langvar_by_name($_loc_type[$_usertype]), '');

} elseif (!empty($_usertype)) {
    $top_message = array(
        'content' => func_get_langvar_by_name('txt_wrong_usertype_modify'),
        'type' => 'E'
    );

    func_header_location('users.php');
}

include $xcart_dir . DIR_ADMIN . '/user_modify_tools.php';

$smarty->assign('usertype_name', $usertypes[$usertype]);

/**
 * Update profile only
 */
$mode = 'update';

if ($REQUEST_METHOD=="POST")
    require $xcart_dir.'/include/safe_mode.php';

if (!empty($submode) && $submode == 'seller_address' && $single_mode)
    func_header_location("user_modify.php?user=$user&usertype=$usertype");

/**
 * Update provider seller address
 */
if ($REQUEST_METHOD == 'POST' && $_GET['usertype'] == 'P' && isset($_POST['submode']) && $_POST['submode'] == 'seller_address') {

    x_load('user');

    $_fields = array('address', 'address_2', 'city', 'state', 'country', 'zipcode');
    $saved_data = $posted_data = array();
    $posted_data['userid'] = $_GET["user"];
    foreach($_fields as $_field)
        if (isset($_field)) {
            $posted_data[$_field] = $_POST[$_field];
            $saved_data['seller_'.$_field] = $posted_data[$_field];
        }

    $top_message = array();
    if (func_update_seller_address($posted_data)) {
        $top_message['content'] = func_get_langvar_by_name("msg_seller_address_upd");
    }
    else {
        x_session_register('profile_modified_data');
        $profile_modified_data[$_GET['user']] = $saved_data;

        $top_message['content'] = func_get_langvar_by_name("msg_err_profile_upd");
        $top_message['type'] = 'E';
        $top_message['reg_error'] = 'Y';
    }

    func_header_location("user_modify.php?user=".$_GET['user']."&usertype=P&submode=seller_address");

}
elseif (
    $REQUEST_METHOD == 'POST' 
    && (
        $_GET['usertype'] == 'B' 
        || $_GET['usertype'] == 'P'
    )
) {

    $current_status = func_query_first_cell("SELECT status FROM $sql_tbl[customers] WHERE usertype = '$_GET[usertype]' AND id = '$_GET[user]'");

    if (
        (
            !empty($current_status) 
            && !empty($status) 
            && $current_status != $status
        ) 
        || (
            @$_POST['mode'] == 'approved' 
            || @$_POST['mode'] == 'declined'
        )
    ) {

        $userinfo = func_userinfo($_GET['user'], $_GET['usertype'], FALSE, FALSE, NULL, TRUE, SKIP_CACHE);
        $mail_smarty->assign('userinfo', $userinfo);
        
        $mail_usertype = ($_GET['usertype'] == 'B' ? 'partner' : 'provider');

        if ($_POST['mode'] == 'approved' || $status == 'Y') {

            $allow_approve_email = array(
                'B' => ($config['XAffiliate']['eml_partner_approved'] == 'Y'),
                'P' => ($config['Email_Note']['eml_provider_approved'] == 'Y')
            );

            if ($allow_approve_email[$_GET['usertype']]) {
                func_send_mail($userinfo['email'],
                    "mail/{$mail_usertype}_approved_subj.tpl",
                    "mail/{$mail_usertype}_approved.tpl",
                    $config['Company']['users_department'], false);
            }

            func_update_user_status($userinfo, 'Y');
        } elseif ($_POST['mode'] == 'declined' || $status == 'D') {
            $mail_smarty->assign('reason', $reason);

            $allow_decline_email = array(
                'B' => ($config['XAffiliate']['eml_partner_declined'] == 'Y'),
                'P' => ($config['Email_Note']['eml_provider_declined'] == 'Y')
            );


            if ($allow_decline_email[$_GET['usertype']]) {
                func_send_mail($userinfo['email'],
                    "mail/{$mail_usertype}_declined_subj.tpl",
                    "mail/{$mail_usertype}_declined.tpl",
                    $config['Company']['users_department'], false);
            }

            func_update_user_status($userinfo, 'D');
        }
    }

    if (@$_POST['mode'] == 'approved' || @$_POST['mode'] == 'declined')
        func_header_location("user_modify.php?user=" . $_GET['user']."&usertype=".$_GET['usertype']);
}

if (
    !empty($active_modules['XPayments_Connector'])
    && 'delete_saved_card' == $action
    && !empty($id)
) {

    func_xpay_func_load();

    $res = func_xpc_delete_saved_card($_GET['user'], $id);
    $msg = func_get_langvar_by_name('txt_saved_card_removed');

    if ($res) {
        $top_message = array(
            'type'    => 'I',
            'content' => $msg,
        );
    }

    func_header_location("user_modify.php?user=" . $_GET['user']."&usertype=".$_GET['usertype']);
}


$login_ = $login;
$login_type_ = $login_type;
$logged_userid_ = $logged_userid;

$login_type = $_GET['usertype'];
$logged_userid = intval($_GET['user']);
$login = func_get_login_by_userid($logged_userid);

/**
 * Where to forward <form action
 */
$smarty->assign('register_script_name', ( ($config['Security']['use_https_login']=="Y") ? $xcart_catalogs_secure['admin'] . "/" : "") . "user_modify.php");

require $xcart_dir.'/include/register.php';

switch ($usertype) {
    case 'P':
        $tpldir = 'provider';
        break;

    case 'B':
        $tpldir = 'partner';
        break;

    default:
        $tpldir = 'admin';
}

if (!empty($active_modules['Simple_Mode']) && ($usertype=="A" || $usertype=="P"))
    $tpldir = 'admin';

// Display the 'Activity' input box for admin, provider or partner
if (XCUserActivity::isActivityApplicable($usertype)) {
    $smarty->assign('display_activity_box', 'Y');
}

$smarty->assign('main', 'user_profile');
$smarty->assign('tpldir', $tpldir);

$login = $login_;
$login_type = $login_type_;
$logged_userid = $logged_userid_;

if (!empty($page)) {
    $smarty->assign('navigation_page', $page);
}

if (!empty($active_modules['XPayments_Connector'])) {

    func_xpay_func_load();

    $saved_cards = func_xpc_get_saved_cards($user);
    $default_card_id = func_xpc_get_default_card($user);
    $smarty->assign('saved_cards', $saved_cards);
    $smarty->assign('default_card_id', $default_card_id);
}

// Assign the current location line
$smarty->assign('location', $location);

// Assign the section navigation data
$smarty->assign('dialog_tools_data', $dialog_tools_data);
$smarty->assign('display_antibot', $display_antibot);

if (is_readable($xcart_dir.'/modules/gold_display.php')) {
    include $xcart_dir.'/modules/gold_display.php';
}
func_display('admin/home.tpl',$smarty);
?>
