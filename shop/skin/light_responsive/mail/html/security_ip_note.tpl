{*
a74fe6885509c651f2ee37e8b41267a193293cc7, v1 (xcart_4_7_0), 2015-02-27 17:35:59, security_ip_note.tpl, aim

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
