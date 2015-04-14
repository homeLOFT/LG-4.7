{*
dbb6fe1c24a66fadc0c6c4ef9cc5fee53261ecd5, v4 (xcart_4_6_1), 2013-07-25 13:29:23, login_error.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
{include file="mail/html/mail_header.tpl"}
<br />{if $usertype eq ''}
{$lng.eml_login_error}
{else}
{$lng.eml_customer_login_error|substitute:"area":$userarea}
{/if}

<br /> 
<table cellpadding="2" cellspacing="1">
{if $smarty.server.REMOTE_ADDR ne ""}
<tr>
<td width="20%"><b>{$lng.lbl_remote_addr}:</b></td> 
<td width="10">&nbsp;</td> 
<td>{$smarty.server.REMOTE_ADDR|escape}</td>
</tr>
{/if}
{if $smarty.server.HTTP_X_FORWARDED_FOR ne ""}
<tr>
<td><b>{$lng.lbl_http_x_forwarded_for}:</b></td>
<td>&nbsp;</td>
<td>{$smarty.server.HTTP_X_FORWARDED_FOR|escape}</td>
</tr>
{/if}
<tr>
<td><b>{$lng.lbl_username}:</b></td>
<td>&nbsp;</td>
<td>{$failed_login}</td>
</tr>
{if $config.Security.send_login_pass eq 'Y'}
<tr>
<td><b>{$lng.lbl_password}:</b></td>
<td>&nbsp;</td>
<td>{$failed_password}</td>
</tr>
{/if}
<tr>
<td><b>{$lng.eml_message_shown}:</b></td>
<td>&nbsp;</td>
<td>{$shown_front_message}</td>
</tr>
</table>

{include file="mail/html/signature.tpl"}
