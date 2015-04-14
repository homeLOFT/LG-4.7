{*
777ebb9aa318e4a2b740cfb673bb5fc92596c9cd, v2 (xcart_4_5_5), 2013-01-16 15:15:30, password_recover.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
{config_load file="$skin_config"}
{include file="mail/mail_header.tpl"}


{$lng.eml_dear_customer},

{$lng.eml_password_reset_msg}

{$lng.lbl_account_information}:
--------------------
{$lng.lbl_username|mail_truncate}{$account.login}
{$lng.lbl_password_reset_url|mail_truncate}{if $config.Security.use_https_login eq 'Y'}{$https_location}{else}{$http_location}{/if}{if $userpath ne ''}{$userpath}{/if}/change_password.php?password_reset_key={$account.password_reset_key}&user={$account.id}


{include file="mail/signature.tpl"}
