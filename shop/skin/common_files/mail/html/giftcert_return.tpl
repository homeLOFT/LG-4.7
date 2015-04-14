{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, giftcert_return.tpl, joy
vim: set ts=2 sw=2 sts=2 et:
*}
{config_load file="$skin_config"}
{include file="mail/html/mail_header.tpl"}
<br />{include file="mail/salutation.tpl" salutation=$giftcert.recipient}

<br />
{$lng.eml_rma_giftcert_note|substitute:"returnid":$returnid:"amount":$giftcert.amount}

<br />{$lng.lbl_message}:
<br />
{$giftcert.message}

<br />
<table border="1" cellpadding="20" cellspacing="0">
<tr><td>{$lng.lbl_gc_id}: {$giftcert.gcid}</td></tr>
</table>

<br /><pre>{$lng.eml_gc_body}</pre>

{include file="mail/html/signature.tpl"}
