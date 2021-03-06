{*
a74fe6885509c651f2ee37e8b41267a193293cc7, v1 (xcart_4_7_0), 2015-02-27 17:35:59, order_customer_processed.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}
{config_load file="$skin_config"}
{include file="mail/html/mail_header.tpl"}

{capture name="row"}
<h1>{include file="mail/salutation.tpl" title=$customer.title firstname=$customer.firstname lastname=$customer.lastname}</h1>

{$lng.eml_order_processed}
{/capture}
{include file="mail/html/responsive_row.tpl" content=$smarty.capture.row}

<table class="block-grid data-table">
<tr>
  <td class="name"><b>{$lng.lbl_order_id}:</b></td>
  <td class="value"><tt><b>#{$order.orderid}</b></tt></td>
</tr>
<tr>
  <td class="name"><b>{$lng.lbl_order_date}:</b></td>
  <td class="value"><tt><b>{$order.date|date_format:$config.Appearance.datetime_format}</b></tt></td>
</tr>
{if $order.tracking}
<tr>
  <td class="name"><b>{$lng.lbl_tracking_number}:</b></td>
  <td class="value"><tt>{$order.tracking}</tt></td>
</tr>
{/if}
</table>

{include file="mail/html/order_data.tpl"}

{include file="mail/html/signature.tpl"}

