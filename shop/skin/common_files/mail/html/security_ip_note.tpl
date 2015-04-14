{*
70d51006be7028e098cc22c1ecd7d8ae209b0891, v2 (xcart_4_5_5), 2012-12-11 11:34:38, security_ip_note.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
{config_load file="$skin_config"}
{include file="mail/html/mail_header.tpl"}

{if $mode eq 'L'}
{$lng.txt_security_ip_login_note|substitute:"date":$date:"local_login":$local_login:"ip":$ip}
{else}
{$lng.txt_security_ip_note|substitute:"date":$date:"local_login":$local_login:"ip":$ip}
{/if}
<br />
<br />
<a href="{$url}">{$url}</a>

{include file="mail/html/signature.tpl"}
