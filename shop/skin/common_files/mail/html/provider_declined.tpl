{*
b59a3fe9cc6e4cadd77cd776fc64ad799fcd1b06, v2 (xcart_4_4_1), 2010-09-21 12:13:23, provider_declined.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
{include file="mail/html/mail_header.tpl"}
<br /><br />
{include file="mail/salutation.tpl" title=$userinfo.title firstname=$userinfo.firstname lastname=$userinfo.lastname}<br />
<br />
{$lng.eml_partner_declined}<br />
<br />
{if $reason ne ""}
<b>{$lng.eml_reason}:</b><br />
{$reason}<br />
<br />
{/if}

{include file="mail/html/signature.tpl"}
