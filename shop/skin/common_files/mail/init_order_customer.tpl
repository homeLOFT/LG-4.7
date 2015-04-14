{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, init_order_customer.tpl, joy
vim: set ts=2 sw=2 sts=2 et:
*}
{config_load file="$skin_config"}
{include file="mail/mail_header.tpl"}


{include file="mail/salutation.tpl" title=$order.title firstname=$order.firstname lastname=$order.lastname}

{$lng.eml_init_order_customer}

{$lng.lbl_order_details_label}:

{$lng.lbl_order_id|mail_truncate}#{$order.orderid}
{$lng.lbl_order_date|mail_truncate}{$order.date|date_format:$config.Appearance.datetime_format}
{$lng.lbl_order_status|mail_truncate}{include file="main/order_status.tpl" mode="static" status=$order.status}

{include file="mail/signature.tpl"}
