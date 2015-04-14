{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, newsletter_subscribe.tpl, joy
vim: set ts=2 sw=2 sts=2 et:
*}
{config_load file="$skin_config"}
{include file="mail/mail_header.tpl"}


{$lng.eml_subscribed}

{$lng.eml_unsubscribe_information}
{$http_location}/mail/unsubscribe.php?email={$email|escape}

{include file="mail/signature.tpl"}
