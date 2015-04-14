{*
e94f191a2cc0cb8dbefd384fabaa6f48af5f3c11, v1 (xcart_4_6_2), 2013-12-25 09:14:58, security_ip_note.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
{config_load file="$skin_config"}
{include file="mail/html/mail_header.tpl"}

{capture name="row"}
{if $mode eq 'L'}
{$lng.txt_security_ip_login_note|substitute:"date":$date:"local_login":$local_login:"ip":$ip}
{else}
{$lng.txt_security_ip_note|substitute:"date":$date:"local_login":$local_login:"ip":$ip}
{/if}
<br />
<br />
<a href="{$url}">{$url}</a>
{/capture}
{include file="mail/html/responsive_row.tpl" content=$smarty.capture.row}

{include file="mail/html/signature.tpl"}
