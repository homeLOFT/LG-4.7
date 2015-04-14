{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, wrong_bf_key.tpl, joy
vim: set ts=2 sw=2 sts=2 et:
*}
{include file="mail/html/mail_header.tpl"}

<br /><br />{$lng.lbl_cannot_decrypt_password|substitute:"user":$username}

<br /><br />{$lng.txt_bf_key_internal_error}

{include file="mail/html/signature.tpl"}
