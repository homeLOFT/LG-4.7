{*
a74fe6885509c651f2ee37e8b41267a193293cc7, v1 (xcart_4_7_0), 2015-02-27 17:35:59, login_error.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}
{include file="mail/html/mail_header.tpl"}

{capture name="row"}
{if $usertype eq ''}
{$lng.eml_login_error}
{else}
{$lng.eml_customer_login_error|substitute:"area":$userarea}
{/if}
{/capture}
{include file="mail/html/responsive_row.tpl" content=$smarty.capture.row}

<table class="block-grid data-table">
{if $smarty.server.REMOTE_ADDR ne ""}
<tr>
  <td class="name"><b>{$lng.lbl_remote_addr}:</b></td> 
  <td class="value">{$smarty.server.REMOTE_ADDR|escape}</td>
</tr>
{/if}
{if $smarty.server.HTTP_X_FORWARDED_FOR ne ""}
<tr>
  <td class="name"><b>{$lng.lbl_http_x_forwarded_for}:</b></td>
  <td class="value">{$smarty.server.HTTP_X_FORWARDED_FOR|escape}</td>
</tr>
{/if}
<tr>
  <td class="name"><b>{$lng.lbl_username}:</b></td>
  <td class="value">{$failed_login}</td>
</tr>
{if $config.Security.send_login_pass eq 'Y'}
<tr>
  <td class="name"><b>{$lng.lbl_password}:</b></td>
  <td class="value">{$failed_password}</td>
</tr>
{/if}
<tr>
  <td class="name"><b>{$lng.eml_message_shown}:</b></td>
  <td class="value">{$shown_front_message}</td>
</tr>
</table>

{include file="mail/html/signature.tpl"}
