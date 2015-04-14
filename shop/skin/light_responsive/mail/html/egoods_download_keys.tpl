{*
a74fe6885509c651f2ee37e8b41267a193293cc7, v1 (xcart_4_7_0), 2015-02-27 17:35:59, egoods_download_keys.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}
{config_load file="$skin_config"}
{include file="mail/html/mail_header.tpl"}

{capture name="row"}
{$lng.eml_dear_customer},

<br /><br />{$lng.eml_egoods}

<br /><br />{$lng.eml_egoods_download}:
{/capture}
{include file="mail/html/responsive_row.tpl" content=$smarty.capture.row}

<table class="block-grid data-table">
{section name=prod_num loop=$products}
{if $products[prod_num].download_key}
<tr>
  <td class="name">
  {$lng.lbl_sku}:
  </td><td class="value">
  {$products[prod_num].productcode}
  </td>
</tr>
<tr>
<td class="name"><tt>{$lng.lbl_product}:</tt></td>
<td class="value"><tt>{$products[prod_num].product}</tt></td>
</tr>
<tr>
<td class="name"><tt>{$lng.lbl_item_price}:</tt></td>
<td class="value"><tt>{currency value=$products[prod_num].display_price}</tt></td>
</tr>
<tr>
<td class="name"><tt>{$lng.lbl_filename}:</tt></td>
<td class="value"><tt>{$products[prod_num].distribution_filename}</tt></td>
</tr>
<tr>
<td class="name"><tt>{$lng.lbl_download_url}:</tt></td>
<td class="value"><tt><a href="{$catalogs.customer}/download.php?id={$products[prod_num].download_key}" target="_blank">{$catalogs.customer}/download.php?id={$products[prod_num].download_key}</a></tt></td>
</tr>
<tr>
<td colspan="2" class="section"><hr size="1" noshade="noshade" width="70%" align="left" color="#DDDDDD" /></td>
</tr>
{/if}
{/section}
</table>

{capture name="row"}
<b>{$lng.eml_egoods_download_note|substitute:"ttl":$config.Egoods.download_key_ttl}</b>
{/capture}
{include file="mail/html/responsive_row.tpl" content=$smarty.capture.row}

{include file="mail/html/signature.tpl"}
