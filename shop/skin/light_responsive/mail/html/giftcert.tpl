{*
a74fe6885509c651f2ee37e8b41267a193293cc7, v1 (xcart_4_7_0), 2015-02-27 17:35:59, giftcert.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}
{if not $inline}
{config_load file="$skin_config"}
{include file="mail/html/mail_header.tpl"}
{/if}

{capture name="row"}
<h1>{include file="mail/salutation.tpl" salutation=$giftcert.recipient}</h1>

{if $giftcert.purchaser ne ""}{assign var="purchaser" value=$giftcert.purchaser}{else}{assign var="purchaser" value=$giftcert.purchaser_email}{/if}{currency value=$giftcert.amount assign="amount"}{$lng.eml_gc_header|substitute:"purchaser":$purchaser:"amount":$amount}
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

{if not $inline}
{include file="mail/html/signature.tpl"}
{/if}
