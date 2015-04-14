{*
e94f191a2cc0cb8dbefd384fabaa6f48af5f3c11, v1 (xcart_4_6_2), 2013-12-25 09:14:58, wishlist_sendall2friend.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
{config_load file="$skin_config"}
{include file="mail/html/mail_header.tpl"}

{capture name="row"}
{$lng.eml_hello}
<br /><br />
{$lng.eml_wish_list_send_msg|substitute:"sender":"`$userinfo.firstname` `$userinfo.lastname`"}
{/capture}
{include file="mail/html/responsive_row.tpl" content=$smarty.capture.row}

{section name=num loop=$wl_products}
{capture name="row"}
<hr noshade="noshade" size="1" width="70%" align="left" />
<b>{$wl_products[num].product}</b>
<br /><br />
{$wl_products[num].descr|truncate:200:"..."}
<br /><br />
<b>{$lng.lbl_price}: {currency value=$wl_products[num].taxed_price|default:$wl_products[num].price}</b>
{/capture}
{include file="mail/html/responsive_row.tpl" content=$smarty.capture.row}
{/section}

{capture name="row"}
<table class="tiny-button radius skinned">
<tr>
  <td>
    <a href="{$catalogs.customer}/cart.php?mode=friend_wl&amp;wlid={$wlid}">{$lng.eml_click_to_view_wishlist}</a>
  </td>
</tr>
</table>
{/capture}
{include file="mail/html/responsive_row.tpl" content=$smarty.capture.row}

{include file="mail/html/signature.tpl"}
