{*
a74fe6885509c651f2ee37e8b41267a193293cc7, v1 (xcart_4_7_0), 2015-02-27 17:35:59, password_recover.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}
{config_load file="$skin_config"}
{include file="mail/html/mail_header.tpl"}

{capture name="row"}
{$lng.eml_dear_customer},

<br />{$lng.eml_password_reset_msg}
{/capture}
{include file="mail/html/responsive_row.tpl" content=$smarty.capture.row}

<table class="block-grid data-table">
<tr>
<td colspan="2" class="section"><b>{$lng.lbl_account_information}:</b></td>
</tr>
<tr>
<td class="name"><tt>{$lng.lbl_username}:</tt></td>
<td class="value"><tt>{$account.login}</tt></td>
</tr>
<tr>
<td class="name"><tt>{$lng.lbl_password_reset_url}:</tt></td>
<td class="value"><tt><a href="{if $config.Security.use_https_login eq 'Y'}{$https_location}{else}{$http_location}{/if}{if $userpath ne ''}{$userpath}{/if}/change_password.php?password_reset_key={$account.password_reset_key}&amp;user={$account.id}">{if $config.Security.use_https_login eq 'Y'}{$https_location}{else}{$http_location}{/if}{if $userpath ne ''}{$userpath}{/if}/change_password.php?password_reset_key={$account.password_reset_key}&user={$account.id}</a></tt></td>
</tr>
</table>

{include file="mail/html/signature.tpl"}

