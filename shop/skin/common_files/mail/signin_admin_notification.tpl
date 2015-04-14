{*
b999578fe1441f5abb38e2e121356b0f30a2a500, v2 (xcart_4_5_5), 2012-12-12 18:29:53, signin_admin_notification.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
{include file="mail/mail_header.tpl"}


{$lng.eml_signin_admin_notification}

{$lng.lbl_profile_details}:
---------------------
{include file="mail/profile_data.tpl"}

{include file="mail/signature.tpl"}
