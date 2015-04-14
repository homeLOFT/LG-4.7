{*
50431e5a9f8922c87e07b012c4fb6ddc9ace7825, v2 (xcart_4_4_0_beta_2), 2010-05-25 08:14:23, preauth_order_customer.tpl, igoryan
vim: set ts=2 sw=2 sts=2 et:
*}
{config_load file="$skin_config"}
{include file="mail/mail_header.tpl"}


{include file="mail/salutation.tpl" title=$order.title firstname=$order.firstname lastname=$order.lastname}

{$lng.eml_thankyou_for_order}

{if $order.status eq 'A' or $order.status eq 'P' or $order.status eq 'C'}{$lng.lbl_receipt}{else}{$lng.lbl_invoice}{/if}:

{include file="mail/order_invoice.tpl"}

{include file="mail/signature.tpl"}
