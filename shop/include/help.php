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
 * Help section actions processor
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Lib
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v130 (xcart_4_7_0), 2015-02-17 23:56:28, help.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if ( !defined('XCART_SESSION_START') ) { header("Location: ../"); die("Access denied"); }

x_load('crypt','mail',
'user', // For XCUserSql and other
'templater','pages');

$location[] = array(func_get_langvar_by_name('lbl_help_zone'), 'help.php');

if (!empty($logged_userid)) {

    $userinfo = func_userinfo($logged_userid, $login_type);

    if (!empty($userinfo['address']['B'])) {
        $__address_fields = (array) $userinfo['address']['B'];
        $__address_fields_list = array_keys($default_address_book_fields);
        foreach ($__address_fields_list as $__address_field) {
            if (isset($__address_fields[$__address_field])) {
                $userinfo[$__address_field] = $__address_fields[$__address_field];
            }
        }
    }
}

if (empty($section))
    $section = '';

if (empty($action))
    $action = '';

if (strpos($section, 'Password_Recovery') !== false || $action == 'recover_password') {
    // Define name of the recover field depending on login setting: email or username
    $recover_field_name = func_get_langvar_by_name(
        'lbl_' . ($config['email_as_login'] == 'Y' ? 'email' : 'email_username'),
        NULL,
        false,
        true
    );

    $smarty->assign('recover_field_name', $recover_field_name);
}

if ($action == 'contactus' || $section == 'contactus') {

    if (!empty($active_modules['Kayako_Connector'])) {
        func_kayako_help_desk_redirect($action, $err, $mode);
    }

    x_session_register('store_contactus');

    $additional_fields = func_get_add_contact_fields($current_area);

    $default_fields = func_get_default_fields($current_area, 'contact_us');

    $is_areas = array(
        'C' => (
            !empty($default_fields['title']['avail']) ||
            !empty($default_fields['firstname']['avail']) ||
            !empty($default_fields['lastname']['avail']) ||
            !empty($default_fields['company']['avail']) ||
            !empty($default_fields['email']['avail']) ||
            !empty($default_fields['url']['avail'])
        ),
        'A' => (
            !empty($default_fields['address']['avail']) ||
            !empty($default_fields['address_2']['avail']) ||
            !empty($default_fields['city']['avail']) ||
            !empty($default_fields['county']['avail']) ||
            !empty($default_fields['state']['avail']) ||
            !empty($default_fields['country']['avail']) ||
            !empty($default_fields['zipcode']['avail']) ||
            !empty($default_fields['phone']['avail']) ||
            !empty($default_fields['fax']['avail'])
        ),
    );

    include $xcart_dir . '/include/states.php';
    include $xcart_dir . '/include/countries.php';

    if ($config['General']['use_counties'] == 'Y') {
        include $xcart_dir . '/include/counties.php';
    }

    if (isset($err) && !empty($store_contactus)) {
        $userinfo = $store_contactus;
        if ($userinfo['fillerror'])
            $fillerror = $userinfo['fillerror'];

        if ($userinfo['antibot_contactus_err'])
            $antibot_contactus_err = $userinfo['antibot_contactus_err'];

        func_unset($userinfo, 'fillerror', 'antibot_contactus_err');
    }
}

if ($REQUEST_METHOD == 'POST' && $action == 'contactus') {

    // Send mail to support

    $body = $_POST['body'] = stripslashes($_POST['body']);

    $contact = $_POST;

    $contact['titleid'] = func_detect_title($contact['title']);

    x_session_register('antibot_contactus_err');
    $antibot_contactus_err = (!empty($active_modules['Image_Verification']) && func_validate_image("on_contact_us", $antibot_input_str));

    //Fill values for func_check_required_fields/emails templates
    if (is_array($additional_fields)) {
        $contact['additional_fields'] = array();
        foreach ($additional_fields as $k => $v) 
            $additional_fields[$k]['value'] = $contact['additional_fields'][$k]['value'] = stripslashes($contact['additional_values'][$k]);
    }

    // Check required fields
    $fillerror = !func_check_required_fields($contact, $current_area, 'contact_us');

    // Check email
    if (
        (
            $default_fields['email']['required'] == 'Y'
            || !empty($contact['email'])
        )
        && !func_check_email($contact['email'])
    ) {
        $fillerror = true;
    }

    // Check subject and body
    if (!$fillerror) {
        $fillerror = (empty($subject) || empty($body));
    }

    if (!$fillerror && !$antibot_contactus_err) {

        $contact['statename'] = func_get_state(stripslashes($contact['state']), stripslashes($contact['country']));
        $contact['countryname'] = func_get_country(stripslashes($contact['country']));

        if ($config['General']['use_counties'] == 'Y')
            $contact['countyname'] = func_get_county($contact['county']);

        $contact = func_stripslashes($contact);

        $mail_smarty->assign('contact', $contact);
        $mail_smarty->assign('default_fields', $default_fields);
        $mail_smarty->assign('is_areas', $is_areas);
        $mail_smarty->assign('additional_fields', $additional_fields);

        if (
            !func_send_mail(
                $config['Company']['support_department'],
                'mail/help_contactus_subj.tpl',
                'mail/help_contactus.tpl',
                $contact['email'],
                false
            )
        ) {
            $top_message = array(
                'type'         => 'E',
                'content'     => func_get_langvar_by_name("lbl_send_mail_error")
            );

            $userinfo = $_POST;
            $userinfo['login'] = $userinfo['uname'];
            $store_contactus = $userinfo;

        } else {

            $store_contactus = false;
            func_header_location("help.php?section=contactus&mode=sent");

        }

    } else {

        $userinfo                          = func_stripslashes($_POST);
        $userinfo['login']                 = $userinfo['uname'];
        $userinfo['fillerror']             = $fillerror;
        $userinfo['antibot_contactus_err'] = $antibot_contactus_err;

        $store_contactus = $userinfo;

        $top_message = array(
            'type'    => 'E',
            'content' => func_get_langvar_by_name($fillerror ? 'txt_registration_error' : 'msg_err_antibot')
        );

    }

    func_header_location("help.php?section=contactus&mode=update&err=1");
}

