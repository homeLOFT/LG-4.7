{*
e94f191a2cc0cb8dbefd384fabaa6f48af5f3c11, v1 (xcart_4_6_2), 2013-12-25 09:14:58, signin_admin_notification.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
{include file="mail/html/mail_header.tpl"}

{capture name="row"}
{$lng.eml_signin_admin_notification}

<br /><br />{$lng.lbl_profile_details}:
{/capture}
{include file="mail/html/responsive_row.tpl" content=$smarty.capture.row}

{include file="mail/html/profile_data.tpl"}

{include file="mail/html/signature.tpl"}

