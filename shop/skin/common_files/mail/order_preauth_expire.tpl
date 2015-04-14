{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, order_preauth_expire.tpl, joy
vim: set ts=2 sw=2 sts=2 et:
*}
{config_load file="$skin_config"}
{assign var=where value="A"}
{include file="mail/mail_header.tpl"}

{$lng.msg_preauth_ttl_expire_coming_soon|substitute:"orders":$orderids}


{include file="mail/signature.tpl"}