/**
 * Recover password
 */
if (
    $REQUEST_METHOD == 'POST'
    && $action == 'recover_password'
    && !empty($username)
) {

    $antibot_pwd_err = !empty($active_modules['Image_Verification']) && func_validate_image('on_pwd_recovery', $antibot_input_str);

    if ($antibot_pwd_err) {

        $top_message['content'] = func_get_langvar_by_name('msg_err_antibot');
        $top_message['type'] = 'E';

        func_header_location('help.php?section=Password_Recovery_error&err_type=antibot&username=' . urlencode(stripslashes($username)));

    }

    $utype = !empty($active_modules['Simple_Mode']) && $current_area == 'A' ? 'P' : $current_area;
    $accounts = func_query_first("SELECT id, login, password, email, usertype, status FROM $sql_tbl[customers] WHERE login='$username' AND usertype='$utype' AND " . XCUserSql::getSqlRegisteredCond());

    if (!empty($accounts)) {
        $accounts = array($accounts);
    } else {
        $accounts = func_query("SELECT id, login, password, email, usertype, status FROM $sql_tbl[customers] WHERE email='$username' AND usertype='$utype' AND " . XCUserSql::getSqlRegisteredCond());
    }

    if (empty($accounts)) {

        $top_message['content'] = func_get_langvar_by_name('txt_email_not_match', array('login_field' => $recover_field_name), false, true);
        $top_message['type'] = 'E';

        func_header_location('help.php?section=Password_Recovery_error&username=' . urlencode(stripslashes($username)));

    }

    $active_accounts_counter = 0;

    foreach ($accounts as $key => $account) {

        if ($account['status'] != 'Y') {
            continue;
        }

        $active_accounts_counter++;

        $account['password_reset_key'] = func_add_password_reset_key($account['id']);
        func_send_mail_password_recover($account);
    }

    if ($active_accounts_counter == 0) {
        // Account is suspended
        $top_message['content'] = func_get_langvar_by_name('err_account_temporary_disabled');
        $top_message['type'] = 'E';

        func_header_location('help.php?section=Password_Recovery&username=' . urlencode(stripslashes($username)));
    }

    func_header_location('help.php?section=Password_Recovery_message&email=' . urlencode(stripslashes($account['email'])));

} elseif (
    $REQUEST_METHOD == 'POST'
    && $action == 'recover_password'
    && empty($username)
) {

    $top_message['type']    = 'E';
    $top_message['content'] = func_get_langvar_by_name('txt_registration_error');

    func_header_location("help.php?section=Password_Recovery_error&username=" . urlencode(stripslashes($username)));

}

$pageid = -1;
switch ($section) {
    case 'Password_Recovery':
    case 'Password_Recovery_error':
        $location[] = array(func_get_langvar_by_name('lbl_forgot_password'), '');
        break;

    case 'Password_Recovery_message':
        $location[] = array(func_get_langvar_by_name('lbl_confirmation'), '');
        break;

    case 'FAQ':
        $location[] = array(func_get_langvar_by_name('lbl_faq'), '');
        $pageid = func_get_pageid_by_alias($section);
        break;

    case 'contactus':
        $location[] = array(func_get_langvar_by_name('lbl_contact_us'), '');
        break;

    case 'about':
        $location[] = array(func_get_langvar_by_name('lbl_about_our_site'), '');
        $pageid = func_get_pageid_by_alias($section);
        break;

    case 'business':
        $location[] = array(func_get_langvar_by_name('lbl_privacy_statement'), '');
        $pageid = func_get_pageid_by_alias($section);
        break;

    case 'conditions':
        $location[] = array(func_get_langvar_by_name('lbl_terms_n_conditions'), '');
        $pageid = func_get_pageid_by_alias($section);
        break;

    case 'publicity':
        $location[] = array(func_get_langvar_by_name('lbl_publicity'), '');
        $pageid = func_get_pageid_by_alias($section);
        break;
}

if ($current_area == 'C') {

    if ($pageid > 0) {

        func_header_location('pages.php?pageid=' . $pageid);

    } elseif (!$pageid) {

        func_page_not_found();

    }

}

if (isset($username)) {
    $username = preg_replace(
        '/' . func_login_validation_regexp(true) . '/s',
        '',
        stripslashes($username)
    );
    $smarty->assign('username', $username);
}

if (isset($email)) {
    $email = stripslashes($email);
    if (func_check_email($email)) {
        $smarty->assign('email', $email);
    }
}

if (
    !empty($active_modules['Image_Verification'])
    && !empty($antibot_contactus_err)
) {

    $smarty->assign('antibot_contactus_err', @$antibot_contactus_err);
    x_session_unregister('antibot_contactus_err');
}

if (isset($vid)) {
    $smarty->assign('prepare_fields', func_wm_tpl_prep($vid));
}

$smarty->assign('userinfo',             @$userinfo);
$smarty->assign('fillerror',            @$fillerror);
$smarty->assign('default_fields',       @$default_fields);
$smarty->assign('additional_fields',    @$additional_fields);
$smarty->assign('titles',               func_get_titles());
$smarty->assign('help_section',         @$section);
$smarty->assign('main',                 'help');

$smarty->clear_assign('location');

?>
