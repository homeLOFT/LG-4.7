{*
e94f191a2cc0cb8dbefd384fabaa6f48af5f3c11, v1 (xcart_4_6_2), 2013-12-25 09:14:58, wishlist_send2friend.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
{config_load file="$skin_config"}
{include file="mail/html/mail_header.tpl"}

{capture name="row"}
{$lng.eml_hello}<br />
<br />
{$lng.eml_send2friend|substitute:"sender":"`$userinfo.firstname` `$userinfo.lastname`"}
<br />
<b>{$product.product}</b><br />
<hr />
{$product.descr}<br />
<br />
<b>{$lng.lbl_price}: {currency value=$product.taxed_price|default:$product.price}</b><br />
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
