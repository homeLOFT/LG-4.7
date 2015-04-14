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

#
# 2b39e63712da5477e1aaf5cfa80d1370f583bce9, v52 (xcart_4_7_0), 2015-02-17 23:56:28, unsubscribe.php, Yuriy
#

require_once "../top.inc.php";
require_once $xcart_dir."/init.php";

x_load('mail');

include $xcart_dir."/include/get_language.php";

$redirect_to = $xcart_catalogs['customer'];
$email = trim($email);

if (!empty($active_modules['Adv_Mailchimp_Subscription'])) {

    func_mailchimp_unsubscribe_newslists($email);

} elseif (empty($active_modules['News_Management'])) {

	func_header_location($redirect_to."/home.php");

}

if (!func_check_email($email)) {
    $top_message = array(
        "type" => "E",
        "content" => func_get_langvar_by_name("err_subscribe_email_invalid")
    );
    func_header_location("home.php");
}

$subscribe_lng = $current_language;
$listid_cond = "";
if (!empty($listid)) {
	$listid_cond = " AND listid='$listid'";
	$subscribe_lng = func_query_first_cell("SELECT lngcode FROM $sql_tbl[newslists] WHERE listid='$listid'");
}

$c = func_query_first_cell("SELECT COUNT(*) FROM $sql_tbl[newslist_subscription] WHERE email='$email'".$listid_cond);
if ($c < 1) {
	func_header_location($redirect_to."/error_message.php?subscribe_bad_email");
}

db_query("DELETE FROM $sql_tbl[newslist_subscription] WHERE email='$email'".$listid_cond);

$saved_lng = $current_language;

#
# Send mail notification to customer
#
$mail_smarty->assign("email",$email);
if($config['News_Management']['eml_newsletter_unsubscr'] == 'Y') {
	$current_language = $subscribe_lng;
	func_send_mail($email, "mail/newsletter_unsubscribe_subj.tpl", "mail/newsletter_unsubscribe.tpl", $config["News_Management"]["newsletter_email"], false);
}
#
# Send mail notification to admin
#
if($config['News_Management']['eml_newsletter_unsubscr_admin'] == 'Y') {
	$current_language = '';
	func_send_mail($config["News_Management"]["newsletter_email"], "mail/newsltr_unsubscr_admin_subj.tpl", "mail/newsltr_unsubscr_admin.tpl", $email, true);
}

$current_language = $saved_lng;

func_header_location($redirect_to."/home.php?mode=unsubscribed&email=".urlencode(stripslashes($email)));
?>
