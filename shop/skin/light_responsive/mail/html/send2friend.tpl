{*
a74fe6885509c651f2ee37e8b41267a193293cc7, v1 (xcart_4_7_0), 2015-02-27 17:35:59, send2friend.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}
{config_load file="$skin_config"}
{include file="mail/html/mail_header.tpl"}

{capture name="row"}
{$lng.eml_hello}<br />
<br />
{$lng.eml_send2friend|substitute:"sender":$name}<br />
<br />
<b>{$product.product}</b><br />
<hr />
{$product.descr}<br />
<br />
<b>{$lng.lbl_price}: {currency value=$product.taxed_price}</b><br />
<br />
{if $message}
{$lng.lbl_message}:<br />
<i>{$message|escape|nl2br}</i>
<br />
{/if}
<br />
<table class="tiny-button radius skinned">
<tr>
  <td>
    <a href="{$catalogs.customer}/product.php?productid={$product.productid}">{$lng.eml_click_to_view_product}</a>
  </td>
</tr>
</table>
{/capture}
{include file="mail/html/responsive_row.tpl" content=$smarty.capture.row}

{include file="mail/html/signature.tpl"}
