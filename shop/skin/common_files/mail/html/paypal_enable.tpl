{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, paypal_enable.tpl, joy
vim: set ts=2 sw=2 sts=2 et:
*}
{config_load file="$skin_config"}

{$lng.eml_paypal_enable|substitute:"admin_url":$catalogs.admin:"paypal_enable_id":$paypal_enable_id}

{include file="mail/html/signature.tpl"}
