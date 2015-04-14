{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, order_customer_complete.tpl, joy
vim: set ts=2 sw=2 sts=2 et:
*}
{config_load file="$skin_config"}
{include file="mail/mail_header.tpl"}


{include file="mail/salutation.tpl" title=$customer.title firstname=$customer.firstname lastname=$customer.lastname}

{$lng.eml_order_complete}

{$lng.lbl_order_id|mail_truncate}#{$order.orderid}
{$lng.lbl_order_date|mail_truncate}{$order.date|date_format:$config.Appearance.datetime_format}
{if $order.tracking} 
{$lng.lbl_tracking_number|mail_truncate}{$order.tracking} 
{/if}

{include file="mail/order_data.tpl"}

{include file="mail/signature.tpl"}
