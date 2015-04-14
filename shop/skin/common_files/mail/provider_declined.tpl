{*
b59a3fe9cc6e4cadd77cd776fc64ad799fcd1b06, v1 (xcart_4_4_1), 2010-09-21 12:13:23, provider_declined.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
{include file="mail/mail_header.tpl"}


{include file="mail/salutation.tpl" title=$userinfo.title firstname=$userinfo.firstname lastname=$userinfo.lastname}

{$lng.eml_partner_declined}

{if $reason ne ""}
{$lng.eml_reason}:
{$reason}
{/if}


{include file="mail/signature.tpl"}
