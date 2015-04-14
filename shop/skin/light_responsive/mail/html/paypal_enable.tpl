{*
a74fe6885509c651f2ee37e8b41267a193293cc7, v1 (xcart_4_7_0), 2015-02-27 17:35:59, paypal_enable.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}
{config_load file="$skin_config"}

{capture name="row"}
{$lng.eml_paypal_enable|substitute:"admin_url":$catalogs.admin:"paypal_enable_id":$paypal_enable_id}
{/capture}
{include file="mail/html/responsive_row.tpl" content=$smarty.capture.row}

{include file="mail/html/signature.tpl"}
