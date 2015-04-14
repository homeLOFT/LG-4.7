{*
a74fe6885509c651f2ee37e8b41267a193293cc7, v1 (xcart_4_7_0), 2015-02-27 17:35:59, order_customer.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}
{config_load file="$skin_config"}
{include file="mail/html/mail_header.tpl"}

{capture name="row"}
<h1>{include file="mail/salutation.tpl" title=$order.title firstname=$order.firstname lastname=$order.lastname}</h1>

{$lng.eml_thankyou_for_order}
{if $order.userid lte 0}
  <br /><br />{$lng.txt_anonymous_order_access_url|substitute:"url":"`$current_location`/order.php?orderid=`$order.orderid`&amp;access_key=`$order.access_key`"}<br />
{/if}
{/capture}
{include file="mail/html/responsive_row.tpl" content=$smarty.capture.row}

{include file="mail/html/order_invoice.tpl"}

{include file="mail/html/signature.tpl"}
