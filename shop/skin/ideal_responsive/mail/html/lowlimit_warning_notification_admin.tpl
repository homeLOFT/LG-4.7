{*
e94f191a2cc0cb8dbefd384fabaa6f48af5f3c11, v1 (xcart_4_6_2), 2013-12-25 09:14:58, lowlimit_warning_notification_admin.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
{config_load file="$skin_config"}
{include file="mail/html/mail_header.tpl"}

{capture name="row"}
{$lng.eml_lowlimit_warning_message|substitute:"sender":$config.Company.company_name:"productid":$product.productid}
{/capture}
{include file="mail/html/responsive_row.tpl" content=$smarty.capture.row}

<table class="block-grid data-table">
<tr>
  <td class="name">{$lng.lbl_sku}:</td>
  <td class="value"><b>{$product.productcode}</b></td>
</tr>
<tr>
  <td class="name">{$lng.lbl_product}:</td>
  <td class="value"><b>{$product.product}</b></td>
</tr>
{if $product.product_options ne ""}
<tr>
  <td class="name">{$lng.lbl_selected_options}:</td>
  <td class="value">{include file="modules/Product_Options/display_options.tpl" options=$product.product_options options_txt=$product.product_options_txt}</td>
</tr>
{/if}
</table>

{capture name="row"}
{$lng.lbl_items_in_stock|substitute:"items":$product.avail}
{/capture}
{include file="mail/html/responsive_row.tpl" content=$smarty.capture.row}

{include file="mail/html/signature.tpl"}
