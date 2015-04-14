{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, profile_modified.tpl, joy
vim: set ts=2 sw=2 sts=2 et:
*}
{include file="mail/html/mail_header.tpl"}

<br />{include file="mail/salutation.tpl" title=$userinfo.title firstname=$userinfo.firstname lastname=$userinfo.lastname}

<br />{$lng.txt_profile_modified}

<br />{$lng.lbl_your_profile}:

{include file="mail/html/profile_data.tpl"}

{include file="mail/html/signature.tpl"}
