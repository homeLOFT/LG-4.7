{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, giftcert_notification.tpl, joy
vim: set ts=2 sw=2 sts=2 et:
*}
{config_load file="$skin_config"}
{include file="mail/html/mail_header.tpl"}
<br />{$lng.eml_gc_notification|substitute:"recipient":$giftcert.recipient}

<br />{$lng.eml_gc_copy_sent|substitute:"email":$giftcert.recipient_email}:

<br />=========================| start |=========================

<table cellpadding="15" cellspacing="0" width="100%"><tr><td bgcolor="#EEEEEE">
{include file="mail/html/giftcert.tpl"}
</td></tr></table>

<br />=========================| end |=========================

{include file="mail/html/signature.tpl"}
