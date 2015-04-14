{*
e94f191a2cc0cb8dbefd384fabaa6f48af5f3c11, v1 (xcart_4_6_2), 2013-12-25 09:14:58, giftcert_return.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
{config_load file="$skin_config"}
{include file="mail/html/mail_header.tpl"}

{capture name="row"}
<h1>{include file="mail/salutation.tpl" salutation=$giftcert.recipient}</h1>

{$lng.eml_rma_giftcert_note|substitute:"returnid":$returnid:"amount":$giftcert.amount}
{/capture}
{include file="mail/html/responsive_row.tpl" content=$smarty.capture.row}

{capture name="row"}
{$lng.lbl_message}:<br />
<i>{$giftcert.message}</i>
{/capture}
{include file="mail/html/responsive_row.tpl" content=$smarty.capture.row}

{capture name="row"}
<table class="block-grid one-up">
<tr><td class="panel">{$lng.lbl_gc_id}: {$giftcert.gcid}</td></tr>
</table>
{/capture}
{include file="mail/html/responsive_row.tpl" content=$smarty.capture.row}

{capture name="row"}
{$lng.eml_gc_body|nl2br}
{/capture}
{include file="mail/html/responsive_row.tpl" content=$smarty.capture.row}

{include file="mail/html/signature.tpl"}
