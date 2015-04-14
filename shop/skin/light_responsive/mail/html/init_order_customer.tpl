{*
a74fe6885509c651f2ee37e8b41267a193293cc7, v1 (xcart_4_7_0), 2015-02-27 17:35:59, init_order_customer.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}
{config_load file="$skin_config"}
{include file="mail/html/mail_header.tpl"}
{capture name="row"}
<h1>{include file="mail/salutation.tpl" title=$order.title firstname=$order.firstname lastname=$order.lastname}</h1>

{$lng.eml_init_order_customer}

<br /><br />{$lng.lbl_order_details_label}:
{/capture}
{include file="mail/html/responsive_row.tpl" content=$smarty.capture.row}

<table class="block-grid data-table">
<tr>
  <td class="name"><b>{$lng.lbl_order_id}:</b></td>
  <td class="value">#{$order.orderid}</td>
</tr>
<tr>
  <td class="name"><b>{$lng.lbl_order_date}:</b></td>
  <td class="value">{$order.date|date_format:$config.Appearance.datetime_format}</td>
</tr>
<tr>
  <td class="name"><b>{$lng.lbl_order_status}:</b></td>
  <td class="value">{include file="main/order_status.tpl" mode="static" status=$order.status}</td>
</tr>
</table>

{include file="mail/html/signature.tpl"}
