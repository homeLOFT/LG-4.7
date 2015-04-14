{*
dc21e5a8162683b211ca53dc1512dab280748457, v4 (xcart_4_5_5), 2013-01-16 17:52:17, account_activation_key.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
{config_load file="$skin_config"}
{include file="mail/mail_header.tpl"}


{include file="mail/salutation.tpl" title=$userinfo.title firstname=$userinfo.firstname lastname=$userinfo.lastname}

{if $reason eq 'long_unused'}
{$lng.eml_account_was_suspended_long_unused|substitute:"number":$config.Security.suspend_admin_after|substitute:"login_name":$userinfo.login}:
{else}
{$lng.eml_account_was_suspended|substitute:"number":$lock_login_attempts|substitute:"login_name":$userinfo.login}:
{/if}

{$http_location}/login.php?activation_key={$activation_key}

{include file="mail/signature.tpl"}
