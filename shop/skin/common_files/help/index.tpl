{*
3b4a0b72e64014394ec76bb81bf426cc4d220155, v5 (xcart_4_6_5), 2014-08-04 13:25:33, index.tpl, mixon
vim: set ts=2 sw=2 sts=2 et:
*}
{if $section eq "Password_Recovery"}
{include file="help/Password_Recovery.tpl"}

{elseif $section eq "Password_Recovery_message"}
{include file="help/Password_Recovery_message.tpl"}

{elseif $section eq "Password_Recovery_error"}
{include file="help/Password_Recovery.tpl"}

{else}
{include file="page_title.tpl" title=$lng.lbl_help_zone}

{if $section eq "contactus"}
{include file="help/contactus.tpl"}

{elseif $section eq "conditions"}
{include file="help/conditions.tpl"}

{else}
{include file="help/general.tpl"}
{/if}

{/if}
