{*
a74fe6885509c651f2ee37e8b41267a193293cc7, v1 (xcart_4_7_0), 2015-02-27 17:35:59, order_notification_admin.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}
{config_load file="$skin_config"}
{include file="mail/html/mail_header.tpl"}

{assign var=where value="A"}

{capture name="row"}
{$lng.eml_order_notification|substitute:"orderid":$order.orderid}
{/capture}
{include file="mail/html/responsive_row.tpl" content=$smarty.capture.row}

{if $active_modules.PayPal_Here ne '' and $order.pph_web_url ne ''}
{capture name="row"}
{include file="modules/PayPal_Here/paypal_here_link.tpl" pph_url=$order.pph_web_url}
{/capture}
{include file="mail/html/responsive_row.tpl" content=$smarty.capture.row}
{/if}

{include file="mail/html/order_invoice.tpl"}

{include file="mail/html/signature.tpl"}
