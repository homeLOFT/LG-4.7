{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, giftcert_notification_subj.tpl, joy
vim: set ts=2 sw=2 sts=2 et:
*}
{config_load file="$skin_config"}{$config.Company.company_name}: {if $giftcert.recipient}{assign var="rcpt" value=$giftcert.recipient}{else}{assign var="rcpt" value=$giftcert.recipient_email}{/if}{$lng.eml_giftcert_notification_subj|substitute:"recipient":$rcpt}
