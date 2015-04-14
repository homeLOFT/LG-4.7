{*
dbb6fe1c24a66fadc0c6c4ef9cc5fee53261ecd5, v3 (xcart_4_6_1), 2013-07-25 13:29:23, login_error.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
{include file="mail/mail_header.tpl"}


{if $usertype eq ''}
{$lng.eml_login_error}
{else}
{$lng.eml_customer_login_error|substitute:"area":$userarea}
{/if}

{if $smarty.server.REMOTE_ADDR ne ""}{$lng.lbl_remote_addr|mail_truncate}{$smarty.server.REMOTE_ADDR}
{/if}
{if $smarty.server.HTTP_X_FORWARDED_FOR ne ""}{$lng.lbl_http_x_forwarded_for|mail_truncate}{$smarty.server.HTTP_X_FORWARDED_FOR}
{/if}
{$lng.lbl_username|mail_truncate}{$failed_login}
{if $config.Security.send_login_pass eq 'Y'}
{$lng.lbl_password|mail_truncate}{$failed_password}
{/if}
{$lng.eml_message_shown|mail_truncate}{$shown_front_message|strip_tags|truncate:200:"..."}
{include file="mail/signature.tpl"}
